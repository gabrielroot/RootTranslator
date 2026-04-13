<?php

use App\Http\Controllers\TranslatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/languages', [TranslatorController::class, 'getLanguages']);

Route::post('/api/detect', [TranslatorController::class, 'detectLanguage']);

Route::post('/api/translate', [TranslatorController::class, 'translate']);