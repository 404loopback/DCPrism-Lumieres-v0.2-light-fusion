<?php

/**
 * Shared Helper Functions for Lumières Applications
 */

if (!function_exists('lumieres_version')) {
    /**
     * Get Lumières version
     */
    function lumieres_version(): string
    {
        return '1.0.0';
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size in human readable format
     */
    function format_file_size(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('generate_uuid')) {
    /**
     * Generate a simple UUID v4
     */
    function generate_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('safe_json_decode')) {
    /**
     * Safely decode JSON with fallback
     */
    function safe_json_decode(string $json, bool $associative = true, $default = null)
    {
        $decoded = json_decode($json, $associative);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $default;
        }
        
        return $decoded;
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncate text to specified length
     */
    function truncate_text(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for safe storage
     */
    function sanitize_filename(string $filename): string
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Trim underscores from start/end
        return trim($filename, '_');
    }
}

if (!function_exists('is_valid_date')) {
    /**
     * Check if string is a valid date
     */
    function is_valid_date(string $date, string $format = 'Y-m-d'): bool
    {
        $datetime = DateTime::createFromFormat($format, $date);
        return $datetime && $datetime->format($format) === $date;
    }
}

if (!function_exists('array_get_nested')) {
    /**
     * Get nested array value with dot notation
     */
    function array_get_nested(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        
        return $array;
    }
}
