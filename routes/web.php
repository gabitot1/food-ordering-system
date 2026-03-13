<?php

use App\Http\Controllers\AdminSheduleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FoodsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PublicCartController;
use App\Http\Controllers\ProfileController;
use Inertia\Inertia;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/landing', function () {
    return Inertia::render('Landing');
})->name('landing');
Route::get('/menu', [HomeController::class, 'index'])->name('menu');
Route::get('/foods', [FoodsController::class, 'index'])->name('foods.index');
// Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');


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

Route::post('/cart/quantity/{id}', [PublicCartController::class, 'updateQuantity'])
    ->name('cart.quantity.update');

Route::post('/cart/instruction/{id}', [PublicCartController::class, 'updateInstruction'])
    ->name('cart.instruction.update');


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

    return response()->json([
        'latest_order' => $latestOrder ? [
            'id' => $latestOrder->id,
            'order_number' => $latestOrder->order_number,
            'status' => $latestOrder->status,
        ] : null,
    ]);

})->name('navbar.notification');
Route::get('/check-order-status', function(){

    $orderIds = session()->get('my_orders', []);

    $orders = [];

    if(!empty($orderIds)){
        $orders = \App\Models\Orders::whereIn('id', $orderIds)
                    ->latest()
                    ->get(['id', 'order_number', 'status', 'approval_status'])
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
                            'approval_status' => $order->approval_status ?? 'pending',
                            'status_label' => match ($order->approval_status ?? 'pending') {
                                'pending' => 'Awaiting approval',
                                'disapproved' => 'Disapproved',
                                default => $labels[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)),
                            },
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    Route::patch('/admin/foods/{food}/approval', [FoodsController::class, 'updateApproval'])
        ->name('admin.foods.approval.update');

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
    Route::post('/admin/schedule/controls', [AdminSheduleController::class, 'updateControls'])->name('admin.schedule.controls.update');

    Route::get('/admin/notifications', function () {
        if (auth()->user()?->is_admin != 1) {
            abort(403);
        }

        $baseQuery = \App\Models\Orders::query();
        $lowStockQuery = \App\Models\Foods::query()
            ->whereNotNull('available_quantity')
            ->whereBetween('available_quantity', [1, 5])
            ->where('approval_status', 'approved');

        $orderNotifications = (clone $baseQuery)
            ->orderByDesc(\DB::raw('GREATEST(UNIX_TIMESTAMP(created_at), UNIX_TIMESTAMP(updated_at))'))
            ->get([
                'id',
                'order_number',
                'customer_name',
                'status',
                'is_scheduled',
                'scheduled_for',
                'schedule_slot',
                'created_at',
                'updated_at',
            ])
            ->map(function ($order) {
                $type = $order->status === 'cancelled'
                    ? 'cancelled'
                    : ($order->is_scheduled ? 'scheduled' : 'new');

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status,
                    'type' => $type,
                    'is_scheduled' => (bool) $order->is_scheduled,
                    'schedule_slot' => $order->schedule_slot,
                    'scheduled_for' => optional($order->scheduled_for)->format('Y-m-d H:i:s'),
                    'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => optional($order->updated_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        $lowStockNotifications = (clone $lowStockQuery)
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'name',
                'available_quantity',
                'updated_at',
            ])
            ->map(function ($food) {
                return [
                    'id' => 'food-' . $food->id,
                    'food_id' => $food->id,
                    'food_name' => $food->name,
                    'available_quantity' => (int) $food->available_quantity,
                    'type' => 'low_stock',
                    'created_at' => optional($food->updated_at)->format('Y-m-d H:i:s'),
                    'updated_at' => optional($food->updated_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        $notifications = $orderNotifications
            ->concat($lowStockNotifications)
            ->sortByDesc(function ($item) {
                return strtotime($item['updated_at'] ?? $item['created_at'] ?? now()->toDateTimeString());
            })
            ->take(5)
            ->values();

        $lastSeenAt = session('admin_notif_seen_at');

        $unreadCount = $notifications
            ->filter(function ($item) use ($lastSeenAt) {
                if (!$lastSeenAt) {
                    return true;
                }

                $itemTime = $item['updated_at'] ?? $item['created_at'] ?? null;

                return $itemTime && strtotime($itemTime) > strtotime($lastSeenAt);
            })
            ->count();

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
