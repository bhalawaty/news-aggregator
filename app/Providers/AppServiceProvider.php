<?php

namespace App\Providers;

use App\Factories\ProviderFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(ProviderFactory::class, function () {
            return new ProviderFactory(
                config('news.providers')
            );
        });
    }


    public function boot(): void
    {
        //
    }
}
