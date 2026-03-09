<?php

use App\Http\Controllers\AdminSheduleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FoodsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PublicCartController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/landing', 'landing')->name('landing');

Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');


// ---------------- CART ----------------

Route::get('/cart', [PublicCartController::class, 'index'])
    ->name('cart.index');

Route::post('/cart/add', [PublicCartController::class, 'add'])
    ->name('cart.add');

Route::delete('/cart/remove/{id}', [PublicCartController::class, 'remove'])
    ->name('cart.remove');

Route::post('/cart/increase/{id}', [PublicCartController::class, 'increase'])
    ->name('cart.increase');

Route::post('/cart/decrease/{id}', [PublicCartController::class, 'decrease'])
    ->name('cart.decrease');


// ---------------- CHECKOUT ----------------

Route::post('/checkout', [PublicCartController::class, 'checkout'])
    ->name('checkout.store');


// ---------------- ORDERS ----------------

// IMPORTANT: Static routes FIRST

Route::get('/orders', [PublicCartController::class, 'myOrders'])
    ->name('orders.index');

Route::post('/orders/search', [PublicCartController::class, 'searchOrder'])
    ->name('orders.search');

Route::get('/orders/{id}', [PublicCartController::class, 'show'])
    ->name('orders.show');

Route::get('/orders/track/{order_number}', [PublicCartController::class, 'track'])
    ->name('orders.track');

Route::post('/orders/{order}/cancel', [PublicCartController::class, 'cancelOrder'])
    ->name('orders.cancel');

Route::get('/orders/{order}/receipt-modal', [PublicCartController::class, 'receiptModal'])
    ->name('orders.receipt.modal');

Route::get('/orders/{id}/receipt', [PublicCartController::class, 'receipt'])
    ->name('orders.receipt');

Route::get('/orders/{id}/receipt/download', [PublicCartController::class, 'downloadReceipt'])
    ->name('orders.receipt.download');

Route::get('/dashboard', [PublicCartController::class, 'dashboard'])
    ->name('public.dashboard');
Route::get('/navbar-notification', function(){

    $orderIds = session()->get('my_orders', []);
    $latestOrder = null;

    if(!empty($orderIds)){
        $latestOrder = \App\Models\Orders::whereIn('id', $orderIds)
                        ->latest()
                        ->first();
    }

    return view('orders.partials.navbar-notification', compact('latestOrder'));

})->name('navbar.notification');
Route::get('/check-order-status', function(){

    $orderIds = session()->get('my_orders', []);

    $orders = [];

    if(!empty($orderIds)){
        $orders = \App\Models\Orders::whereIn('id', $orderIds)
                    ->latest()
                    ->get(['id', 'order_number', 'status'])
                    ->map(function ($order) {
                        $labels = [
                            'pending' => 'Pending',
                            'preparing' => 'Preparing',
                            'out_of_delivery' => 'Out of Delivery',
                            'delivered' => 'Delivered',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ];

                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'status' => $order->status,
                            'status_label' => $labels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)),
                        ];
                    })
                    ->values();
    }

    return response()->json($orders);
})->name('check.order.status');

/*
|--------------------------------------------------------------------------
| Admin Routes (Auth Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/admin/dashboard', [CartController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    // Admin Foods
    Route::get('/admin/foods', [FoodsController::class, 'adminIndex'])
        ->name('admin.foods.index');

    Route::get('/admin/foods/create', [FoodsController::class, 'create'])
        ->name('admin.foods.create');

    Route::post('/admin/foods', [FoodsController::class, 'store'])
        ->name('admin.foods.store');

    Route::get('/admin/foods/{food}/edit', [FoodsController::class, 'edit'])
        ->name('admin.foods.edit');

    Route::patch('/admin/foods/{food}', [FoodsController::class, 'update'])
        ->name('admin.foods.update');

    Route::delete('/admin/foods/{food}', [FoodsController::class, 'destroy'])
        ->name('admin.foods.destroy');

    Route::patch('/admin/foods/{food}/toggle', [FoodsController::class, 'toggleAvailability'])
        ->name('admin.foods.toggle');

    // Admin Categories
    Route::get('/admin/categories', [CategoryController::class, 'index'])
        ->name('admin.categories.index');

    Route::post('/admin/categories', [CategoryController::class, 'store'])
        ->name('admin.categories.store');

    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])
        ->name('admin.categories.edit');

    Route::patch('/admin/categories/{category}', [CategoryController::class, 'update'])
        ->name('admin.categories.update');

    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');

    // Admin Orders
    Route::get('/admin/orders', [CartController::class, 'adminOrders'])
        ->name('admin.orders');

    Route::patch('/admin/orders/{order}', [CartController::class, 'adminUpdateOrder'])
        ->name('admin.orders.update');

    Route::get('/admin/orders/export/pdf', [CartController::class, 'exportOrdersPdf'])
        ->name('admin.orders.export.pdf');

    //schedule
    Route::get('/admin/schedule', [AdminSheduleController::class, 'index'])->name('admin.schedule');

    Route::get('/admin/notifications', function () {
        if (auth()->user()?->is_admin != 1) {
            abort(403);
        }

        $lastSeenAt = session('admin_notif_seen_at');
        if (empty($lastSeenAt)) {
            $lastSeenAt = now()->toDateTimeString();
            session(['admin_notif_seen_at' => $lastSeenAt]);
        }

        $baseQuery = \App\Models\Orders::query();

        $unreadCount = (clone $baseQuery)
            ->where('created_at', '>', $lastSeenAt)
            ->count();

        $notifications = (clone $baseQuery)
            ->latest()
            ->limit(12)
            ->get([
                'id',
                'order_number',
                'customer_name',
                'status',
                'is_scheduled',
                'scheduled_for',
                'schedule_slot',
                'created_at',
            ])
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status,
                    'is_scheduled' => (bool) $order->is_scheduled,
                    'schedule_slot' => $order->schedule_slot,
                    'scheduled_for' => optional($order->scheduled_for)->format('Y-m-d H:i:s'),
                    'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    })->name('admin.notifications');

    Route::post('/admin/notifications/mark-read', function () {
        if (auth()->user()?->is_admin != 1) {
            abort(403);
        }

        session(['admin_notif_seen_at' => now()->toDateTimeString()]);

        return response()->json(['ok' => true]);
    })->name('admin.notifications.mark-read');

});

require __DIR__.'/auth.php';
