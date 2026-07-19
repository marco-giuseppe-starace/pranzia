<?php

use App\Http\Controllers\Api\Admin\AiCostController;
use App\Http\Controllers\Api\Admin\AllergenController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\MenuCategoryController;
use App\Http\Controllers\Api\Admin\MenuItemController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\TableController as AdminTableController;
use App\Http\Controllers\Api\AiController;
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
Route::patch('/orders/{order}/items/{item}', [OrderController::class, 'updateItemNotes']);

// Rotte IA pubbliche: rate limit per sessione tavolo (non per IP), per
// evitare abusi/costi eccessivi senza penalizzare un tavolo condiviso.
Route::middleware('throttle:ai-per-session')->group(function () {
    Route::post('/ai/recommend', [AiController::class, 'recommend']);
    Route::post('/ai/ask', [AiController::class, 'ask']);
});

// Rotte area admin: login pubblico, il resto protetto da token Sanctum.
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('menu-items', MenuItemController::class)->except(['show']);
        Route::post('/menu-items/{menuItem}/image', [MenuItemController::class, 'uploadImage']);
        Route::delete('/menu-items/{menuItem}/image', [MenuItemController::class, 'destroyImage']);
        Route::apiResource('menu-categories', MenuCategoryController::class)->except(['show']);
        Route::apiResource('allergens', AllergenController::class)->except(['show']);
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('/ai-costs', [AiCostController::class, 'index']);
        Route::get('/tables', [AdminTableController::class, 'index']);
        Route::post('/tables/{table}/close-session', [AdminTableController::class, 'closeSession']);
    });
});
