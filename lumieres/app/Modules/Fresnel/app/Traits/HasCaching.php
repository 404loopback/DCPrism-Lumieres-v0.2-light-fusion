<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCaching
{
    /**
     * Enable/disable caching for this instance
     */
    protected bool $cachingEnabled = true;

    /**
     * Default cache TTL in minutes
     */
    protected int $defaultCacheTtl = 60;

    /**
     * Cache key prefix for this component
     */
    protected ?string $cachePrefix = null;

    /**
     * Cache tags for grouping related cache entries
     */
    protected array $cacheTags = [];

    /**
     * Get data from cache or execute callback
     */
    protected function cacheRemember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (! $this->cachingEnabled) {
            return $callback();
        }

        $cacheKey = $this->buildCacheKey($key);
        $ttl = $ttl ?? $this->defaultCacheTtl;

        return Cache::remember($cacheKey, now()->addMinutes($ttl), $callback);
    }

    /**
     * Get data from cache or execute callback with tags
     */
    protected function cacheRememberWithTags(string $key, callable $callback, array $tags = [], ?int $ttl = null): mixed
    {
        if (! $this->cachingEnabled) {
            return $callback();
        }

        $cacheKey = $this->buildCacheKey($key);
        $ttl = $ttl ?? $this->defaultCacheTtl;
        $allTags = array_merge($this->cacheTags, $tags);

        if (empty($allTags)) {
            return Cache::remember($cacheKey, now()->addMinutes($ttl), $callback);
        }

        return Cache::tags($allTags)->remember($cacheKey, now()->addMinutes($ttl), $callback);
    }

    /**
     * Store data in cache
     */
    protected function cacheStore(string $key, mixed $value, ?int $ttl = null): void
    {
        if (! $this->cachingEnabled) {
            return;
        }

        $cacheKey = $this->buildCacheKey($key);
        $ttl = $ttl ?? $this->defaultCacheTtl;

        Cache::put($cacheKey, $value, now()->addMinutes($ttl));
    }

    /**
     * Store data in cache with tags
     */
    protected function cacheStoreWithTags(string $key, mixed $value, array $tags = [], ?int $ttl = null): void
    {
        if (! $this->cachingEnabled) {
            return;
        }

        $cacheKey = $this->buildCacheKey($key);
        $ttl = $ttl ?? $this->defaultCacheTtl;
        $allTags = array_merge($this->cacheTags, $tags);

        if (empty($allTags)) {
            Cache::put($cacheKey, $value, now()->addMinutes($ttl));

            return;
        }

        Cache::tags($allTags)->put($cacheKey, $value, now()->addMinutes($ttl));
    }

    /**
     * Get data from cache
     */
    protected function cacheGet(string $key, mixed $default = null): mixed
    {
        if (! $this->cachingEnabled) {
            return $default;
        }

        return Cache::get($this->buildCacheKey($key), $default);
    }

    /**
     * Check if cache key exists
     */
    protected function cacheHas(string $key): bool
    {
        if (! $this->cachingEnabled) {
            return false;
        }

        return Cache::has($this->buildCacheKey($key));
    }

    /**
     * Forget cache key
     */
    protected function cacheForget(string $key): void
    {
        if (! $this->cachingEnabled) {
            return;
        }

        Cache::forget($this->buildCacheKey($key));
    }

    /**
     * Flush cache by tags
     */
    protected function cacheFlushTags(array $tags = []): void
    {
        if (! $this->cachingEnabled) {
            return;
        }

        $allTags = array_merge($this->cacheTags, $tags);

        if (empty($allTags)) {
            return;
        }

        Cache::tags($allTags)->flush();
    }

    /**
     * Build cache key with prefix
     */
    protected function buildCacheKey(string $key): string
    {
        $prefix = $this->cachePrefix ?? $this->getCachePrefix();

        return "{$prefix}:{$key}";
    }

    /**
     * Get default cache prefix based on class name
     */
    protected function getCachePrefix(): string
    {
        return strtolower(class_basename(static::class));
    }

    /**
     * Cache method result
     */
    protected function cacheMethod(string $method, array $args = [], ?int $ttl = null): mixed
    {
        $key = $this->buildMethodCacheKey($method, $args);

        return $this->cacheRemember($key, function () use ($method, $args) {
            return $this->{$method}(...$args);
        }, $ttl);
    }

    /**
     * Cache query result
     */
    protected function cacheQuery(string $query, array $bindings = [], ?int $ttl = null): mixed
    {
        $key = $this->buildQueryCacheKey($query, $bindings);

        return $this->cacheRemember($key, function () use ($query, $bindings) {
            return \DB::select($query, $bindings);
        }, $ttl);
    }

    /**
     * Cache model collection
     */
    protected function cacheModel(string $model, array $constraints = [], ?int $ttl = null): mixed
    {
        $key = $this->buildModelCacheKey($model, $constraints);

        return $this->cacheRememberWithTags($key, function () use ($model, $constraints) {
            $query = app($model)->newQuery();

            foreach ($constraints as $field => $value) {
                $query->where($field, $value);
            }

            return $query->get();
        }, ["model:{$model}"], $ttl);
    }

    /**
     * Build cache key for method calls
     */
    protected function buildMethodCacheKey(string $method, array $args): string
    {
        $argsHash = md5(serialize($args));

        return "method:{$method}:{$argsHash}";
    }

    /**
     * Build cache key for database queries
     */
    protected function buildQueryCacheKey(string $query, array $bindings): string
    {
        $queryHash = md5($query.serialize($bindings));

        return "query:{$queryHash}";
    }

    /**
     * Build cache key for model queries
     */
    protected function buildModelCacheKey(string $model, array $constraints): string
    {
        $constraintsHash = md5(serialize($constraints));
        $modelName = class_basename($model);

        return "model:{$modelName}:{$constraintsHash}";
    }

    /**
     * Cache file contents
     */
    protected function cacheFile(string $filePath, ?int $ttl = null): mixed
    {
        if (! file_exists($filePath)) {
            return null;
        }

        $fileHash = md5_file($filePath);
        $key = 'file:'.basename($filePath).":{$fileHash}";

        return $this->cacheRemember($key, function () use ($filePath) {
            return file_get_contents($filePath);
        }, $ttl);
    }

    /**
     * Cache API response
     */
    protected function cacheApiResponse(string $url, array $headers = [], ?int $ttl = null): mixed
    {
        $key = 'api:'.md5($url.serialize($headers));

        return $this->cacheRememberWithTags($key, function () {
            // This would be implemented with your HTTP client
            // return Http::withHeaders($headers)->get($url)->json();
            return null;
        }, ['api'], $ttl);
    }

    /**
     * Increment cache counter
     */
    protected function cacheIncrement(string $key, int $increment = 1): int
    {
        if (! $this->cachingEnabled) {
            return 0;
        }

        $cacheKey = $this->buildCacheKey($key);

        return Cache::increment($cacheKey, $increment);
    }

    /**
     * Decrement cache counter
     */
    protected function cacheDecrement(string $key, int $decrement = 1): int
    {
        if (! $this->cachingEnabled) {
            return 0;
        }

        $cacheKey = $this->buildCacheKey($key);

        return Cache::decrement($cacheKey, $decrement);
    }

    /**
     * Lock execution using cache
     */
    protected function cacheLock(string $key, int $seconds = 60): ?\Illuminate\Cache\Lock
    {
        if (! $this->cachingEnabled) {
            return null;
        }

        $lockKey = $this->buildCacheKey("lock:{$key}");

        return Cache::lock($lockKey, $seconds);
    }

    /**
     * Execute callback with cache lock
     */
    protected function cacheWithLock(string $key, callable $callback, int $lockSeconds = 60): mixed
    {
        $lock = $this->cacheLock($key, $lockSeconds);

        if (! $lock || ! $lock->get()) {
            throw new \RuntimeException("Could not acquire cache lock for key: {$key}");
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }

    /**
     * Set cache prefix
     */
    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;

        return $this;
    }

    /**
     * Set cache tags
     */
    public function setCacheTags(array $tags): self
    {
        $this->cacheTags = $tags;

        return $this;
    }

    /**
     * Add cache tag
     */
    public function addCacheTag(string $tag): self
    {
        $this->cacheTags[] = $tag;

        return $this;
    }

    /**
     * Enable/disable caching
     */
    public function setCachingEnabled(bool $enabled): self
    {
        $this->cachingEnabled = $enabled;

        return $this;
    }

    /**
     * Set default cache TTL
     */
    public function setDefaultCacheTtl(int $minutes): self
    {
        $this->defaultCacheTtl = $minutes;

        return $this;
    }

    /**
     * Get cache statistics
     */
    protected function getCacheStats(): array
    {
        // This would require implementing cache driver specific statistics
        // For now, return basic information
        return [
            'enabled' => $this->cachingEnabled,
            'default_ttl' => $this->defaultCacheTtl,
            'prefix' => $this->cachePrefix ?? $this->getCachePrefix(),
            'tags' => $this->cacheTags,
        ];
    }
}
