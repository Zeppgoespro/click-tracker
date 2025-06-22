<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClickController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/clicks', [ClickController::class, 'store']);

Route::post('/clicks/batch', [ClickController::class, 'batchStore']);
