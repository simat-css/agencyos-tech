<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;

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
->middleware(['auth','verified'])
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

                Route::post('/bulk-action', [CompanyController::class, 'bulkAction'])
            ->middleware('permission:companies.edit')
            ->name('bulk-action');

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

        Route::post('/bulk-action', [DepartmentController::class, 'bulkAction'])
            ->middleware('permission:departments.edit')
            ->name('bulk-action');
    });

    }); // Auth middleware group close here

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
// Test
// Route::view('/company-dashboard-demo', 'dashboard.company-admin')
//     ->name('company-dashboard-demo');
//     Route::view('/manager-dashboard-demo', 'dashboard.manager')
//     ->name('manager-dashboard-demo');
//         Route::view('/hr-dashboard-demo', 'dashboard.hr')
//     ->name('hr-dashboard-demo');
//       Route::view('/user-dashboard-demo', 'dashboard.user')
//     ->name('user-dashboard-demo');
//global search
Route::get('/global-search',
    [SearchController::class,'search']
)->name('global.search');

    
// Roles

Route::middleware(['auth'])
    ->prefix('roles')
    ->name('roles.')
    ->group(function () {

        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission:roles.view')
            ->name('index');

        Route::get('/create', [RoleController::class, 'create'])
            ->middleware('permission:roles.create')
            ->name('create');

        Route::post('/', [RoleController::class, 'store'])
            ->middleware('permission:roles.create')
            ->name('store');

        Route::get('/{role}', [RoleController::class, 'show'])
            ->middleware('permission:roles.view')
            ->name('show');

        Route::get('/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:roles.edit')
            ->name('edit');

        Route::put('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.edit')
            ->name('update');

        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete')
            ->name('destroy');

    });

    //User Module 
    Route::prefix('users')
->name('users.')
->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('index');

        Route::get('/create', [UserController::class, 'create'])
            ->middleware('permission:users.create')
            ->name('create');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('store');

    // EXPORT HERE
    Route::get('/export', [UserController::class, 'export'])
        ->middleware('permission:users.export')
        ->name('export');

  Route::post('/import', [UserController::class, 'import'])
    ->middleware('permission:users.import')
    ->name('import');   

 Route::post('/bulk-action',[UserController::class,'bulkAction'])
->middleware('permission:users.edit')
->name('bulk-action');

            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])
           ->middleware('permission:users.edit')
            ->name('toggle-status');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission:users.view')
            ->name('show');

      Route::get('/{user}/edit', [UserController::class, 'edit'])
            ->middleware('permission:users.edit')
            ->name('edit');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('update');

       Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('destroy');

                        //for loading department in create user
            Route::get('/companies/{company}/departments',
          [UserController::class, 'getDepartments']
          )->name('companies.departments');
});

require __DIR__ . '/auth.php';