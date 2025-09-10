<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MovieCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = MovieResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
                'has_more_pages' => $this->resource->hasMorePages(),
                
                // Additional statistics
                'statistics' => [
                    'completed_dcps' => $this->collection->where('dcp_status', 'completed')->count(),
                    'processing_dcps' => $this->collection->where('dcp_status', 'processing')->count(),
                    'failed_dcps' => $this->collection->where('dcp_status', 'failed')->count(),
                    'total_size' => $this->collection->sum('dcp_size'),
                    'total_size_human' => $this->formatBytes($this->collection->sum('dcp_size')),
                    'average_duration' => $this->collection->avg('duration'),
                    'genres' => $this->collection->pluck('genre')->unique()->values(),
                ]
            ],
            'links' => [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ]
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'filters_applied' => [
                'status' => $request->get('status'),
                'genre' => $request->get('genre'),
                'year' => $request->get('year'),
                'festival_id' => $request->get('festival_id'),
                'search' => $request->get('search'),
            ],
            'available_filters' => [
                'statuses' => ['pending', 'uploading', 'processing', 'completed', 'failed'],
                'genres' => $this->getAvailableGenres(),
                'years' => $this->getAvailableYears(),
            ],
            'sort_options' => [
                'title' => 'Title A-Z',
                '-title' => 'Title Z-A', 
                'year' => 'Year (Oldest first)',
                '-year' => 'Year (Newest first)',
                'created_at' => 'Created (Oldest first)',
                '-created_at' => 'Created (Newest first)',
                'dcp_size' => 'File size (Smallest first)',
                '-dcp_size' => 'File size (Largest first)',
            ]
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes): string
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get available genres from the collection
     */
    private function getAvailableGenres(): array
    {
        return $this->collection->pluck('genre')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get available years from the collection
     */
    private function getAvailableYears(): array
    {
        return $this->collection->pluck('year')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
