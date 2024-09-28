<?php

namespace App\Providers;

use App\Contracts\CoinServiceInterface;
use App\Repositories\CoinPriceRepository;
use App\Services\CoinGeckoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CoinPriceRepository::class, function ($app) {
            return new CoinPriceRepository();
        });
        $this->app->bind(CoinServiceInterface::class, CoinGeckoService::class);
    }
}
