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

Route::post('/api/translate', function () {
    if (!$apiKey = env('LIBRETRANSLATE_KEY')) {
        return response()->json(['error' => 'API key not configured'], 500);
    }

    $response = Http::withBody(json_encode(array_merge(
        request()->all(), [
            'alternatives' => 3,
            'api_key' => $apiKey
        ])))
    ->post(env('LIBRETRANSLATE_HOST') . '/translate');
    
    return response()->json(
        $response->json(),
        $response->status()
    );
});