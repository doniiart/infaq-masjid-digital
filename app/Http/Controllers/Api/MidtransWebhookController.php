<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Midtrans\StaticQrisWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handleStaticQris(Request $request, StaticQrisWebhookService $webhookService): JsonResponse
    {
        $result = $webhookService->handle(
            payload: $request->all(),
            headers: $request->headers->all()
        );

        $httpStatus = $result['http_status'];
        unset($result['http_status']);

        return response()->json($result, $httpStatus);
    }
}