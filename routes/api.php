<?php

use App\Http\Controllers\Api\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/midtrans/static-qris', [MidtransWebhookController::class, 'handleStaticQris'])
    ->middleware('throttle:120,1')
    ->name('api.webhooks.midtrans.static-qris');