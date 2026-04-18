<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use App\DTO\IntegrationResponse;
use App\Exceptions\IntegrationException;

class TranslatorController extends Controller
{
    public function getLanguages(TranslationService $translationService) {
        $body = request()->all();

        try {
            $response = $translationService->getLanguages();
        } catch (\Throwable $exception) {
            $response = IntegrationResponse::error(
                message: 'Ocorreu um erro no servidor durante o processamento',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                meta: ['body' => $body, 'exception' => $exception->getMessage()]
            );
        }
        
        return $this->buildApiResponse($response);
    }

    public function detectLanguage(TranslationService $translationService) {
        $body = request()->all();

        try {
            $response = $translationService->detectLanguage(request()->all());
        } catch (\Throwable $exception) {
            $response = IntegrationResponse::error(
                message: 'Ocorreu um erro no servidor durante o processamento',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                meta: ['body' => $body, 'exception' => $exception->getMessage()]
            );
        }

        return $this->buildApiResponse($response);
    }

    public function translate(TranslationService $translationService) {
        $body = request()->all();

        try {
            $response = $translationService->translate($body);
        } catch (\Throwable $exception) {
            $response = IntegrationResponse::error(
                message: 'Ocorreu um erro no servidor durante o processamento',
                errorCode: IntegrationException::ERROR_SERVER_ERROR,
                httpStatusCode: 500,
                meta: ['body' => $body, 'exception' => $exception->getMessage()]
            );
        }

        return $this->buildApiResponse($response);
    }
}
