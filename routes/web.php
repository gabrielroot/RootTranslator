<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/languages', function () {
    $response = Http::get(env('LIBRETRANSLATE_HOST') . '/languages');
    
    return response()->json(
        $response->json(),
        $response->status()
    );
});

Route::post('/api/detect', function () {
    if (!$apiKey = env('LIBRETRANSLATE_KEY')) {
        return response()->json(['message' => 'API key not configured'], 500);
    }

    $response = Http::withBody(json_encode(array_merge(request()->all(), ['api_key' => $apiKey])))
        ->post(env('LIBRETRANSLATE_HOST') . '/detect');

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
});

Route::post('/api/translate', function () {
    if (!$apiKey = env('LIBRETRANSLATE_KEY')) {
        return response()->json(['message' => 'API key not configured'], 500);
    }

    $body = request()->all();

    if (strlen($body['q']) == 0) {
        return response()->json(['message' => 'Text cannot be empty'], 400);
    }

    if (strlen($body['q']) > 500) {
        return response()->json(['message' => 'Text exceeds maximum length of 500 characters'], 400);
    }

    $response = Http::withBody(json_encode(array_merge(
        $body, 
        [
            'alternatives' => 3,
            'api_key' => $apiKey
        ])))
    ->post(env('LIBRETRANSLATE_HOST') . '/translate');
    
    return response()->json(
        $response->json(),
        $response->status()
    );
});