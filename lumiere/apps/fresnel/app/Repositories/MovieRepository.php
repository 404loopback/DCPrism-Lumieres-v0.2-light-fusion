<?php

namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository extends BaseRepository
{
    /**
     * Default relationships to eager load
     */
    protected array $defaultWith = ['festivals', 'uploads'];

    /**
     * Searchable fields
     */
    protected array $searchableFields = [
        'title',
        'original_title', 
        'director',
        'synopsis',
        'genre'
    ];

    /**
     * Filterable fields
     */
    protected array $filterableFields = [
        'status',
        'genre',
        'year',
        'format',
        'dcp_status',
        'processing_status',
        'source_email'
    ];

    /**
     * Create model instance
     */
    protected function makeModel(): Model
    {
        return new Movie();
    }

    /**
     * Find movies by status
     */
    public function findByStatus(string $status): Collection
    {
        return $this->findBy('status', $status);
    }

    /**
     * Find movies by DCP status
     */
    public function findByDcpStatus(string $dcpStatus): Collection
    {
        return $this->findBy('dcp_status', $dcpStatus);
    }

    /**
     * Get movies pending validation
     */
    public function getPendingValidation(): Collection
    {
        return $this->cacheRememberWithTags(
            'pending_validation',
            fn() => $this->model->with($this->defaultWith)
                ->whereIn('status', ['upload_ok', 'in_review'])
                ->orderBy('uploaded_at', 'asc')
                ->get(),
            ['validation', 'status:pending'],
            15
        );
    }

    /**
     * Get movies validated today
     */
    public function getValidatedToday(): Collection
    {
        return $this->cacheRememberWithTags(
            'validated_today',
            fn() => $this->model->with($this->defaultWith)
                ->where('status', 'validated')
                ->whereDate('validated_at', today())
                ->orderBy('validated_at', 'desc')
                ->get(),
            ['validation', 'today'],
            60
        );
    }

    /**
     * Find movies by festival
     */
    public function findByFestival(int $festivalId): Collection
    {
        return $this->cacheRememberWithTags(
            "by_festival:{$festivalId}",
            fn() => $this->model->with($this->defaultWith)
                ->whereHas('festivals', function($q) use ($festivalId) {
                    $q->where('festival_id', $festivalId);
                })
                ->get(),
            ["festival:{$festivalId}"],
            30
        );
    }

    /**
     * Find movies by uploader email
     */
    public function findByUploader(string $email): Collection
    {
        return $this->findBy('source_email', $email);
    }

    /**
     * Get movies with technical issues
     */
    public function getWithTechnicalIssues(): Collection
    {
        return $this->cacheRememberWithTags(
            'technical_issues',
            fn() => $this->model->with($this->defaultWith)
                ->whereIn('status', ['upload_error', 'validation_error', 'rejected', 'error'])
                ->orWhereNotNull('processing_errors')
                ->orderBy('updated_at', 'desc')
                ->get(),
            ['issues', 'technical'],
            15
        );
    }

    /**
     * Get movies by format and year
     */
    public function findByFormatAndYear(string $format, int $year): Collection
    {
        $cacheKey = "format_year:{$format}:{$year}";
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            fn() => $this->model->with($this->defaultWith)
                ->where('format', $format)
                ->where('year', $year)
                ->orderBy('title')
                ->get(),
            ["format:{$format}", "year:{$year}"],
            60
        );
    }

    /**
     * Get statistics for movies
     */
    public function getStatistics(): array
    {
        return $this->cacheRememberWithTags(
            'statistics',
            function() {
                $stats = [
                    'total' => $this->model->count(),
                    'by_status' => $this->model->select('status', \DB::raw('count(*) as count'))
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray(),
                    'by_format' => $this->model->select('format', \DB::raw('count(*) as count'))
                        ->whereNotNull('format')
                        ->groupBy('format')
                        ->pluck('count', 'format')
                        ->toArray(),
                    'by_year' => $this->model->select('year', \DB::raw('count(*) as count'))
                        ->whereNotNull('year')
                        ->groupBy('year')
                        ->orderBy('year', 'desc')
                        ->limit(10)
                        ->pluck('count', 'year')
                        ->toArray(),
                    'recent_uploads' => $this->model->whereDate('created_at', '>=', now()->subDays(7))->count(),
                    'pending_validation' => $this->model->whereIn('status', ['upload_ok', 'in_review'])->count(),
                    'validated_this_month' => $this->model->where('status', 'validated')
                        ->whereMonth('validated_at', now()->month)
                        ->whereYear('validated_at', now()->year)
                        ->count(),
                ];

                // Add storage statistics
                $totalSize = $this->model->sum('file_size') ?? 0;
                $stats['storage'] = [
                    'total_size_bytes' => $totalSize,
                    'total_size_human' => $this->formatFileSize($totalSize),
                    'average_size_bytes' => $stats['total'] > 0 ? intval($totalSize / $stats['total']) : 0,
                ];

                return $stats;
            },
            ['statistics'],
            30
        );
    }

    /**
     * Update movie status with cache invalidation
     */
    public function updateStatus(int $id, string $status): bool
    {
        $result = $this->update($id, ['status' => $status]);
        
        if ($result) {
            // Invalidate specific cache entries
            $this->cacheFlushTags(['validation', 'status:pending', 'statistics']);
        }
        
        return $result;
    }

    /**
     * Bulk update movies status
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return $this->timeExecution('bulk_status_update', function() use ($ids, $status) {
            $updated = $this->model->whereIn('id', $ids)->update([
                'status' => $status,
                'updated_at' => now()
            ]);
            
            $this->recordMetric('repository_bulk_status_update', $updated, [
                'status' => $status,
                'count' => count($ids)
            ]);
            
            $this->invalidateModelCache();
            
            $this->logInfo('Bulk status update completed', [
                'ids' => $ids,
                'status' => $status,
                'updated_count' => $updated
            ]);
            
            return $updated;
        });
    }

    /**
     * Get movies requiring processing
     */
    public function getRequiringProcessing(): Collection
    {
        return $this->cacheRememberWithTags(
            'requiring_processing',
            fn() => $this->model->with($this->defaultWith)
                ->where('status', 'upload_ok')
                ->whereNull('processing_status')
                ->orWhere('processing_status', 'pending')
                ->orderBy('uploaded_at', 'asc')
                ->limit(50)
                ->get(),
            ['processing', 'pending'],
            10 // Cache for 10 minutes only
        );
    }

    /**
     * Find duplicate movies by title and year
     */
    public function findDuplicates(string $title, int $year): Collection
    {
        return $this->cacheRememberWithTags(
            "duplicates:" . md5($title . $year),
            fn() => $this->model->with($this->defaultWith)
                ->where('title', $title)
                ->where('year', $year)
                ->get(),
            ['duplicates'],
            30
        );
    }

    /**
     * Get movies with missing metadata
     */
    public function getWithMissingMetadata(): Collection
    {
        return $this->cacheRememberWithTags(
            'missing_metadata',
            fn() => $this->model->with($this->defaultWith)
                ->where('status', 'validated')
                ->whereNull('DCP_metadata')
                ->orWhere('DCP_metadata', '[]')
                ->orWhere('DCP_metadata', '{}')
                ->get(),
            ['metadata', 'missing'],
            30
        );
    }
}
