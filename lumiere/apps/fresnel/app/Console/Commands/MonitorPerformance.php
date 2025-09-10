<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\Upload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MonitorPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dcprism:monitor {--interval=60} {--alert-threshold=80} {--log-file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor DCPrism performance metrics and send alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = $this->option('interval');
        $alertThreshold = $this->option('alert-threshold');
        $logFile = $this->option('log-file') ?: storage_path('logs/performance.log');
        
        $this->info("🔍 Starting DCPrism performance monitoring...");
        $this->info("📊 Monitoring interval: {$interval} seconds");
        $this->info("⚠️ Alert threshold: {$alertThreshold}%");
        $this->info("📝 Log file: {$logFile}");
        $this->line('');
        
        while (true) {
            try {
                $metrics = $this->collectMetrics();
                $this->displayMetrics($metrics);
                $this->logMetrics($metrics, $logFile);
                $this->checkAlerts($metrics, $alertThreshold);
                
            } catch (\Exception $e) {
                $this->error("Error collecting metrics: " . $e->getMessage());
                Log::error('Performance monitoring error', ['error' => $e->getMessage()]);
            }
            
            sleep($interval);
        }
    }
    
    /**
     * Collect comprehensive performance metrics
     */
    private function collectMetrics(): array
    {
        $metrics = [
            'timestamp' => now()->toISOString(),
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'cache' => $this->getCacheMetrics(),
            'application' => $this->getApplicationMetrics(),
            'octane' => $this->getOctaneMetrics(),
        ];
        
        return $metrics;
    }
    
    /**
     * Get system-level metrics
     */
    private function getSystemMetrics(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        // Convert memory limit to bytes
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $memoryPercent = $memoryLimitBytes > 0 ? ($memoryUsage / $memoryLimitBytes) * 100 : 0;
        
        return [
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'memory_peak_mb' => round($memoryPeak / 1024 / 1024, 2),
            'memory_limit' => $memoryLimit,
            'memory_percent' => round($memoryPercent, 2),
            'disk_free_gb' => round(disk_free_space(storage_path()) / 1024 / 1024 / 1024, 2),
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
        ];
    }
    
    /**
     * Get database performance metrics
     */
    private function getDatabaseMetrics(): array
    {
        $connectionName = DB::getDefaultConnection();
        
        // Test query performance
        $start = microtime(true);
        DB::select('SELECT 1');
        $queryTime = (microtime(true) - $start) * 1000;
        
        // Get active connections if possible
        $activeConnections = 0;
        try {
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            $activeConnections = $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            // Ignore if not MySQL/MariaDB
        }
        
        return [
            'connection' => $connectionName,
            'query_time_ms' => round($queryTime, 2),
            'active_connections' => $activeConnections,
            'total_queries' => DB::getQueryLog() ? count(DB::getQueryLog()) : 0,
        ];
    }
    
    /**
     * Get cache performance metrics
     */
    private function getCacheMetrics(): array
    {
        $metrics = ['driver' => config('cache.default')];
        
        try {
            // Test cache performance
            $start = microtime(true);
            Cache::put('performance_test', 'test_value', 1);
            $writeTime = (microtime(true) - $start) * 1000;
            
            $start = microtime(true);
            Cache::get('performance_test');
            $readTime = (microtime(true) - $start) * 1000;
            
            Cache::forget('performance_test');
            
            $metrics['write_time_ms'] = round($writeTime, 2);
            $metrics['read_time_ms'] = round($readTime, 2);
            
            // Redis-specific metrics
            if (config('cache.default') === 'redis') {
                try {
                    $info = Redis::info();
                    $metrics['redis_memory_mb'] = round($info['used_memory'] / 1024 / 1024, 2);
                    $metrics['redis_connected_clients'] = $info['connected_clients'];
                    $metrics['redis_keyspace_hits'] = $info['keyspace_hits'];
                    $metrics['redis_keyspace_misses'] = $info['keyspace_misses'];
                } catch (\Exception $e) {
                    $metrics['redis_error'] = $e->getMessage();
                }
            }
            
        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }
        
        return $metrics;
    }
    
    /**
     * Get application-specific metrics
     */
    private function getApplicationMetrics(): array
    {
        return [
            'total_movies' => Movie::count(),
            'active_jobs' => 0,
            'failed_jobs' => 0,
            'completed_jobs' => 0,
            'total_uploads' => Upload::count(),
            'failed_uploads' => Upload::where('status', 'failed')->count(),
            'processing_uploads' => Upload::where('status', 'uploading')->count(),
        ];
    }
    
    /**
     * Get Octane-specific metrics
     */
    private function getOctaneMetrics(): array
    {
        $metrics = ['enabled' => app()->bound('octane')];
        
        if ($metrics['enabled']) {
            // Try to get RoadRunner metrics if available
            if (file_exists('.rr.yaml')) {
                $metrics['server'] = 'roadrunner';
                
                // Try to get worker status
                try {
                    $output = shell_exec('rr workers -i -c .rr.yaml 2>/dev/null');
                    if ($output) {
                        // Parse worker information if available
                        $metrics['workers_output'] = trim($output);
                    }
                } catch (\Exception $e) {
                    $metrics['rr_error'] = $e->getMessage();
                }
            }
        }
        
        return $metrics;
    }
    
    /**
     * Display metrics in a formatted table
     */
    private function displayMetrics(array $metrics): void
    {
        $this->line("📊 " . $metrics['timestamp']);
        $this->line(str_repeat('─', 80));
        
        // System metrics
        $system = $metrics['system'];
        $this->line(sprintf(
            "💾 Memory: %s MB (%.1f%%) | Peak: %s MB | Disk: %s GB",
            $system['memory_usage_mb'],
            $system['memory_percent'],
            $system['memory_peak_mb'],
            $system['disk_free_gb']
        ));
        
        // Database metrics
        $db = $metrics['database'];
        $this->line(sprintf(
            "🗄️  Database: %s ms query time | %s connections",
            $db['query_time_ms'],
            $db['active_connections']
        ));
        
        // Cache metrics
        $cache = $metrics['cache'];
        if (isset($cache['write_time_ms'])) {
            $this->line(sprintf(
                "⚡ Cache: %.2f ms write | %.2f ms read | Driver: %s",
                $cache['write_time_ms'],
                $cache['read_time_ms'],
                $cache['driver']
            ));
        }
        
        // Application metrics
        $app = $metrics['application'];
        $this->line(sprintf(
            "🎬 App: %s movies | %s active jobs | %s uploads",
            $app['total_movies'],
            $app['active_jobs'],
            $app['total_uploads']
        ));
        
        $this->line('');
    }
    
    /**
     * Log metrics to file
     */
    private function logMetrics(array $metrics, string $logFile): void
    {
        $logEntry = json_encode($metrics, JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check for performance alerts
     */
    private function checkAlerts(array $metrics, int $threshold): void
    {
        $alerts = [];
        
        // Memory usage alert
        if ($metrics['system']['memory_percent'] > $threshold) {
            $alerts[] = "High memory usage: {$metrics['system']['memory_percent']}%";
        }
        
        // Database performance alert
        if ($metrics['database']['query_time_ms'] > 100) {
            $alerts[] = "Slow database queries: {$metrics['database']['query_time_ms']} ms";
        }
        
        // Cache performance alert
        if (isset($metrics['cache']['write_time_ms']) && $metrics['cache']['write_time_ms'] > 50) {
            $alerts[] = "Slow cache writes: {$metrics['cache']['write_time_ms']} ms";
        }
        
        // Failed jobs alert
        if ($metrics['application']['failed_jobs'] > 10) {
            $alerts[] = "High number of failed jobs: {$metrics['application']['failed_jobs']}";
        }
        
        // Display alerts
        foreach ($alerts as $alert) {
            $this->error("🚨 ALERT: " . $alert);
            Log::warning('Performance alert', ['alert' => $alert, 'metrics' => $metrics]);
        }
    }
    
    /**
     * Convert PHP memory limit to bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;
        
        switch ($unit) {
            case 'g':
                $number *= 1024;
                // no break
            case 'm':
                $number *= 1024;
                // no break
            case 'k':
                $number *= 1024;
        }
        
        return $number;
    }
}
