<?php

namespace App\DTO;

use App\Exceptions\IntegrationException;

/**
 * DTO para padronizar respostas de integrações externas.
 *
 * Uso:
 * ```php
 * // Sucesso
 * return IntegrationResponse::success($data);
 *
 * // Erro
 * return IntegrationResponse::error('Mensagem de erro', 'ERROR_CODE', 400);
 *
 * // A partir de uma exceção
 * return IntegrationResponse::fromException($exception);
 * ```
 */
class IntegrationResponse
{
    private bool $success;
    private mixed $data;
    private ?string $message;
    private ?string $errorCode;
    private ?int $httpStatusCode;
    private ?array $meta;

    private function __construct(
        bool $success,
        mixed $data = null,
        ?string $message = null,
        ?string $errorCode = null,
        ?int $httpStatusCode = null,
        ?array $meta = null
    ) {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
        $this->meta = $meta;
    }

    /**
     * Cria uma resposta de sucesso.
     */
    public static function success(mixed $data = null, ?string $message = null, ?array $meta = null): self
    {
        return new self(
            success: true,
            data: $data,
            message: $message,
            httpStatusCode: 200,
            meta: $meta
        );
    }

    /**
     * Cria uma resposta de erro.
     */
    public static function error(
        string $message,
        ?string $errorCode = null,
        ?int $httpStatusCode = null,
        mixed $data = null,
        ?array $meta = null
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            errorCode: $errorCode,
            httpStatusCode: $httpStatusCode ?? 500,
            meta: $meta
        );
    }

    /**
     * Cria uma resposta a partir de uma IntegrationException.
     */
    public static function fromException(IntegrationException $exception): self
    {
        return new self(
            success: false,
            data: null,
            message: $exception->getMessage(),
            errorCode: $exception->getErrorCode(),
            httpStatusCode: $exception->getHttpStatusCode(),
            meta: [
                'context' => $exception->getContext(),
                'api_response' => $exception->getApiResponse(),
                'retryable' => $exception->isRetryable(),
            ]
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage($message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /**
     * Converte para array.
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'http_status_code' => $this->httpStatusCode,
            'meta' => $this->meta,
        ], fn($value) => $value !== null);
    }

    /**
     * Lança uma exceção se a resposta for de erro.
     * Útil para quando você quer propagar o erro.
     *
     * @throws IntegrationException
     */
    public function throwIfError(): self
    {
        if (!$this->success) {
            throw new IntegrationException(
                message: $this->message ?? 'Erro desconhecido',
                errorCode: $this->errorCode ?? 'UNKNOWN_ERROR',
                httpStatusCode: $this->httpStatusCode ?? 500
            );
        }

        return $this;
    }

    /**
     * Retorna os dados ou um valor padrão se for erro.
     */
    public function getDataOrDefault(mixed $default = null): mixed
    {
        return $this->success ? $this->data : $default;
    }

    /**
     * Executa um callback com os dados se for sucesso.
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->success) {
            $callback($this->data);
        }
        return $this;
    }

    /**
     * Executa um callback se for erro.
     */
    public function onError(callable $callback): self
    {
        if (!$this->success) {
            $callback($this->message, $this->errorCode, $this->httpStatusCode);
        }
        return $this;
    }
}
