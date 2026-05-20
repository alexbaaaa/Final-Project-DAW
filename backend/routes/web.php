<?php

use Illuminate\Support\Facades\Route;


Route::get('/welcome', [BackController::class, 'index']);
