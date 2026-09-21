<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — WOFINS iOS / mobile (Fase 1)
|--------------------------------------------------------------------------
|
| Prefix otomatis: /api
| Versioning: /api/v1/...
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('api.v1.auth.login');

    Route::middleware([
        'auth:sanctum',
        'abilities:mobile',
        'api.account.active',
        'api.app.license',
    ])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])
            ->name('api.v1.auth.logout');

        Route::get('/me', [MeController::class, 'show'])
            ->name('api.v1.me');
    });
});
