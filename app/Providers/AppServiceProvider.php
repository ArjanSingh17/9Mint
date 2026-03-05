<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Pricing\DbCurrencyCatalog;
use App\Services\Pricing\CoinbaseRateProvider;
use App\Services\Pricing\CurrencyCatalogInterface;
use App\Services\Pricing\RateProviderInterface;
use Illuminate\Validation\Rules\Password;

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

        // Set the default password requirements for the entire application
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            // In production, check if the password was in a data breach
            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
