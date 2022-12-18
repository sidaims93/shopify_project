<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Laravel\Cashier\Cashier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {

        Cashier::ignoreMigrations();

        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

    }
}
