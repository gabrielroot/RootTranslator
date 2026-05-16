<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\DTO\IntegrationResponse;

abstract class Controller
{
    protected function buildApiResponse(IntegrationResponse $response): JsonResponse
    {
        if ($response->isSuccess()) {
            return response()->json(
                $response->getData(),
                $response->getHttpStatusCode()
            );
        }

        return response()->json(
            ['message' => $response->getMessage(), 'meta' => $response->getMeta(), 'timestamp' => $response->getTimestamp()->format('c')],
            $response->getHttpStatusCode() ?? 500
        );
    }
}
