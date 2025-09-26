<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Tenant\HouseController;
use App\Http\Controllers\Api\Tenant\FloorController;
use App\Http\Controllers\Api\Tenant\FlatController;
use App\Http\Controllers\Api\Tenant\TenantUserController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are for tenant-specific operations
| They are accessed through tenant subdomains
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/v1')->group(function () {

    // Tenant user authentication
    Route::post('/login', [AuthController::class, 'tenantUserLogin']);

    // Protected tenant routes
    Route::middleware(['auth:sanctum', 'tenant.valid'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // House management
        Route::apiResource('houses', HouseController::class);
        Route::get('/houses/{house}/statistics', [HouseController::class, 'statistics']);

        // Floor management
        Route::apiResource('houses.floors', FloorController::class);

        // Flat management
        Route::apiResource('flats', FlatController::class);
        Route::patch('/flats/{flat}/status', [FlatController::class, 'updateStatus']);
        Route::get('/flats/available', [FlatController::class, 'availableFlats']);

        // Tenant user management
        Route::apiResource('tenant-users', TenantUserController::class);
        Route::post('/tenant-users/{tenantUser}/assign-flat', [TenantUserController::class, 'assignFlat']);
        Route::post('/tenant-users/{tenantUser}/remove-flat', [TenantUserController::class, 'removeFromFlat']);
    });

    // Tenant health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'tenant' => tenant('id'),
            'domain' => request()->getHost(),
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});