<?php

use App\Http\Controllers\BackController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SwimmerController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\TrainingGroupController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BackController::class, 'index'])->name('admin.index');

Route::get('/login', [BackController::class, 'login'])->name('admin.login');
Route::post('/login', [BackController::class, 'authenticate'])->name('admin.authenticate');
Route::post('/logout', [BackController::class, 'logout'])->middleware('admin.auth')->name('admin.logout');
Route::middleware('admin.auth')->group(function (): void {
    Route::get('/password/change', [BackController::class, 'editPassword'])->name('admin.password.edit');
    Route::post('/password/change', [BackController::class, 'updatePassword'])->name('admin.password.update');
});

Route::middleware(['admin.auth', 'admin.can:home'])->group(function (): void {
    Route::get('/home', [BackController::class, 'home'])->name('admin.home');
});

Route::middleware(['admin.auth', 'admin.can:manage-swimmers'])->group(function (): void {
    Route::get('/swimmers', [SwimmerController::class, 'index'])->name('admin.swimmers.index');
    Route::get('/swimmers/create', [SwimmerController::class, 'create'])->name('admin.swimmers.create');
    Route::post('/swimmers', [SwimmerController::class, 'store'])->name('admin.swimmers.store');
    Route::get('/swimmers/{swimmer}', [SwimmerController::class, 'show'])->name('admin.swimmers.show');
    Route::get('/swimmers/{swimmer}/edit', [SwimmerController::class, 'edit'])->name('admin.swimmers.edit');
    Route::put('/swimmers/{swimmer}', [SwimmerController::class, 'update'])->name('admin.swimmers.update');
    Route::delete('/swimmers/{swimmer}', [SwimmerController::class, 'destroy'])->name('admin.swimmers.destroy');
});

Route::middleware(['admin.auth', 'admin.can:manage-events'])->group(function (): void {
    Route::get('/events', [EventController::class, 'index'])->name('admin.events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
    Route::post('/events', [EventController::class, 'store'])->name('admin.events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('admin.events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('admin.events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('admin.events.destroy');
});

Route::middleware(['admin.auth', 'admin.can:manage-calendar'])->group(function (): void {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::get('/calendar/create', [CalendarController::class, 'create'])->name('admin.calendar.create');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('admin.calendar.store');
    Route::get('/calendar/{calendar}', [CalendarController::class, 'show'])->name('admin.calendar.show');
    Route::get('/calendar/{calendar}/edit', [CalendarController::class, 'edit'])->name('admin.calendar.edit');
    Route::put('/calendar/{calendar}', [CalendarController::class, 'update'])->name('admin.calendar.update');
    Route::delete('/calendar/{calendar}', [CalendarController::class, 'destroy'])->name('admin.calendar.destroy');
});

Route::middleware(['admin.auth', 'admin.can:manage-training-groups'])->group(function (): void {
    Route::get('/training-groups', [TrainingGroupController::class, 'index'])->name('admin.training_groups.index');
    Route::get('/training-groups/create', [TrainingGroupController::class, 'create'])->name('admin.training_groups.create');
    Route::post('/training-groups', [TrainingGroupController::class, 'store'])->name('admin.training_groups.store');
    Route::get('/training-groups/{trainingGroup}', [TrainingGroupController::class, 'show'])->name('admin.training_groups.show');
    Route::get('/training-groups/{trainingGroup}/edit', [TrainingGroupController::class, 'edit'])->name('admin.training_groups.edit');
    Route::put('/training-groups/{trainingGroup}', [TrainingGroupController::class, 'update'])->name('admin.training_groups.update');
    Route::delete('/training-groups/{trainingGroup}', [TrainingGroupController::class, 'destroy'])->name('admin.training_groups.destroy');
});

Route::middleware(['admin.auth', 'admin.can:manage-times'])->group(function (): void {
    Route::get('/times', [TimeController::class, 'index'])->name('admin.times.index');
    Route::get('/times/create', [TimeController::class, 'create'])->name('admin.times.create');
    Route::post('/times', [TimeController::class, 'store'])->name('admin.times.store');
    Route::get('/times/{time}', [TimeController::class, 'show'])->name('admin.times.show');
    Route::get('/times/{time}/edit', [TimeController::class, 'edit'])->name('admin.times.edit');
    Route::put('/times/{time}', [TimeController::class, 'update'])->name('admin.times.update');
    Route::delete('/times/{time}', [TimeController::class, 'destroy'])->name('admin.times.destroy');
});

Route::middleware(['admin.auth', 'admin.can:manage-app-users'])->group(function (): void {
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware(['admin.auth', 'admin.can:view-admin-users'])->group(function (): void {
    Route::get('/users-admin', [UserAdminController::class, 'index'])->name('admin.users_admin.index');
});

Route::middleware(['admin.auth', 'admin.can:manage-admin-users'])->group(function (): void {
    Route::get('/users-admin/create', [UserAdminController::class, 'create'])->name('admin.users_admin.create');
    Route::post('/users-admin', [UserAdminController::class, 'store'])->name('admin.users_admin.store');
    Route::get('/users-admin/{userAdmin}/edit', [UserAdminController::class, 'edit'])->name('admin.users_admin.edit');
    Route::put('/users-admin/{userAdmin}', [UserAdminController::class, 'update'])->name('admin.users_admin.update');
    Route::patch('/users-admin/{userAdmin}/toggle', [UserAdminController::class, 'toggle'])->name('admin.users_admin.toggle');
    Route::patch('/users-admin/{userAdmin}/reset-password', [UserAdminController::class, 'resetPassword'])->name('admin.users_admin.reset_password');
});

Route::middleware(['admin.auth', 'admin.can:delete-admin-users'])->group(function (): void {
    Route::delete('/users-admin/{userAdmin}', [UserAdminController::class, 'destroy'])->name('admin.users_admin.destroy');
});
