<?php

use App\Http\Controllers\VerseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VerseController::class, 'index']);
Route::get('/api/verse', [VerseController::class, 'verse']);
