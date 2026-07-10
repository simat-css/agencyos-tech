<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Department;
use App\Policies\DepartmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $view->with(
                'hasCompany',
                Company::exists()
            );

        });

        Gate::policy(
            Department::class,
            DepartmentPolicy::class
        );
    }
}