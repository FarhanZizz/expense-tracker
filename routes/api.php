<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentSourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\TelegramController;

Route::post('telegram/webhook', [TelegramController::class, 'handle']);
Route::apiResource('sources', PaymentSourceController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('transactions', TransactionController::class);
Route::get('summary', [SummaryController::class, 'index']);
Route::get('summary/monthly', [SummaryController::class, 'monthly']);

Route::get('reset-db', function () {
    \Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    return response()->json(['message' => 'Done']);
});