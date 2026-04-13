<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;

class TranslatorController extends Controller
{
    public function getLanguages(TranslationService $translationService) {
        try {
            $response = $translationService->getLanguages();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    public function detectLanguage(TranslationService $translationService) {
        try {
            $response = $translationService->detectLanguage(request()->all());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        if ($response->successful()) {
            $detections = $response->json();
            
            return response()->json(
                array_first($detections)['language'] ?? null,
                $response->status()
            );
        }

        return response()->json(
            $response->json(),
            $response->status()
        );
    }

    public function translate(TranslationService $translationService) {
        try {
            $response = $translationService->translate(request()->all());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        
        return response()->json(
            $response->json(),
            $response->status()
        );
    }
}
