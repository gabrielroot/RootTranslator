<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

use App\DTO\IntegrationResponse;
use App\Exceptions\IntegrationException;

class TranslationService
{

    private const MAX_TEXT_LENGTH = 500;

    private ?string $BASE_URL;

    private ?string $API_KEY;

    public function __construct(){}

    /**
     * Obtém os idiomas disponíveis.
     *
     * @return IntegrationResponse
     */
    public function getLanguages(): IntegrationResponse
    {
        try {
            $key = "languages:" . md5($this->getBaseUrl() . '/languages');
            $value = Cache::remember($key, now()->addDays(1), function () {
                $response = Http::get($this->getBaseUrl() . '/languages');

                if ($response->failed()) {
                    throw new IntegrationException(
                        message: 'Failed to fetch languages',
                        errorCode: IntegrationException::ERROR_API_REQUEST_FAILED,
                        httpStatusCode: $response->status(),
                        apiResponse: $response->body()
                    );
                }

                return $response->json();
            });

            return IntegrationResponse::success($value);
        } catch (IntegrationException $integrationException) {
            return IntegrationResponse::error(
                message: $integrationException->getMessage(),
                errorCode: $integrationException->getErrorCode(),
                httpStatusCode: $integrationException->getHttpStatusCode() ?? 400,
                meta: [
                    'context' => $integrationException->getContext(),
                    'api_response' => $integrationException->getApiResponse()
                ]
            );
        } catch (\Exception $e) {
            return IntegrationResponse::fromException(new IntegrationException(
                message: 'Erro inesperado durante a tradução',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                context: ['endpoint' => $this->getBaseUrl() . '/languages']
            ));
        }
    }

    /**
     * Detecta o idioma de um texto.
     *
     * @param array $data
     * @return IntegrationResponse
     */
    public function detectLanguage(array $body): IntegrationResponse
    {
        try {
            $this->validateInputSize($body['q']);

            $key = "language-detection:" . md5(json_encode($body));
            $value = Cache::remember($key, now()->addDays(1), function () use ($body) {
                $response = Http::withBody(json_encode(array_merge($body, ['api_key' => $this->getApiKey()])))
                    ->post($this->getBaseUrl() . '/detect');

                if ($response->failed()) {
                    throw new IntegrationException(
                        message: 'Failed to detect language',
                        errorCode: IntegrationException::ERROR_API_REQUEST_FAILED,
                        httpStatusCode: $response->status(),
                        apiResponse: $response->body(),
                        context: $body
                    );
                }

                $detections = array_first($response->json())['language'];

                if (!$detections) {
                    throw new IntegrationException(
                        message: 'No language detected',
                        errorCode: IntegrationException::ERROR_NOT_FOUND,
                        httpStatusCode: 404,
                        apiResponse: $response->body(),
                        context: $body
                    );
                }

                return $detections;
            });

            return IntegrationResponse::success($value);
        } catch (IntegrationException $integrationException) {
            return IntegrationResponse::error(
                message: $integrationException->getMessage(),
                errorCode: $integrationException->getErrorCode(),
                httpStatusCode: $integrationException->getHttpStatusCode() ?? 400,
                meta: [
                    'context' => $integrationException->getContext(),
                    'api_response' => $integrationException->getApiResponse()
                ]
            );
        } catch (\Exception $e) {
            return IntegrationResponse::fromException(new IntegrationException(
                message: 'Erro inesperado durante a tradução',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                context: $body
            ));
        }
    }

    /**
     * Realiza a tradução de um texto.
     *
     * @param array $data
     * @return IntegrationResponse
     */
    public function translate(array $body): IntegrationResponse
    {
        try {
            $this->validateInputSize($body['q']);

            $key = "translation:" . md5(json_encode($body));
            $value = Cache::remember($key, now()->addCentury(), function () use ($body) {
                $response = Http::withBody(json_encode(array_merge(
                    $body, 
                    ['alternatives' => 3, 'api_key' => $this->getApiKey()]
                )))
                ->post($this->getBaseUrl() . '/translate');

                if ($response->failed()) {
                    throw new IntegrationException(
                        message: 'Failed to translate text',
                        errorCode: IntegrationException::ERROR_API_REQUEST_FAILED,
                        httpStatusCode: $response->status(),
                        apiResponse: $response->body(),
                        context: $body
                    );
                }

                return $response->json();
            });

            return IntegrationResponse::success($value);
        } catch (IntegrationException $integrationException) {
            return IntegrationResponse::error(
                message: $integrationException->getMessage(),
                errorCode: $integrationException->getErrorCode(),
                httpStatusCode: $integrationException->getHttpStatusCode() ?? 400,
                meta: [
                    'context' => $integrationException->getContext(),
                    'api_response' => $integrationException->getApiResponse()
                ]
            );
        } catch (\Exception $e) {
            return IntegrationResponse::fromException(new IntegrationException(
                message: 'Erro inesperado durante a tradução',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                context: $body
            ));
        }
    }

    public function validateInputSize(string $text): void
    {
        if (strlen($text) == 0) {
            throw new IntegrationException('Text cannot be empty', IntegrationException::ERROR_BAD_REQUEST);
        }

        if (strlen($text) > self::MAX_TEXT_LENGTH) {
            throw new IntegrationException('Text exceeds maximum length of ' . self::MAX_TEXT_LENGTH . ' characters', 
                IntegrationException::ERROR_BAD_REQUEST);
        }
    }

    private function getBaseUrl(): string
    {
        if (!$this->BASE_URL = env('LIBRETRANSLATE_HOST')) {
            throw new IntegrationException('Base URL not configured', IntegrationException::ERROR_BAD_REQUEST);
        }

        return $this->BASE_URL;
    }

    private function getApiKey(): string
    {
        if (!$this->API_KEY = env('LIBRETRANSLATE_KEY')) {
            throw new IntegrationException('API key not configured', IntegrationException::ERROR_AUTHENTICATION_FAILED);
        }

        return $this->API_KEY;
    }
}
