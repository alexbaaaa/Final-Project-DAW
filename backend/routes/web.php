<?php

use App\Http\Controllers\BackController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SwimmerController;
use App\Http\Controllers\TimeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BackController::class, 'index'])->name('admin.index');

Route::get('/login', [BackController::class, 'login'])->name('admin.login');
Route::post('/login', [BackController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/home', [BackController::class, 'home'])->name('admin.home');

Route::get('/swimmers', [SwimmerController::class, 'index'])->name('admin.swimmers.index');
Route::get('/swimmers/create', [SwimmerController::class, 'create'])->name('admin.swimmers.create');
Route::post('/swimmers', [SwimmerController::class, 'store'])->name('admin.swimmers.store');
Route::get('/swimmers/{swimmer}', [SwimmerController::class, 'show'])->name('admin.swimmers.show');
Route::get('/swimmers/{swimmer}/edit', [SwimmerController::class, 'edit'])->name('admin.swimmers.edit');
Route::put('/swimmers/{swimmer}', [SwimmerController::class, 'update'])->name('admin.swimmers.update');
Route::delete('/swimmers/{swimmer}', [SwimmerController::class, 'destroy'])->name('admin.swimmers.destroy');

Route::get('/events', [EventController::class, 'index'])->name('admin.events.index');
Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
Route::post('/events', [EventController::class, 'store'])->name('admin.events.store');
Route::get('/events/{event}', [EventController::class, 'show'])->name('admin.events.show');
Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
Route::put('/events/{event}', [EventController::class, 'update'])->name('admin.events.update');
Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('admin.events.destroy');

Route::get('/times', [TimeController::class, 'index'])->name('admin.times.index');
Route::get('/times/create', [TimeController::class, 'create'])->name('admin.times.create');
Route::post('/times', [TimeController::class, 'store'])->name('admin.times.store');
Route::get('/times/{time}', [TimeController::class, 'show'])->name('admin.times.show');
Route::get('/times/{time}/edit', [TimeController::class, 'edit'])->name('admin.times.edit');
Route::put('/times/{time}', [TimeController::class, 'update'])->name('admin.times.update');
Route::delete('/times/{time}', [TimeController::class, 'destroy'])->name('admin.times.destroy');
