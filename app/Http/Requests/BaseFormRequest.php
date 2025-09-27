<?php
// app/Http/Requests/BaseFormRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt with standardized format.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            $this->buildValidationErrorResponse($errors)
        );
    }

    /**
     * Build validation error response.
     */
    protected function buildValidationErrorResponse(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getValidationErrorMessage(),
            'errors' => $errors,
            'data' => null,
            'timestamp' => now()->toISOString()
        ], 422);
    }

    /**
     * Get validation error message.
     */
    protected function getValidationErrorMessage(): string
    {
        return 'Los datos enviados contienen errores de validación.';
    }

    /**
     * Get sanitized input data.
     */
    protected function getSanitizedInput(string $key, mixed $default = null): mixed
    {
        $value = $this->input($key, $default);
        
        if (is_string($value)) {
            return trim($value);
        }
        
        return $value;
    }

    /**
     * Convert string boolean to actual boolean.
     */
    protected function getBooleanInput(string $key, bool $default = false): bool
    {
        $value = $this->input($key);
        
        if (is_null($value)) {
            return $default;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * Get integer input with validation.
     */
    protected function getIntegerInput(string $key, int $default = 0, int $min = null, int $max = null): int
    {
        $value = $this->input($key, $default);
        $intValue = (int) $value;
        
        if (!is_null($min) && $intValue < $min) {
            return $min;
        }
        
        if (!is_null($max) && $intValue > $max) {
            return $max;
        }
        
        return $intValue;
    }
}