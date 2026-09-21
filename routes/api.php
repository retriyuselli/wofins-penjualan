<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MobileModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — WOFINS iOS / mobile (wofins-penjualan)
|--------------------------------------------------------------------------
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

        Route::get('/me', [MeController::class, 'show'])->name('api.v1.me');
        Route::patch('/me', [MeController::class, 'update'])->name('api.v1.me.update');
        Route::post('/me/avatar', [MeController::class, 'updateAvatar'])->name('api.v1.me.avatar');
        Route::put('/me/password', [MeController::class, 'updatePassword'])
            ->middleware('throttle:5,1')
            ->name('api.v1.me.password');
        Route::get('/me/devices', [MeController::class, 'devices'])->name('api.v1.me.devices');
        Route::get('/me/compensation', [MeController::class, 'compensation'])
            ->middleware('pro.feature:payroll')
            ->name('api.v1.me.compensation');

        Route::prefix('modules')->group(function () {
            Route::get('/', [MobileModuleController::class, 'index'])
                ->name('api.v1.modules.index');
            Route::get('/{key}/form', [MobileModuleController::class, 'form'])
                ->name('api.v1.modules.form');
            Route::get('/{key}', [MobileModuleController::class, 'show'])
                ->name('api.v1.modules.show');
            Route::post('/{key}', [MobileModuleController::class, 'store'])
                ->name('api.v1.modules.store');
            Route::get('/{key}/{id}', [MobileModuleController::class, 'detail'])
                ->whereNumber('id')
                ->name('api.v1.modules.detail');
            Route::get('/{key}/{id}/draft-kontrak', [MobileModuleController::class, 'draftKontrak'])
                ->whereNumber('id')
                ->name('api.v1.modules.draft-kontrak');
            Route::get('/{key}/{id}/pdf', [MobileModuleController::class, 'pdf'])
                ->whereNumber('id')
                ->name('api.v1.modules.pdf');
            Route::patch('/{key}/{id}', [MobileModuleController::class, 'update'])
                ->whereNumber('id')
                ->name('api.v1.modules.update');
        });

        Route::prefix('finance')->group(function () {
            Route::middleware('pro.feature:basic_finance')->group(function () {
                Route::get('/dashboard', [FinanceController::class, 'dashboard'])
                    ->name('api.v1.finance.dashboard');
                Route::get('/transactions', [FinanceController::class, 'transactions'])
                    ->name('api.v1.finance.transactions');
                Route::get('/payments/{id}/proof', [FinanceController::class, 'paymentProof'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.payments.proof');
                Route::get('/reports/summary', [FinanceController::class, 'reportSummary'])
                    ->middleware('throttle:30,1')
                    ->name('api.v1.finance.reports.summary');
                Route::get('/reports/pdf', [FinanceController::class, 'reportPdf'])
                    ->middleware('throttle:10,1')
                    ->name('api.v1.finance.reports.pdf');
                Route::get('/reports/excel', [FinanceController::class, 'reportExcel'])
                    ->middleware('throttle:10,1')
                    ->name('api.v1.finance.reports.excel');
                Route::get('/piutangs', [FinanceController::class, 'piutangs'])
                    ->name('api.v1.finance.piutangs');
                Route::get('/piutangs/{id}', [FinanceController::class, 'piutangShow'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.piutangs.show');
            });

            Route::middleware('pro.feature:projects')->group(function () {
                Route::get('/prospects', [FinanceController::class, 'prospects'])
                    ->name('api.v1.finance.prospects');
                Route::post('/prospects', [FinanceController::class, 'prospectStore'])
                    ->name('api.v1.finance.prospects.store');
                Route::get('/prospects/{id}', [FinanceController::class, 'prospectShow'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.prospects.show');
                Route::patch('/prospects/{id}', [FinanceController::class, 'prospectUpdate'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.prospects.update');
                Route::get('/projects', [FinanceController::class, 'projects'])
                    ->name('api.v1.finance.projects');
                Route::get('/projects/overview', [FinanceController::class, 'projectsOverview'])
                    ->name('api.v1.finance.projects.overview');
                Route::get('/projects/widgets/{key}', [FinanceController::class, 'projectsOverviewWidget'])
                    ->where('key', '[a-z0-9_]+')
                    ->middleware('throttle:60,1')
                    ->name('api.v1.finance.projects.widgets');
                Route::get('/projects/closing', [FinanceController::class, 'projectsClosing'])
                    ->name('api.v1.finance.projects.closing');
                Route::get('/projects/options', [FinanceController::class, 'projectOptions'])
                    ->name('api.v1.finance.projects.options');
                Route::post('/projects', [FinanceController::class, 'projectStore'])
                    ->name('api.v1.finance.projects.store');
                Route::post('/projects/{id}', [FinanceController::class, 'projectUpdate'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.projects.update');
                Route::get('/projects/{id}', [FinanceController::class, 'projectShow'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.projects.show');
                Route::get('/projects/{id}/contract', [FinanceController::class, 'projectContract'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.projects.contract');
                Route::get('/projects/{id}/invoice', [FinanceController::class, 'projectInvoice'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.projects.invoice');
                Route::get('/products/{id}/pdf', [FinanceController::class, 'productPdf'])
                    ->whereNumber('id')
                    ->name('api.v1.finance.products.pdf');
                Route::get('/products/{id}', [FinanceController::class, 'productShow'])
                    ->name('api.v1.finance.products.show');
                Route::get('/vendors/{id}', [FinanceController::class, 'vendorShow'])
                    ->name('api.v1.finance.vendors.show');
            });
        });
    });
});
