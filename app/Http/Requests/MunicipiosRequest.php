<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class MunicipiosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estadosValidos = [
            'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche',
            'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima',
            'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'México',
            'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla',
            'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora',
            'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'
        ];

        return [
            'estado' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::in($estadosValidos)
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'estado' => 'nombre del estado',
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El :attribute es obligatorio.',
            'estado.string' => 'El :attribute debe ser texto.',
            'estado.min' => 'El :attribute debe tener al menos :min caracteres.',
            'estado.max' => 'El :attribute no puede tener más de :max caracteres.',
            'estado.in' => 'El :attribute seleccionado no es válido. Debe ser uno de los 32 estados de México.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $estado = $this->input('estado');
            
            if ($estado) {
                $estadoExiste = \App\Models\Estado::where('nombre', trim($estado))->exists();
                
                if (!$estadoExiste) {
                    $validator->errors()->add(
                        'estado', 
                        "El estado '{$estado}' no se encuentra en la base de datos. Primero debe cargar los estados desde COPOMEX."
                    );
                }
            }
        });
    }

    public function getEstadoNormalizado(): string
    {
        return trim($this->validated()['estado']);
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'El estado proporcionado no es válido.',
                'errors' => $errors,
                'data' => null,
                'available_estados' => [
                    'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche',
                    'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima',
                    'Durango', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'México',
                    'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla',
                    'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora',
                    'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'
                ]
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
          $this->merge([
        'estado' => $this->route('estado') ?? $this->input('estado')

    ]);
    }
}