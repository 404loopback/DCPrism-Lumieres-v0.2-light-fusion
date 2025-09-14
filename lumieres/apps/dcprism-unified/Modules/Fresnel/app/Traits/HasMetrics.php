<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait HasMetrics
{
    /**
     * Metrics data
     */
    protected array $metrics = [];

    /**
     * Metrics start times for duration calculations
     */
    protected array $metricsStartTimes = [];

    /**
     * Record a metric value
     */
    protected function recordMetric(string $name, mixed $value, array $tags = []): void
    {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => now()->timestamp,
            'component' => $this->getMetricsComponent()
        ];
    }

    /**
     * Start timing a metric
     */
    protected function startTiming(string $name): void
    {
        $this->metricsStartTimes[$name] = microtime(true);
    }

    /**
     * End timing and record the duration metric
     */
    protected function endTiming(string $name, array $tags = []): float
    {
        if (!isset($this->metricsStartTimes[$name])) {
            return 0.0;
        }

        $duration = (microtime(true) - $this->metricsStartTimes[$name]) * 1000; // in milliseconds
        $this->recordMetric("{$name}_duration_ms", round($duration, 2), $tags);
        
        unset($this->metricsStartTimes[$name]);
        
        return $duration;
    }

    /**
     * Record execution time of a callable
     */
    protected function timeExecution(string $name, callable $callback, array $tags = [])
    {
        $this->startTiming($name);
        
        try {
            $result = $callback();
            $this->endTiming($name, array_merge($tags, ['status' => 'success']));
            return $result;
        } catch (\Exception $e) {
            $this->endTiming($name, array_merge($tags, ['status' => 'error']));
            throw $e;
        }
    }

    /**
     * Record memory usage
     */
    protected function recordMemoryUsage(string $operation = 'current'): void
    {
        $this->recordMetric('memory_usage_bytes', memory_get_usage(true), [
            'operation' => $operation,
            'type' => 'current'
        ]);
        
        $this->recordMetric('memory_peak_bytes', memory_get_peak_usage(true), [
            'operation' => $operation,
            'type' => 'peak'
        ]);
    }

    /**
     * Record database query count and time
     */
    protected function recordDatabaseMetrics(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $queryLog = DB::getQueryLog();
        $queryCount = count($queryLog);
        $totalTime = array_sum(array_column($queryLog, 'time'));

        $this->recordMetric('database_queries_count', $queryCount);
        $this->recordMetric('database_queries_time_ms', $totalTime);
    }

    /**
     * Record cache hit/miss
     */
    protected function recordCacheMetric(string $key, bool $hit): void
    {
        $this->recordMetric('cache_operation', 1, [
            'key' => $key,
            'result' => $hit ? 'hit' : 'miss'
        ]);
    }

    /**
     * Record API response metrics
     */
    protected function recordApiMetrics(int $statusCode, int $responseSize = 0, float $responseTime = 0): void
    {
        $this->recordMetric('api_response', 1, [
            'status_code' => $statusCode,
            'status_class' => $this->getStatusClass($statusCode)
        ]);

        if ($responseSize > 0) {
            $this->recordMetric('api_response_size_bytes', $responseSize);
        }

        if ($responseTime > 0) {
            $this->recordMetric('api_response_time_ms', $responseTime);
        }
    }

    /**
     * Record file operation metrics
     */
    protected function recordFileMetrics(string $operation, string $filePath, int $fileSize = 0, float $duration = 0): void
    {
        $tags = [
            'operation' => $operation,
            'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION)
        ];

        $this->recordMetric('file_operation', 1, $tags);

        if ($fileSize > 0) {
            $this->recordMetric('file_size_bytes', $fileSize, $tags);
        }

        if ($duration > 0) {
            $this->recordMetric('file_operation_duration_ms', $duration, $tags);
        }
    }

    /**
     * Record DCP processing metrics
     */
    protected function recordDcpMetrics(string $operation, array $metadata = []): void
    {
        $tags = array_merge([
            'operation' => $operation,
            'component' => $this->getMetricsComponent()
        ], $metadata);

        $this->recordMetric('dcp_operation', 1, $tags);
    }

    /**
     * Get all recorded metrics
     */
    protected function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Clear all metrics
     */
    protected function clearMetrics(): void
    {
        $this->metrics = [];
        $this->metricsStartTimes = [];
    }

    /**
     * Store metrics to cache for later retrieval
     */
    protected function storeMetrics(string $key, int $ttlMinutes = 60): void
    {
        if (empty($this->metrics)) {
            return;
        }

        $cacheKey = "metrics:{$key}:" . now()->format('Y-m-d-H');
        $existingMetrics = Cache::get($cacheKey, []);
        
        Cache::put($cacheKey, array_merge($existingMetrics, $this->metrics), now()->addMinutes($ttlMinutes));
    }

    /**
     * Get metrics summary
     */
    protected function getMetricsSummary(): array
    {
        if (empty($this->metrics)) {
            return [];
        }

        $summary = [
            'total_metrics' => count($this->metrics),
            'component' => $this->getMetricsComponent(),
            'time_range' => [
                'start' => min(array_column($this->metrics, 'timestamp')),
                'end' => max(array_column($this->metrics, 'timestamp'))
            ]
        ];

        // Group by metric name
        $groupedMetrics = [];
        foreach ($this->metrics as $metric) {
            $name = $metric['name'];
            if (!isset($groupedMetrics[$name])) {
                $groupedMetrics[$name] = [];
            }
            $groupedMetrics[$name][] = $metric['value'];
        }

        // Calculate aggregations for numeric metrics
        foreach ($groupedMetrics as $name => $values) {
            if (is_numeric($values[0])) {
                $summary['metrics'][$name] = [
                    'count' => count($values),
                    'sum' => array_sum($values),
                    'avg' => round(array_sum($values) / count($values), 2),
                    'min' => min($values),
                    'max' => max($values)
                ];
            } else {
                $summary['metrics'][$name] = [
                    'count' => count($values),
                    'values' => array_unique($values)
                ];
            }
        }

        return $summary;
    }

    /**
     * Get the component name for metrics
     */
    protected function getMetricsComponent(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get HTTP status class (2xx, 3xx, 4xx, 5xx)
     */
    protected function getStatusClass(int $statusCode): string
    {
        return substr((string)$statusCode, 0, 1) . 'xx';
    }

    /**
     * Record system metrics
     */
    protected function recordSystemMetrics(): void
    {
        $this->recordMemoryUsage();
        
        // CPU load average (Linux only)
        if (PHP_OS_FAMILY === 'Linux' && function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $this->recordMetric('system_load_1min', $load[0] ?? 0);
            $this->recordMetric('system_load_5min', $load[1] ?? 0);
            $this->recordMetric('system_load_15min', $load[2] ?? 0);
        }
        
        // Disk usage
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        
        if ($diskFree !== false && $diskTotal !== false) {
            $this->recordMetric('disk_free_bytes', $diskFree);
            $this->recordMetric('disk_usage_percent', round((($diskTotal - $diskFree) / $diskTotal) * 100, 2));
        }
    }

    /**
     * Set a performance baseline for comparisons
     */
    protected function setPerformanceBaseline(string $operation, float $baselineMs): void
    {
        Cache::put("performance_baseline:{$operation}", $baselineMs, now()->addDays(30));
    }

    /**
     * Compare current performance against baseline
     */
    protected function compareToBaseline(string $operation, float $currentMs): array
    {
        $baseline = Cache::get("performance_baseline:{$operation}");
        
        if (!$baseline) {
            return ['status' => 'no_baseline'];
        }
        
        $difference = $currentMs - $baseline;
        $percentChange = round(($difference / $baseline) * 100, 2);
        
        return [
            'status' => $difference > 0 ? 'slower' : 'faster',
            'baseline_ms' => $baseline,
            'current_ms' => $currentMs,
            'difference_ms' => round($difference, 2),
            'percent_change' => $percentChange
        ];
    }
}
