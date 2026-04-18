<?php

namespace App\Exceptions;

/**
 * Exceção base para erros de integração externa.
 */
class IntegrationException extends \Exception
{
    public const ERROR_TOKEN_INVALID = 'TOKEN_INVALID';
    public const ERROR_BAD_REQUEST = 'BAD_REQUEST';
    public const ERROR_SERVER_ERROR = 'SERVER_ERROR';

    public const ERROR_TOKEN_REFRESH_FAILED = 'TOKEN_REFRESH_FAILED';
    public const ERROR_API_REQUEST_FAILED = 'API_REQUEST_FAILED';
    public const ERROR_API_RESPONSE_INVALID = 'API_RESPONSE_INVALID';
    public const ERROR_AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    public const ERROR_UNAUTHORIZED = 'UNAUTHORIZED';
    public const ERROR_NOT_FOUND = 'NOT_FOUND';
    public const ERROR_TIMEOUT = 'TIMEOUT';
    public const ERROR_RATE_LIMIT = 'RATE_LIMIT';

    private string $errorCode;
    private ?int $httpStatusCode;
    private ?array $context;
    private ?string $apiResponse;

    public function __construct(
        string $message,
        string $errorCode = self::ERROR_API_REQUEST_FAILED,
        ?int $httpStatusCode = null,
        ?array $context = null,
        ?string $apiResponse = null,
        ?\Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
        $this->context = $context;
        $this->apiResponse = $apiResponse;

        parent::__construct(
            message: $message,
            code: $httpStatusCode ?? 500,
            previous: $previous
        );
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getApiResponse(): ?string
    {
        return $this->apiResponse;
    }

    /**
     * Retorna um array com todas as informações do erro.
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'http_status_code' => $this->httpStatusCode,
            'context' => $this->context,
            'api_response' => $this->apiResponse,
        ];
    }

    /**
     * Verifica se o erro é recuperável (pode tentar novamente).
     */
    public function isRetryable(): bool
    {
        return in_array($this->errorCode, [
            self::ERROR_TIMEOUT,
            self::ERROR_RATE_LIMIT,
            self::ERROR_TOKEN_REFRESH_FAILED,
        ]);
    }

    /**
     * Cria uma exceção para token inválido.
     */
    public static function invalidToken(?array $context = null): self
    {
        return new self(
            message: "Token de autorização inválido.",
            errorCode: self::ERROR_TOKEN_INVALID,
            httpStatusCode: 401,
            context: $context
        );
    }

    /**
     * Cria uma exceção para falha no refresh do token.
     */
    public static function tokenRefreshFailed(?string $apiResponse = null, ?\Throwable $previous = null): self
    {
        return new self(
            message: "Falha ao renovar token de autorização.",
            errorCode: self::ERROR_TOKEN_REFRESH_FAILED,
            httpStatusCode: 401,
            apiResponse: $apiResponse,
            previous: $previous
        );
    }

    /**
     * Cria uma exceção para falha na requisição à API.
     */
    public static function apiRequestFailed(
        string $endpoint,
        ?int $httpStatusCode = null,
        ?string $apiResponse = null,
        ?\Throwable $previous = null
    ): self {
        return new self(
            message: "Falha na requisição para o endpoint '{$endpoint}'.",
            errorCode: self::ERROR_API_REQUEST_FAILED,
            httpStatusCode: $httpStatusCode,
            context: ['endpoint' => $endpoint],
            apiResponse: $apiResponse,
            previous: $previous
        );
    }

    /**
     * Cria uma exceção para resposta inválida da API.
     */
    public static function invalidApiResponse(string $reason, ?string $apiResponse = null): self
    {
        return new self(
            message: "Resposta inválida da API: {$reason}",
            errorCode: self::ERROR_API_RESPONSE_INVALID,
            httpStatusCode: 502,
            apiResponse: $apiResponse
        );
    }

    /**
     * Cria uma exceção para falha na autenticação.
     */
    public static function authenticationFailed(?string $apiResponse = null, ?\Throwable $previous = null): self
    {
        return new self(
            message: "Falha na autenticação.",
            errorCode: self::ERROR_AUTHENTICATION_FAILED,
            httpStatusCode: 401,
            apiResponse: $apiResponse,
            previous: $previous
        );
    }

    /**
     * Cria uma exceção para não autorizado.
     */
    public static function unauthorized(?string $apiResponse = null): self
    {
        return new self(
            message: "Acesso não autorizado.",
            errorCode: self::ERROR_UNAUTHORIZED,
            httpStatusCode: 401,
            apiResponse: $apiResponse
        );
    }

    /**
     * Cria uma exceção para recurso não encontrado.
     */
    public static function notFound(string $resource, ?string $apiResponse = null): self
    {
        return new self(
            message: "Recurso '{$resource}' não encontrado.",
            errorCode: self::ERROR_NOT_FOUND,
            httpStatusCode: 404,
            context: ['resource' => $resource],
            apiResponse: $apiResponse
        );
    }

    /**
     * Cria uma exceção para timeout.
     */
    public static function timeout(string $endpoint, ?\Throwable $previous = null): self
    {
        return new self(
            message: "Timeout na requisição para o endpoint '{$endpoint}'.",
            errorCode: self::ERROR_TIMEOUT,
            httpStatusCode: 504,
            context: ['endpoint' => $endpoint],
            previous: $previous
        );
    }

    /**
     * Cria uma exceção para rate limit.
     */
    public static function rateLimitExceeded(?string $apiResponse = null): self
    {
        return new self(
            message: "Limite de requisições excedido.",
            errorCode: self::ERROR_RATE_LIMIT,
            httpStatusCode: 429,
            apiResponse: $apiResponse
        );
    }
}
