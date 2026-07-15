<?php

use App\Http\Controllers\Api\Admin\AllergenController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\MenuItemController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

// Rotte pubbliche: nessuna registrazione richiesta, il cliente accede
// tramite qr_token del tavolo.
Route::get('/menu', [MenuController::class, 'index']);
Route::post('/session', [SessionController::class, 'store']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{sessionId}', [OrderController::class, 'show']);

// Rotte area admin: login pubblico, il resto protetto da token Sanctum.
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('menu-items', MenuItemController::class)->except(['show']);
        Route::apiResource('allergens', AllergenController::class)->except(['show']);
        Route::get('/orders', [AdminOrderController::class, 'index']);
    });
});
