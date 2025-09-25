<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Generate request ID for tracking
        $requestId = Str::random(12);
        $request->attributes->set('api_request_id', $requestId);

        // Log incoming request
        $this->logRequest($request, $requestId);

        $response = $next($request);

        // Calculate response time
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        // Log outgoing response
        $this->logResponse($request, $response, $requestId, $responseTime);

        // Add request ID and response time to headers
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Response-Time', $responseTime.'ms');

        return $response;
    }

    /**
     * Log incoming API request
     */
    private function logRequest(Request $request, string $requestId): void
    {
        $data = [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'headers' => $this->getFilteredHeaders($request),
        ];

        // Add request body for non-GET requests (but filter sensitive data)
        if (! $request->isMethod('GET')) {
            $data['body'] = $this->getFilteredRequestBody($request);
        }

        Log::channel('api')->info('API Request', $data);
    }

    /**
     * Log outgoing API response
     */
    private function logResponse(Request $request, Response $response, string $requestId, float $responseTime): void
    {
        $statusCode = $response->getStatusCode();
        $level = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');

        $data = [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response_time_ms' => $responseTime,
            'memory_usage_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        // Log response body for errors
        if ($statusCode >= 400) {
            $content = $response->getContent();
            if ($content && strlen($content) < 10000) { // Limit log size
                $data['response_body'] = json_decode($content, true) ?: $content;
            }
        }

        Log::channel('api')->log($level, 'API Response', $data);

        // Log slow requests
        if ($responseTime > 5000) { // Over 5 seconds
            Log::channel('api')->warning('Slow API Request', [
                'request_id' => $requestId,
                'method' => $request->getMethod(),
                'url' => $request->fullUrl(),
                'response_time_ms' => $responseTime,
                'user_id' => $request->user()?->id,
            ]);
        }
    }

    /**
     * Get filtered headers (remove sensitive information)
     */
    private function getFilteredHeaders(Request $request): array
    {
        $headers = $request->headers->all();

        // Remove or mask sensitive headers
        $sensitiveHeaders = ['authorization', 'x-api-key', 'cookie', 'x-csrf-token'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                if ($header === 'authorization') {
                    $headers[$header] = ['Bearer ***masked***'];
                } else {
                    $headers[$header] = ['***masked***'];
                }
            }
        }

        return $headers;
    }

    /**
     * Get filtered request body (remove sensitive information)
     */
    private function getFilteredRequestBody(Request $request): array
    {
        $body = $request->all();

        // Remove or mask sensitive fields
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key',
            'secret', 'private_key', 'access_token', 'refresh_token',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($body[$field])) {
                $body[$field] = '***masked***';
            }
        }

        // Limit body size in logs
        $bodyString = json_encode($body);
        if (strlen($bodyString) > 10000) {
            return ['_truncated' => 'Request body too large for logging'];
        }

        return $body;
    }
}
