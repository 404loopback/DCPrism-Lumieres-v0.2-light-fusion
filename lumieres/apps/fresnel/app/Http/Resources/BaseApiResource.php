<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

abstract class BaseApiResource extends JsonResource
{
    /**
     * Default date format for API responses
     */
    protected string $dateFormat = 'Y-m-d\TH:i:s.u\Z';

    /**
     * Fields that should be hidden from API response by default
     */
    protected array $hiddenFields = [
        'password', 'remember_token', 'api_token', 'secret'
    ];

    /**
     * Transform the resource into an array.
     */
    abstract public function toArray(Request $request): array;

    /**
     * Format date for API response
     */
    protected function formatDate(?Carbon $date): ?string
    {
        return $date?->format($this->dateFormat);
    }

    /**
     * Format file size in human readable format
     */
    protected function formatFileSize(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format duration in seconds to human readable format
     */
    protected function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    /**
     * Check if user has permission for a specific action on the resource
     */
    protected function userCan(Request $request, string $ability, ?string $model = null): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $model = $model ?? $this->resource;
        
        return $user->can($ability, $model);
    }

    /**
     * Check if user has any of the specified permissions
     */
    protected function userCanAny(Request $request, array $abilities, ?string $model = null): bool
    {
        $user = $request->user();
        
        if (!$user) {
            return false;
        }

        $model = $model ?? $this->resource;
        
        foreach ($abilities as $ability) {
            if ($user->can($ability, $model)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Include field only if user has permission
     */
    protected function whenCan(Request $request, string $ability, mixed $value, ?string $model = null): mixed
    {
        return $this->when($this->userCan($request, $ability, $model), $value);
    }

    /**
     * Include field only if condition is true and user has permission
     */
    protected function whenCanAnd(Request $request, bool $condition, string $ability, mixed $value, ?string $model = null): mixed
    {
        return $this->when($condition && $this->userCan($request, $ability, $model), $value);
    }

    /**
     * Generate API links for the resource
     */
    protected function generateLinks(array $routes = []): array
    {
        $links = [];
        
        foreach ($routes as $name => $route) {
            if (is_array($route)) {
                $links[$name] = [
                    'href' => $route['href'] ?? null,
                    'method' => $route['method'] ?? 'GET',
                    'type' => $route['type'] ?? 'application/json'
                ];
            } else {
                $links[$name] = $route;
            }
        }
        
        return $links;
    }

    /**
     * Get the resource identifier
     */
    protected function getResourceId(): mixed
    {
        return $this->resource->id ?? $this->resource->getKey() ?? null;
    }

    /**
     * Get the resource type name
     */
    protected function getResourceType(): string
    {
        return strtolower(class_basename($this->resource));
    }

    /**
     * Include sensitive data only in development/testing
     */
    protected function whenDebugging(mixed $value): mixed
    {
        return $this->when(app()->environment(['local', 'testing']), $value);
    }

    /**
     * Include field only if it's requested via query parameter
     */
    protected function whenRequested(Request $request, string $parameter, mixed $value, bool $default = false): mixed
    {
        $include = $request->boolean($parameter, $default);
        return $this->when($include, $value);
    }

    /**
     * Include relationship only if it's loaded
     */
    protected function whenLoadedAndCan(Request $request, string $relationship, string $ability): mixed
    {
        return $this->when(
            $this->relationLoaded($relationship) && $this->userCan($request, $ability),
            fn() => $this->getRelationValue($relationship)
        );
    }

    /**
     * Sanitize array by removing hidden fields
     */
    protected function sanitizeArray(array $data): array
    {
        foreach ($this->hiddenFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }

    /**
     * Get metadata about the resource
     */
    protected function getMetadata(Request $request): array
    {
        return [
            'type' => $this->getResourceType(),
            'id' => $this->getResourceId(),
            'links' => $this->generateBaseLinks(),
            'permissions' => $this->getPermissions($request)
        ];
    }

    /**
     * Get basic CRUD links for the resource
     */
    protected function generateBaseLinks(): array
    {
        $resourceType = $this->getResourceType();
        $resourceId = $this->getResourceId();
        
        if (!$resourceId) {
            return [];
        }

        return [
            'self' => route("api.v1.{$resourceType}s.show", $resourceId),
            'edit' => route("api.v1.{$resourceType}s.update", $resourceId),
            'delete' => route("api.v1.{$resourceType}s.destroy", $resourceId)
        ];
    }

    /**
     * Get user permissions for this resource
     */
    protected function getPermissions(Request $request): array
    {
        if (!$request->user()) {
            return [];
        }

        $abilities = ['view', 'update', 'delete'];
        $permissions = [];
        
        foreach ($abilities as $ability) {
            $permissions["can_{$ability}"] = $this->userCan($request, $ability);
        }
        
        return $permissions;
    }

    /**
     * Convert JSON string to array safely
     */
    protected function jsonToArray(?string $json): ?array
    {
        if ($json === null) {
            return null;
        }
        
        $decoded = json_decode($json, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * Get localized text
     */
    protected function trans(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }

    /**
     * Include computed/derived fields
     */
    protected function includeComputed(): array
    {
        return [];
    }

    /**
     * Get the additional data that should be included
     */
    protected function with($request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => config('app.version', '1.0.0')
            ]
        ];
    }

    /**
     * Determine if the resource collection or resource should include the given field.
     */
    protected function shouldInclude(Request $request, string $field): bool
    {
        $includes = $request->get('include', '');
        $includeArray = is_string($includes) ? explode(',', $includes) : [];
        
        return in_array($field, $includeArray);
    }

    /**
     * Filter resource data based on requested fields
     */
    protected function filterFields(array $data, Request $request): array
    {
        $fields = $request->get('fields');
        
        if (!$fields) {
            return $data;
        }
        
        $requestedFields = is_string($fields) ? explode(',', $fields) : $fields;
        
        return array_intersect_key($data, array_flip($requestedFields));
    }
}
