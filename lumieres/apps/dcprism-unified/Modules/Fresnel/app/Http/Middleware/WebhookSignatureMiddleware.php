<?php

namespace Modules\Fresnel\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookSignatureMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the signature from headers
        $signature = $request->header('X-DCPrism-Signature') ??
                    $request->header('X-Hub-Signature-256') ??
                    $request->header('X-Signature');

        if (! $signature) {
            Log::warning('Webhook request missing signature', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Missing webhook signature',
            ], 401);
        }

        // Get the webhook secret from config
        $secret = config('app.webhook_secret');

        if (! $secret) {
            Log::error('Webhook secret not configured');

            return response()->json([
                'error' => 'Webhook configuration error',
            ], 500);
        }

        // Get raw body for signature verification
        $payload = $request->getContent();

        // Calculate expected signature
        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        // Verify signature
        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid webhook signature', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'provided_signature' => $signature,
                'expected_signature' => $expectedSignature,
            ]);

            return response()->json([
                'error' => 'Invalid signature',
            ], 401);
        }

        // Log successful webhook
        Log::info('Webhook signature verified', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        return $next($request);
    }
}
