<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->hasRole('admin');
        });

        Blade::if('manager', function () {
            return auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('manager'));
        });
    }
}
