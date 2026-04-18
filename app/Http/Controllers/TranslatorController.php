<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;

class TranslatorController extends Controller
{
    public function getLanguages(TranslationService $translationService) {
        $response = $translationService->getLanguages();

        if ($response->isSuccess()) {
            return response()->json(
                $response->getData(),
                $response->getHttpStatusCode()
            );
        }

        return response()->json(
            ['message' => $response->getMessage(), 'meta' => $response->getMeta()],
            $response->getHttpStatusCode() ?? 500
        );
    }

    public function detectLanguage(TranslationService $translationService) {
        $response = $translationService->detectLanguage(request()->all());

        if ($response->isSuccess()) {
            return response()->json(
                $response->getData(),
                $response->getHttpStatusCode()
            );
        }

        return response()->json(
            ['message' => $response->getMessage(), 'meta' => $response->getMeta()],
            $response->getHttpStatusCode() ?? 500
        );
    }

    public function translate(TranslationService $translationService) {
        $response = $translationService->translate(request()->all());

        if ($response->isSuccess()) {
            return response()->json(
                $response->getData(),
                $response->getHttpStatusCode()
            );
        }

        return response()->json(
            ['message' => $response->getMessage(), 'meta' => $response->getMeta()],
            $response->getHttpStatusCode() ?? 400
        );
    }
}
