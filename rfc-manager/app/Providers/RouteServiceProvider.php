<?php

namespace App\Providers;

use Illuminate\Foundation\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('console')
                ->group(base_path('routes/console.php'));

            Route::middleware('console')
                ->group(base_path('routes/commands.php'));
        });
    }
}
