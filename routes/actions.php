<?php

use Illuminate\Support\Facades\Route;
use ThomasVantuycom\StatamicMollie\Http\Controllers\WebhookController;

Route::post('webhook', WebhookController::class)
    ->withoutMiddleware([
        'App\Http\Middleware\VerifyCsrfToken',
        'Illuminate\Foundation\Http\Middleware\VerifyCsrfToken',
        'Illuminate\Foundation\Http\Middleware\PreventRequestForgery',
    ])
    ->name('mollie.webhook');
