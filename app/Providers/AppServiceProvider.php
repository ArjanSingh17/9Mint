<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Pricing\DbCurrencyCatalog;
use App\Services\Pricing\CoinbaseRateProvider;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\RateProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RateProviderInterface::class, CoinbaseRateProvider::class);
        $this->app->bind(CurrencyCatalogInterface::class, DbCurrencyCatalog::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('currencySymbols', config('pricing.currency_symbols', []));
    }
}
