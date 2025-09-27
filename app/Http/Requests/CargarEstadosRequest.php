<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CargarEstadosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'force' => ['sometimes', 'boolean'],
            'clear_cache' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'force' => 'forzar recarga',
            'clear_cache' => 'limpiar caché',
        ];
    }

    public function messages(): array
    {
        return [
            'force.boolean' => 'El parámetro :attribute debe ser verdadero o falso.',
            'clear_cache.boolean' => 'El parámetro :attribute debe ser verdadero o falso.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (empty(config('services.copomex.token'))) {
                $validator->errors()->add(
                    'copomex_token', 
                    'El token de COPOMEX no está configurado en las variables de entorno.'
                );
            }

            if (empty(config('services.copomex.base_url'))) {
                $validator->errors()->add(
                    'copomex_url', 
                    'La URL base de COPOMEX no está configurada.'
                );
            }
        });
    }

    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        return [
            'force' => $validated['force'] ?? false,
            'clear_cache' => $validated['clear_cache'] ?? false,
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos enviados.',
                'errors' => $errors,
                'data' => null
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('force')) {
            $this->merge([
                'force' => filter_var($this->force, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
            ]);
        }

        if ($this->has('clear_cache')) {
            $this->merge([
                'clear_cache' => filter_var($this->clear_cache, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false
            ]);
        }
    }
}
