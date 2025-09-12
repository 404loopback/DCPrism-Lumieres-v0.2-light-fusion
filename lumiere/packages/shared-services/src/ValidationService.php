<?php

namespace Lumieres\Shared\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Shared Validation Service
 * 
 * Common validation rules and methods used across Lumières applications
 */
class ValidationService
{
    /**
     * Common validation rules
     */
    public const RULES = [
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:8|confirmed',
        'name' => 'required|string|max:255',
        'status' => 'required|in:active,inactive,pending',
        'role' => 'required|in:admin,manager,user,viewer',
        'phone' => 'nullable|string|max:20',
        'url' => 'nullable|url|max:255',
        'date' => 'nullable|date',
        'datetime' => 'nullable|date_format:Y-m-d H:i:s',
    ];

    /**
     * Validate email format
     */
    public static function validateEmail(string $email): bool
    {
        $validator = Validator::make(['email' => $email], [
            'email' => self::RULES['email']
        ]);

        return !$validator->fails();
    }

    /**
     * Validate password strength
     */
    public static function validatePassword(string $password, ?string $confirmation = null): bool
    {
        $data = ['password' => $password];
        $rules = ['password' => 'required|string|min:8'];

        if ($confirmation !== null) {
            $data['password_confirmation'] = $confirmation;
            $rules['password'] .= '|confirmed';
        }

        $validator = Validator::make($data, $rules);
        return !$validator->fails();
    }

    /**
     * Validate user data for creation
     */
    public static function validateUserData(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => self::RULES['name'],
            'email' => self::RULES['email'] . '|unique:users,email',
            'password' => self::RULES['password'],
            'role' => self::RULES['role'],
            'status' => 'nullable|' . explode('required|', self::RULES['status'])[1],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate user data for update
     */
    public static function validateUserUpdateData(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|' . explode('required|', self::RULES['name'])[1],
            'email' => 'sometimes|' . self::RULES['email'] . "|unique:users,email,{$userId}",
            'password' => 'sometimes|' . self::RULES['password'],
            'role' => 'sometimes|' . self::RULES['role'],
            'status' => 'sometimes|' . explode('required|', self::RULES['status'])[1],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload(array $data, array $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']): array
    {
        $validator = Validator::make($data, [
            'file' => 'required|file|max:10240|mimes:' . implode(',', $allowedTypes),
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Sanitize input data
     */
    public static function sanitizeInput(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim(strip_tags($value));
            }
            return $value;
        }, $data);
    }

    /**
     * Validate and sanitize input
     */
    public static function validateAndSanitize(array $data, array $rules): array
    {
        $sanitized = self::sanitizeInput($data);
        
        $validator = Validator::make($sanitized, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
