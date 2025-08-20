<?php

use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::post('/orders/{id}/advance', [OrderController::class, 'advance']);
