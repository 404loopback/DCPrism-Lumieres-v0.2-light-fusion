<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Octane\Facades\Octane;
use Symfony\Component\HttpFoundation\Response;

class OctaneOptimizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Optimize memory for large file uploads
        if ($this->isDcpUploadRequest($request)) {
            $this->optimizeForFileUpload();
        }
        
        // Set up caching for DCP metadata
        if ($this->isDcpProcessingRequest($request)) {
            $this->setupDcpCaching($request);
        }
        
        // Monitor memory usage
        $memoryBefore = memory_get_usage(true);
        
        $response = $next($request);
        
        // Post-request cleanup for DCP operations
        if ($this->needsCleanup($request)) {
            $this->performCleanup($memoryBefore);
        }
        
        return $response;
    }
    
    /**
     * Check if this is a DCP upload request
     */
    private function isDcpUploadRequest(Request $request): bool
    {
        return $request->is('api/upload*') || 
               $request->hasFile('dcp_file') ||
               str_contains($request->header('Content-Type', ''), 'multipart/form-data');
    }
    
    /**
     * Check if this is a DCP processing request
     */
    private function isDcpProcessingRequest(Request $request): bool
    {
        return $request->is('api/movies/*/analyze') ||
               $request->is('api/movies/*/validate') ||
               $request->is('api/movies/*/metadata') ||
               $request->is('api/batch/*');
    }
    
    /**
     * Optimize settings for file upload
     */
    private function optimizeForFileUpload(): void
    {
        // Increase memory limit for large files
        ini_set('memory_limit', config('octane.dcp.max_upload_size', '2G'));
        
        // Increase execution time for uploads
        set_time_limit(config('octane.dcp.analysis_timeout', 1800));
        
        // Optimize for large file handling
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
    
    /**
     * Setup caching for DCP processing
     */
    private function setupDcpCaching(Request $request): void
    {
        // Warm up frequently accessed cache keys
        $movieId = $request->route('movie');
        if ($movieId) {
            Cache::remember("movie.{$movieId}.metadata", 3600, function () use ($movieId) {
                return \App\Models\Movie::find($movieId)?->parameters;
            });
        }
    }
    
    /**
     * Check if cleanup is needed after request
     */
    private function needsCleanup(Request $request): bool
    {
        return $this->isDcpUploadRequest($request) || 
               $this->isDcpProcessingRequest($request);
    }
    
    /**
     * Perform post-request cleanup
     */
    private function performCleanup(int $memoryBefore): void
    {
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;
        
        // Log memory usage for monitoring
        if ($memoryUsed > 50 * 1024 * 1024) { // 50MB threshold
            Log::info('High memory usage detected', [
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        }
        
        // Force garbage collection for large operations
        if ($memoryUsed > 100 * 1024 * 1024) { // 100MB threshold
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
        
        // Clean up temporary files if any
        $this->cleanupTempFiles();
    }
    
    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(): void
    {
        $tempDir = storage_path('app/temp');
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > 3600) { // 1 hour old
                    @unlink($file);
                }
            }
        }
    }
    
    /**
     * Handle termination (called by Octane)
     */
    public function terminate(Request $request, Response $response): void
    {
        // Additional cleanup on termination
    }
    
}
