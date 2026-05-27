<?php

use App\Http\Controllers\BackController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SwimmerController;
use App\Http\Controllers\TimeController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'swimming-up-api',
    ]);
});

Route::get('/', [BackController::class, 'index'])->name('index');

Route::middleware('web')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [BackController::class, 'index'])->name('index');
        Route::get('/login', [BackController::class, 'login'])->name('login');
        Route::post('/login', [BackController::class, 'authenticate'])->name('authenticate');
        Route::get('/home', [BackController::class, 'home'])->name('home');

        Route::get('/swimmers', [SwimmerController::class, 'index'])->name('swimmers.index');
        Route::get('/swimmers/create', [SwimmerController::class, 'create'])->name('swimmers.create');
        Route::post('/swimmers', [SwimmerController::class, 'store'])->name('swimmers.store');
        Route::get('/swimmers/{swimmer}', [SwimmerController::class, 'show'])->name('swimmers.show');
        Route::get('/swimmers/{swimmer}/edit', [SwimmerController::class, 'edit'])->name('swimmers.edit');
        Route::put('/swimmers/{swimmer}', [SwimmerController::class, 'update'])->name('swimmers.update');
        Route::delete('/swimmers/{swimmer}', [SwimmerController::class, 'destroy'])->name('swimmers.destroy');

        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        Route::get('/times', [TimeController::class, 'index'])->name('times.index');
        Route::get('/times/create', [TimeController::class, 'create'])->name('times.create');
        Route::post('/times', [TimeController::class, 'store'])->name('times.store');
        Route::get('/times/{time}', [TimeController::class, 'show'])->name('times.show');
        Route::get('/times/{time}/edit', [TimeController::class, 'edit'])->name('times.edit');
        Route::put('/times/{time}', [TimeController::class, 'update'])->name('times.update');
        Route::delete('/times/{time}', [TimeController::class, 'destroy'])->name('times.destroy');
    });
});
