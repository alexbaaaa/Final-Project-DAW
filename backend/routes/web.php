<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BackController;

Route::get('/back', [BackController::class, 'index']);
