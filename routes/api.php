<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantRegistrationController;

/*
|--------------------------------------------------------------------------
| Central API Routes
|--------------------------------------------------------------------------
|
| These routes are for the central application (admin panel)
| They handle tenant management and admin authentication
|
*/

Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);

    // Admin protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Tenant management (admin only)
        Route::prefix('tenants')->group(function () {
            Route::get('/', [TenantRegistrationController::class, 'listTenants']);
            Route::post('/', [TenantRegistrationController::class, 'register']);
            Route::get('/{tenant}', [TenantRegistrationController::class, 'showTenant']);
            Route::put('/{tenant}', [TenantRegistrationController::class, 'updateTenant']);
            Route::delete('/{tenant}', [TenantRegistrationController::class, 'deleteTenant']);
            Route::post('/{tenant}/suspend', [TenantRegistrationController::class, 'suspendTenant']);
            Route::post('/{tenant}/activate', [TenantRegistrationController::class, 'activateTenant']);
        });
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ]);
});
