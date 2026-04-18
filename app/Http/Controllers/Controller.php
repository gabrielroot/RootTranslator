<?php

namespace App\Http\Controllers;

use App\DTO\IntegrationResponse;

abstract class Controller
{
    protected function buildApiResponse(IntegrationResponse $response): \Illuminate\Http\JsonResponse
    {
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
}
