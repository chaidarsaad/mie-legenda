<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Observers\CategoryObserver;
use App\Observers\OrderItemObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentPWA\Http\Controllers\PWAController;

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
        Route::middleware(['web', 'check.role'])
            ->group(function () {
                Route::get('/pwa', [PWAController::class, 'index']);
            });
        OrderItem::observe(OrderItemObserver::class);
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Category::observe(CategoryObserver::class);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->visible(outsidePanels: true)
                ->displayLocale('id')
                ->locales(['id', 'en',]);
        });
    }
}
