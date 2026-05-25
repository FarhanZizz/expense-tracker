<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentSourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SummaryController;

Route::apiResource('sources', PaymentSourceController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('transactions', TransactionController::class);
Route::get('summary', [SummaryController::class, 'index']);
Route::get('summary/monthly', [SummaryController::class, 'monthly']);