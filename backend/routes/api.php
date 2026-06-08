<?php

use App\Http\Controllers\FrontController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'swimming-up-api',
    ]);
});

Route::get('/front/users', [FrontController::class, 'users'])->name('front.users');
Route::post('/front/login', [FrontController::class, 'login'])->name('front.login');
Route::get('/front/app-data', [FrontController::class, 'appData'])->name('front.app_data');
