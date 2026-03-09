<?php

namespace App\Providers;

use App\Models\Orders;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.navigation', function ($view) {
            $orderIds = session()->get('my_orders', []);

            if (empty($orderIds)) {
                $view->with('navOrders', collect());

                return;
            }

            $navOrders = Orders::query()
                ->whereIn('id', $orderIds)
                ->latest()
                ->get(['id', 'order_number', 'status']);

            $view->with('navOrders', $navOrders);
        });
    }
}
