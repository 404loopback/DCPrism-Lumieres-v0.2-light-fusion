<?php

namespace Modules\Fresnel\app\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HasValidation
{
    /**
     * Validate data against rules
     */
    protected function validateData(array $data, array $rules, array $messages = []): array
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate data and return boolean result
     */
    protected function isValid(array $data, array $rules): bool
    {
        try {
            $this->validateData($data, $rules);

            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Get validation errors without throwing exception
     */
    protected function getValidationErrors(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);

        return $validator->errors()->toArray();
    }

    /**
     * Validate file upload
     */
    protected function validateFile($file, array $rules = []): bool
    {
        $defaultRules = [
            'file' => 'required|file|max:'.(1024 * 1024 * 500), // 500MB default
        ];

        $rules = array_merge($defaultRules, $rules);

        return $this->isValid(['file' => $file], $rules);
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput(array $data, array $allowedFields = []): array
    {
        if (empty($allowedFields)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($allowedFields));
    }

    /**
     * Apply default validation rules for common fields
     */
    protected function getDefaultRules(string $type = 'basic'): array
    {
        return match ($type) {
            'email' => ['email' => 'required|email|max:255'],
            'password' => ['password' => 'required|min:8|confirmed'],
            'name' => ['name' => 'required|string|max:255'],
            'file' => ['file' => 'required|file|max:51200'], // 50MB
            'image' => ['image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'], // 10MB
            'dcp' => [
                'file' => 'required|file',
                'title' => 'required|string|max:255',
                'format' => 'required|in:2K,4K,3D',
            ],
            default => []
        };
    }
}
