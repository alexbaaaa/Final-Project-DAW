<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BackController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'swimming-up-api',
    ]);
});

Route::get('/', [BackController::class, 'index']);
