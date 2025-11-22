<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GiftController;

Route::prefix('v1')->group(function () {
    // Customer APIs
    Route::get('/customers', [CustomerController::class, 'getCustomers']);
    Route::get('/customer-filter-options', [CustomerController::class, 'getFilterOptions']);
    
    // Gift APIs
    Route::get('/gift-summary', [GiftController::class, 'getGiftSummary']);
    Route::post('/gift-confirmation', [GiftController::class, 'confirmGift']);
    Route::put('/gift-update/{id}', [GiftController::class, 'updateGiftStatus']);
    Route::get('/test-connection', [CustomerController::class, 'testConnection']);
});