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
        $response = $translationService->detectLanguage(request()->all());

        return $this->buildApiResponse($response);
    }

    public function translate(TranslationService $translationService) {
        $response = $translationService->translate(request()->all());

        return $this->buildApiResponse($response);
    }
}
