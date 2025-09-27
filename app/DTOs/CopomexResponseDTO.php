<?php

namespace App\DTOs;

class CopomexResponseDTO
{
    public function __construct(
        public readonly bool $error,
        public readonly int $codeError,
        public readonly ?string $errorMessage,
        public readonly mixed $response
    ) {}

    public static function fromApiResponse(array $apiResponse): self
    {
        return new self(
            error: $apiResponse['error'] ?? true,
            codeError: $apiResponse['code_error'] ?? 1,
            errorMessage: $apiResponse['error_message'] ?? 'Error desconocido',
            response: $apiResponse['response'] ?? null
        );
    }

    public function isSuccess(): bool
    {
        return !$this->error && $this->codeError === 0;
    }

    public function getEstados(): array
    {
        if (!$this->isSuccess() || !isset($this->response['estado'])) {
            return [];
        }

        return is_array($this->response['estado'])
            ? $this->response['estado']
            : [];
    }

    public function getMunicipios(): array
    {

        if (!$this->isSuccess() || !isset($this->response['municipios'])) {
            return [];
        }

        return is_array($this->response['municipios'])
            ? $this->response['municipios']
            : [];
    }

    public function getErrorMessage(): string
    {
        if ($this->isSuccess()) {
            return '';
        }

        return $this->errorMessage ?: 'Error en la respuesta de COPOMEX';
    }

    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'code_error' => $this->codeError,
            'error_message' => $this->errorMessage,
            'response' => $this->response
        ];
    }
}
