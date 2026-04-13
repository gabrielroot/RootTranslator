<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TranslationService
{

    private const MAX_TEXT_LENGTH = 500;

    private ?string $BASE_URL;

    private ?string $API_KEY;

    public function __construct(){}

    /**
     * Obtém os idiomas disponíveis.
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function getLanguages(): \Illuminate\Http\Client\Response
    {
        $response = Http::get($this->getBaseUrl() . '/languages');

        return $response;
    }

    /**
     * Detecta o idioma de um texto.
     *
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    public function detectLanguage(array $body): \Illuminate\Http\Client\Response
    {
        $response = Http::withBody(json_encode(array_merge($body, ['api_key' => $this->getApiKey()])))
            ->post($this->getBaseUrl() . '/detect');

        return $response;
    }

    /**
     * Realiza a tradução de um texto.
     *
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    public function translate(array $body): \Illuminate\Http\Client\Response
    {
        $this->validateInputSize($body['q']);

        $response = Http::withBody(json_encode(array_merge(
            $body, 
            [
                'alternatives' => 3,
                'api_key' => $this->getApiKey()
            ])))
        ->post($this->getBaseUrl() . '/translate');

        return $response;
    }

    public function validateInputSize(string $text): void
    {
        if (strlen($text) == 0) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        if (strlen($text) > self::MAX_TEXT_LENGTH) {
            throw new \InvalidArgumentException('Text exceeds maximum length of ' . self::MAX_TEXT_LENGTH . ' characters');
        }
    }

    private function getBaseUrl(): string
    {
        if (!$this->BASE_URL = env('LIBRETRANSLATE_HOST')) {
            throw new \RuntimeException('Base URL not configured');
        }

        return $this->BASE_URL;
    }

    private function getApiKey(): string
    {
        if (!$this->API_KEY = env('LIBRETRANSLATE_KEY')) {
            throw new \RuntimeException('API key not configured');
        }

        return $this->API_KEY;
    }
}
