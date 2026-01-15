<?php

declare(strict_types=1);

namespace Blamodex\Lists;

use Illuminate\Support\ServiceProvider;

class ListsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/lists.php',
            'lists'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/lists.php' => config_path('lists.php'),
        ], 'blamodex-lists-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
