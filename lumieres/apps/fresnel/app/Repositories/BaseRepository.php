<?php

namespace App\Repositories;

use App\Traits\HasCaching;
use App\Traits\HasLogging;
use App\Traits\HasMetrics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    use HasCaching, HasLogging, HasMetrics;

    /**
     * The model instance
     */
    protected Model $model;

    /**
     * Default relationships to eager load
     */
    protected array $defaultWith = [];

    /**
     * Default ordering
     */
    protected array $defaultOrderBy = ['created_at' => 'desc'];

    /**
     * Searchable fields
     */
    protected array $searchableFields = [];

    /**
     * Filterable fields
     */
    protected array $filterableFields = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
        $this->setCachePrefix($this->getCachePrefix());
        $this->addCacheTag($this->getModelTag());
    }

    /**
     * Create model instance
     */
    abstract protected function makeModel(): Model;

    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->cacheRememberWithTags(
            'all:' . md5(serialize($columns)),
            fn() => $this->model->select($columns)->with($this->defaultWith)->get(),
            [$this->getModelTag()],
            30
        );
    }

    /**
     * Find record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->cacheRememberWithTags(
            "find:{$id}:" . md5(serialize($columns)),
            fn() => $this->model->select($columns)->with($this->defaultWith)->find($id),
            [$this->getModelTag()],
            60
        );
    }

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $result = $this->find($id, $columns);
        
        if (!$result) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Model " . get_class($this->model) . " with ID {$id} not found"
            );
        }
        
        return $result;
    }

    /**
     * Find records by field
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        $cacheKey = "findBy:{$field}:" . md5(serialize($value)) . ':' . md5(serialize($columns));
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            fn() => $this->model->select($columns)->with($this->defaultWith)->where($field, $value)->get(),
            [$this->getModelTag()],
            30
        );
    }

    /**
     * Find first record by field
     */
    public function findByFirst(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        $cacheKey = "findByFirst:{$field}:" . md5(serialize($value)) . ':' . md5(serialize($columns));
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            fn() => $this->model->select($columns)->with($this->defaultWith)->where($field, $value)->first(),
            [$this->getModelTag()],
            30
        );
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->timeExecution('create', function() use ($data) {
            DB::beginTransaction();
            
            try {
                $model = $this->model->create($data);
                
                $this->recordMetric('repository_create', 1, [
                    'model' => $this->getModelName()
                ]);
                
                $this->invalidateModelCache();
                
                DB::commit();
                
                $this->logInfo('Model created', [
                    'model' => $this->getModelName(),
                    'id' => $model->id
                ]);
                
                return $model;
                
            } catch (\Exception $e) {
                DB::rollback();
                
                $this->logError('Failed to create model', [
                    'model' => $this->getModelName(),
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        return $this->timeExecution('update', function() use ($id, $data) {
            $model = $this->findOrFail($id);
            
            DB::beginTransaction();
            
            try {
                $result = $model->update($data);
                
                $this->recordMetric('repository_update', 1, [
                    'model' => $this->getModelName()
                ]);
                
                $this->invalidateModelCache();
                
                DB::commit();
                
                $this->logInfo('Model updated', [
                    'model' => $this->getModelName(),
                    'id' => $id
                ]);
                
                return $result;
                
            } catch (\Exception $e) {
                DB::rollback();
                
                $this->logError('Failed to update model', [
                    'model' => $this->getModelName(),
                    'id' => $id,
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        return $this->timeExecution('delete', function() use ($id) {
            $model = $this->findOrFail($id);
            
            DB::beginTransaction();
            
            try {
                $result = $model->delete();
                
                $this->recordMetric('repository_delete', 1, [
                    'model' => $this->getModelName()
                ]);
                
                $this->invalidateModelCache();
                
                DB::commit();
                
                $this->logInfo('Model deleted', [
                    'model' => $this->getModelName(),
                    'id' => $id
                ]);
                
                return $result;
                
            } catch (\Exception $e) {
                DB::rollback();
                
                $this->logError('Failed to delete model', [
                    'model' => $this->getModelName(),
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Get paginated records with filters
     */
    public function paginate(int $perPage = 15, array $filters = [], array $columns = ['*']): LengthAwarePaginator
    {
        return $this->timeExecution('paginate', function() use ($perPage, $filters, $columns) {
            $query = $this->model->select($columns)->with($this->defaultWith);
            
            // Apply filters
            $query = $this->applyFilters($query, $filters);
            
            // Apply default ordering
            $query = $this->applyOrdering($query, $filters['sort'] ?? []);
            
            $this->recordMetric('repository_paginate', 1, [
                'model' => $this->getModelName(),
                'per_page' => $perPage,
                'filters_count' => count($filters)
            ]);
            
            return $query->paginate($perPage);
        });
    }

    /**
     * Search records
     */
    public function search(string $term, array $columns = ['*']): Collection
    {
        if (empty($this->searchableFields)) {
            return collect();
        }
        
        $cacheKey = 'search:' . md5($term . serialize($columns));
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            function() use ($term, $columns) {
                $query = $this->model->select($columns)->with($this->defaultWith);
                
                $query->where(function($q) use ($term) {
                    foreach ($this->searchableFields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$term}%");
                    }
                });
                
                return $query->get();
            },
            [$this->getModelTag(), 'search'],
            15
        );
    }

    /**
     * Count records with filters
     */
    public function count(array $filters = []): int
    {
        $cacheKey = 'count:' . md5(serialize($filters));
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            function() use ($filters) {
                $query = $this->model->newQuery();
                return $this->applyFilters($query, $filters)->count();
            },
            [$this->getModelTag()],
            30
        );
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        return $this->cacheRememberWithTags(
            "exists:{$id}",
            fn() => $this->model->where('id', $id)->exists(),
            [$this->getModelTag()],
            60
        );
    }

    /**
     * Get records created in date range
     */
    public function createdBetween(\Carbon\Carbon $start, \Carbon\Carbon $end): Collection
    {
        $cacheKey = "createdBetween:{$start->format('Y-m-d')}:{$end->format('Y-m-d')}";
        
        return $this->cacheRememberWithTags(
            $cacheKey,
            fn() => $this->model->with($this->defaultWith)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'desc')
                ->get(),
            [$this->getModelTag()],
            60
        );
    }

    /**
     * Bulk insert records
     */
    public function bulkInsert(array $data): bool
    {
        return $this->timeExecution('bulk_insert', function() use ($data) {
            DB::beginTransaction();
            
            try {
                $result = $this->model->insert($data);
                
                $this->recordMetric('repository_bulk_insert', count($data), [
                    'model' => $this->getModelName()
                ]);
                
                $this->invalidateModelCache();
                
                DB::commit();
                
                $this->logInfo('Bulk insert completed', [
                    'model' => $this->getModelName(),
                    'count' => count($data)
                ]);
                
                return $result;
                
            } catch (\Exception $e) {
                DB::rollback();
                
                $this->logError('Bulk insert failed', [
                    'model' => $this->getModelName(),
                    'count' => count($data),
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (!in_array($field, $this->filterableFields) || $value === null) {
                continue;
            }
            
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query;
    }

    /**
     * Apply ordering to query
     */
    protected function applyOrdering(Builder $query, array $sort = []): Builder
    {
        $ordering = empty($sort) ? $this->defaultOrderBy : $sort;
        
        foreach ($ordering as $field => $direction) {
            $query->orderBy($field, $direction);
        }
        
        return $query;
    }

    /**
     * Get cache prefix for this repository
     */
    protected function getCachePrefix(): string
    {
        return 'repo:' . strtolower($this->getModelName());
    }

    /**
     * Get model cache tag
     */
    protected function getModelTag(): string
    {
        return 'model:' . strtolower($this->getModelName());
    }

    /**
     * Get model name
     */
    protected function getModelName(): string
    {
        return class_basename($this->model);
    }

    /**
     * Invalidate all model-related cache
     */
    protected function invalidateModelCache(): void
    {
        $this->cacheFlushTags([$this->getModelTag()]);
    }

    /**
     * Get repository statistics
     */
    public function getStats(): array
    {
        return [
            'model' => $this->getModelName(),
            'total_records' => $this->count(),
            'cache_prefix' => $this->getCachePrefix(),
            'searchable_fields' => $this->searchableFields,
            'filterable_fields' => $this->filterableFields,
            'default_with' => $this->defaultWith,
            'metrics' => $this->getMetricsSummary()
        ];
    }

    /**
     * Refresh model instance
     */
    public function refreshModel(): void
    {
        $this->model = $this->makeModel();
    }
}
