<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\VerseController;
use App\Http\Middleware\AdminKeyMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', [VerseController::class, 'index']);
Route::get('/api/verse', [VerseController::class, 'verse']);

Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(AdminKeyMiddleware::class);
