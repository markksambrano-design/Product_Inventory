<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\InventoryNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
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
        Paginator::useBootstrapFive();

        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('two-factor', function ($request) {
            return Limit::perMinute(5)->by((string) $request->session()->get('two_factor_user_id', 'unknown').'|'.$request->ip());
        });

        View::composer('layouts.app', function ($view) {
            $low = Product::where('status', true)->whereColumn('quantity', '<=', 'minimum_stock')->count();
            $expiry = ProductBatch::where('quantity', '>', 0)->whereNotNull('expiration_date')
                ->whereDate('expiration_date', '<=', today()->addDays(30))->count();
            $unread = auth()->check() ? InventoryNotification::where('user_id', auth()->id())->whereNull('read_at')->count() : 0;
            $view->with('inventoryAlertCount', max($low + $expiry, $unread));
        });
    }
}
