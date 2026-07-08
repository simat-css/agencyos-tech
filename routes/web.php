<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard',
[DashboardController::class,'index'])
->middleware('auth')
->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::controller(ProfileController::class)
        ->prefix('profile')
        ->name('profile.')
        ->group(function () {

            Route::get('/', 'edit')
                ->name('edit');

            Route::patch('/', 'update')
                ->name('update');

            Route::delete('/', 'destroy')
                ->name('destroy');

        });

    /*
    |--------------------------------------------------------------------------
    | Company Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('companies')
        ->name('companies.')
        ->group(function () {

            Route::get('/', [CompanyController::class, 'index'])
                ->middleware('permission:companies.view')
                ->name('index');

            Route::get('/create', [CompanyController::class, 'create'])
                ->middleware('permission:companies.create')
                ->name('create');

            Route::post('/', [CompanyController::class, 'store'])
                ->middleware('permission:companies.create')
                ->name('store');

            Route::get('/{company}', [CompanyController::class, 'show'])
                ->middleware('permission:companies.view')
                ->name('show');

            Route::get('/{company}/edit', [CompanyController::class, 'edit'])
                ->middleware('permission:companies.edit')
                ->name('edit');

            Route::put('/{company}', [CompanyController::class, 'update'])
                ->middleware('permission:companies.edit')
                ->name('update');

            Route::delete('/{company}', [CompanyController::class, 'destroy'])
                ->middleware('permission:companies.delete')
                ->name('destroy');

            Route::patch('/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])
                ->middleware('permission:companies.edit')
                ->name('toggle-status');

        });

    /*
    |--------------------------------------------------------------------------
    | Department Management
    |--------------------------------------------------------------------------
    */

    Route::prefix('departments')
        ->name('departments.')
        ->group(function () {

            Route::get('/', [DepartmentController::class, 'index'])
                ->middleware('permission:departments.view')
                ->name('index');

            Route::get('/create', [DepartmentController::class, 'create'])
                ->middleware('permission:departments.create')
                ->name('create');

            Route::post('/', [DepartmentController::class, 'store'])
                ->middleware('permission:departments.create')
                ->name('store');

            Route::get('/{department}', [DepartmentController::class, 'show'])
                ->middleware('permission:departments.view')
                ->name('show');

            Route::get('/{department}/edit', [DepartmentController::class, 'edit'])
                ->middleware('permission:departments.edit')
                ->name('edit');

            Route::put('/{department}', [DepartmentController::class, 'update'])
                ->middleware('permission:departments.edit')
                ->name('update');

            Route::delete('/{department}', [DepartmentController::class, 'destroy'])
                ->middleware('permission:departments.delete')
                ->name('destroy');

            Route::patch('/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
                ->middleware('permission:departments.edit')
                ->name('toggle-status');

        });

});

Route::post(
    'departments/bulk-action',
    [DepartmentController::class, 'bulkAction']
)->name('departments.bulk-action');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';