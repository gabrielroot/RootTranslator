<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;

class TranslatorController extends Controller
{
    public function getLanguages(TranslationService $translationService) {
        $response = $translationService->getLanguages();
        
        return $this->buildApiResponse($response);
    }

    public function detectLanguage(TranslationService $translationService) {
        $body = request()->all();
        $response = $translationService->detectLanguage($body);

        return $this->buildApiResponse($response);
    }

    public function translate(TranslationService $translationService) {
        $body = request()->all();
        $response = $translationService->translate($body);

        return $this->buildApiResponse($response);
    }
}
