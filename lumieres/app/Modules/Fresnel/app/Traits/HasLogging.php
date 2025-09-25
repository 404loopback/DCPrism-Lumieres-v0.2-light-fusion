<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait HasLogging
{
    /**
     * Enable/disable logging for this instance
     */
    protected bool $loggingEnabled = true;

    /**
     * Context data to include in all log entries
     */
    protected array $logContext = [];

    /**
     * Get the component name for logging
     */
    protected function getLogComponent(): string
    {
        return class_basename(static::class);
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        if (! $this->loggingEnabled) {
            return;
        }

        Log::info($this->formatLogMessage($message), $this->buildLogContext($context));
    }

    /**
     * Log error message
     */
    protected function logError(string $message, array $context = []): void
    {
        if (! $this->loggingEnabled) {
            return;
        }

        Log::error($this->formatLogMessage($message), $this->buildLogContext($context));
    }

    /**
     * Log warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        if (! $this->loggingEnabled) {
            return;
        }

        Log::warning($this->formatLogMessage($message), $this->buildLogContext($context));
    }

    /**
     * Log debug message
     */
    protected function logDebug(string $message, array $context = []): void
    {
        if (! $this->loggingEnabled || ! app()->environment(['local', 'testing'])) {
            return;
        }

        Log::debug($this->formatLogMessage($message), $this->buildLogContext($context));
    }

    /**
     * Log with custom level
     */
    protected function logCustom(string $level, string $message, array $context = []): void
    {
        if (! $this->loggingEnabled) {
            return;
        }

        Log::log($level, $this->formatLogMessage($message), $this->buildLogContext($context));
    }

    /**
     * Format log message with component information
     */
    protected function formatLogMessage(string $message): string
    {
        $component = $this->getLogComponent();

        return "[{$component}] {$message}";
    }

    /**
     * Build log context with standard fields
     */
    protected function buildLogContext(array $context = []): array
    {
        return array_merge([
            'component' => $this->getLogComponent(),
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true),
        ], $this->logContext, $context);
    }

    /**
     * Log method execution with timing
     */
    protected function logExecution(string $method, callable $callback, array $context = [])
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $this->logInfo("Starting {$method}", $context);

            $result = $callback();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $memoryUsed = memory_get_usage(true) - $startMemory;

            $this->logInfo("Completed {$method}", array_merge($context, [
                'execution_time_ms' => $executionTime,
                'memory_used_bytes' => $memoryUsed,
                'success' => true,
            ]));

            return $result;

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->logError("Failed {$method}", array_merge($context, [
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]));

            throw $e;
        }
    }

    /**
     * Log operation with unique ID for tracking
     */
    protected function logOperation(string $operation, array $data = []): string
    {
        $operationId = Str::uuid()->toString();

        $this->logInfo("Operation started: {$operation}", [
            'operation_id' => $operationId,
            'operation' => $operation,
            'data' => $this->sanitizeLogData($data),
        ]);

        return $operationId;
    }

    /**
     * Log completion of operation by ID
     */
    protected function logOperationComplete(string $operationId, string $operation, array $result = []): void
    {
        $this->logInfo("Operation completed: {$operation}", [
            'operation_id' => $operationId,
            'operation' => $operation,
            'result' => $this->sanitizeLogData($result),
        ]);
    }

    /**
     * Log failure of operation by ID
     */
    protected function logOperationFailed(string $operationId, string $operation, \Exception $exception): void
    {
        $this->logError("Operation failed: {$operation}", [
            'operation_id' => $operationId,
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    /**
     * Sanitize sensitive data in log context
     */
    protected function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'credential', 'api_key'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (is_string($key) &&
                collect($sensitiveKeys)->some(fn ($sensitive) => str_contains(strtolower($key), $sensitive))) {
                $value = '***REDACTED***';
            }
        });

        return $data;
    }

    /**
     * Set additional context for all log entries
     */
    public function setLogContext(array $context): self
    {
        $this->logContext = array_merge($this->logContext, $context);

        return $this;
    }

    /**
     * Enable/disable logging
     */
    public function setLoggingEnabled(bool $enabled): self
    {
        $this->loggingEnabled = $enabled;

        return $this;
    }

    /**
     * Clear log context
     */
    public function clearLogContext(): self
    {
        $this->logContext = [];

        return $this;
    }
}
