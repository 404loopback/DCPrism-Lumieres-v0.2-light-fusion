<?php

namespace Modules\Fresnel\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class Controller
{
    /**
     * Default pagination limit
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum pagination limit
     */
    protected int $maxPerPage = 100;

    /**
     * Enable/disable request logging
     */
    protected bool $logRequests = true;

    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $this->logValidationFailure($request, $validator->errors()->toArray());
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Return success JSON response
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $status = HttpResponse::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error JSON response
     */
    protected function errorResponse(string $message, mixed $errors = null, int $status = HttpResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return paginated JSON response
     */
    protected function paginatedResponse($paginator, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Handle resource not found
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            "{$resource} not found",
            null,
            HttpResponse::HTTP_NOT_FOUND
        );
    }

    /**
     * Handle unauthorized access
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            null,
            HttpResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Handle forbidden access
     */
    protected function forbiddenResponse(string $message = 'Access forbidden'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            null,
            HttpResponse::HTTP_FORBIDDEN
        );
    }

    /**
     * Handle validation error response
     */
    protected function validationErrorResponse(ValidationException $e): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            $e->validator->errors(),
            HttpResponse::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        $perPage = (int) $request->get('per_page', $this->defaultPerPage);
        $perPage = min($perPage, $this->maxPerPage);
        $perPage = max($perPage, 1);

        return [
            'page' => (int) $request->get('page', 1),
            'per_page' => $perPage,
        ];
    }

    /**
     * Get sort parameters from request
     */
    protected function getSortParams(Request $request, array $allowedFields = []): array
    {
        $sort = $request->get('sort', 'created_at');
        $direction = 'asc';

        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
            $direction = 'desc';
        }

        // Validate sort field if allowed fields are specified
        if (! empty($allowedFields) && ! in_array($sort, $allowedFields)) {
            $sort = 'created_at';
        }

        return [
            'field' => $sort,
            'direction' => $direction,
        ];
    }

    /**
     * Get filter parameters from request
     */
    protected function getFilterParams(Request $request, array $allowedFilters = []): array
    {
        $filters = [];

        foreach ($allowedFilters as $filter) {
            if ($request->has($filter) && $request->filled($filter)) {
                $filters[$filter] = $request->get($filter);
            }
        }

        return $filters;
    }

    /**
     * Log request for debugging/monitoring
     */
    protected function logRequest(Request $request, array $context = []): void
    {
        if (! $this->logRequests) {
            return;
        }

        Log::info('API Request', array_merge([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString(),
        ], $context));
    }

    /**
     * Log validation failure
     */
    protected function logValidationFailure(Request $request, array $errors): void
    {
        Log::warning('Validation failed', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'errors' => $errors,
            'input' => $this->sanitizeInput($request->all()),
            'user_id' => $request->user()?->id,
        ]);
    }

    /**
     * Sanitize input for logging (remove sensitive data)
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'key'];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $input)) {
                $input[$key] = '***REDACTED***';
            }
        }

        return $input;
    }

    /**
     * Handle general exception and return appropriate response
     */
    protected function handleException(\Exception $e, Request $request): JsonResponse
    {
        Log::error('Controller exception', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
        ]);

        if ($e instanceof ValidationException) {
            return $this->validationErrorResponse($e);
        }

        if (app()->environment('production')) {
            return $this->errorResponse(
                'An internal server error occurred',
                null,
                HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->errorResponse(
            $e->getMessage(),
            [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ],
            HttpResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Check if request wants JSON response
     */
    protected function wantsJson(Request $request): bool
    {
        return $request->wantsJson() || $request->is('api/*');
    }

    /**
     * Set request logging enabled/disabled
     */
    protected function setRequestLogging(bool $enabled): self
    {
        $this->logRequests = $enabled;

        return $this;
    }
}
