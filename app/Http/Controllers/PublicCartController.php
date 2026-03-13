<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Foods;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\User;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderReceiptMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Services\StoreHoursService;

class PublicCartController extends Controller
{
    private function sessionOrderIds(): array
    {
        return session()->get('my_orders', []);
    }

    private function ensureSessionOwnsOrderId(int $orderId): void
    {
        if (!in_array($orderId, $this->sessionOrderIds(), true)) {
            abort(403);
        }
    }

    // SHOW CART
    public function index(StoreHoursService $storeHours)
    {
        $cart = session()->get('cart', []);
        $deliveryFee = 50;

        $foodIds = collect($cart)
            ->map(fn ($item, $id) => $item['food_id'] ?? (int) $id)
            ->unique()
            ->values();

        $foodsById = Foods::query()
            ->whereIn('id', $foodIds)
            ->get()
            ->keyBy('id');

        $cartItems = collect($cart)->map(function ($item, $id) use ($foodsById) {
            $foodId = $item['food_id'] ?? (int) $id;
            $food = $foodsById->get($foodId);

            return [
                'id' => (string) $id,
                'food_id' => $foodId,
                'name' => $item['name'] ?? $food?->name ?? 'Item',
                'description' => $item['description'] ?? $food?->description,
                'image_url' => $item['image_url'] ?? ($food?->image ? asset('storage/' . $food->image) : null),
                'price' => (float) ($item['price'] ?? $food?->price ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 1),
                'available_quantity' => $food?->available_quantity,
                'instruction' => $item['instruction'] ?? null,
            ];
        })->values();

        $subtotal = $cartItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $now = now();
        $isOpenNow = $storeHours->isOpenAt($now);
        $nextOpening = $storeHours->nextOpeningAfter($now);
        $storeStatusMessage = $isOpenNow
            ? 'Store is open now.'
            : $storeHours->closedMessage($now);

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems,
            'deliveryFee' => $deliveryFee,
            'subtotal' => (float) $subtotal,
            'grandTotal' => (float) ($subtotal + $deliveryFee),
            'minScheduleDate' => ($nextOpening?->toDateString()) ?? now()->addDay()->toDateString(),
            'storeHours' => $storeHours->get(),
            'storeStatus' => [
                'is_open_now' => $isOpenNow,
                'message' => $storeStatusMessage,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    // REMOVE FROM CART
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);

        return back();
    }

    // CHECKOUT
    public function checkout(Request $request, StoreHoursService $storeHours)
    {
        $request->validate([
            'customer_name' => 'required',
            'address' => 'required',
            'department' => 'required',
            'id_number' => 'required',
            'email' => 'required|email',
            'contact_number' => 'required',
            'delivery_option' => 'required',
            'payment_method' => 'required',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'scheduled_for' => 'nullable|date|after_or_equal:today',
            'schedule_slot' => 'required_if:is_scheduled,1',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Cart is empty');
        }

        $foodIds = collect($cart)->pluck('food_id')->filter()->values();
        $foodsById = Foods::query()->whereIn('id', $foodIds)->get()->keyBy('id');

        $total = 0;

        foreach ($cart as $item) {
            $food = $foodsById->get($item['food_id']);

            if (!$food || !$food->is_available || ($food->approval_status ?? 'pending') !== 'approved') {
                return back()->with('error', 'One or more food items are no longer available.');
            }

            if (!is_null($food->available_quantity) && (int) $item['quantity'] > (int) $food->available_quantity) {
                return back()
                    ->with('error', $food->name . ' only has ' . $food->available_quantity . ' item(s) left in stock.');
            }

            $total += $item['price'] * $item['quantity'];
        }

        $customerEmail = $request->email;

        $isScheduled = $request->boolean('is_scheduled');
        $scheduledFor = null;
        $scheduleSlot = null;

        if ($isScheduled) {
            $scheduleDate = $request->input('scheduled_date');
            if (empty($scheduleDate) && $request->filled('scheduled_for')) {
                $scheduleDate = Carbon::parse($request->input('scheduled_for'))->toDateString();
            }

            if (empty($scheduleDate)) {
                return back()
                    ->withInput()
                    ->withErrors(['scheduled_date' => 'The schedule date field is required when scheduled is enabled.']);
            }

            $scheduleSlot = $request->schedule_slot;
            $availableSlots = collect($storeHours->scheduleSlotsForDate(Carbon::parse($scheduleDate)))
                ->pluck('value')
                ->all();

            if (!in_array($scheduleSlot, $availableSlots, true)) {
                return back()
                    ->withInput()
                    ->withErrors(['schedule_slot' => 'Selected schedule slot is not available for this date.']);
            }

            [$startTime] = explode('-', $scheduleSlot);
            $scheduledFor = Carbon::parse($scheduleDate . ' ' . $startTime);

            $slotCount = Orders::query()
                ->where('is_scheduled', true)
                ->whereDate('scheduled_for', $scheduleDate)
                ->where('schedule_slot', $scheduleSlot)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($slotCount >= 10) {
                return back()
                    ->withInput()
                    ->withErrors(['schedule_slot' => 'Selected schedule slot is already full.']);
            }

            if ($scheduledFor && !$storeHours->isOpenAt($scheduledFor)) {
                return back()
                    ->withInput()
                    ->withErrors(['scheduled_date' => $storeHours->closedMessage($scheduledFor)]);
            }
        } else {
            $now = now();
            if (!$storeHours->isOpenAt($now)) {
                return back()
                    ->withInput()
                    ->withErrors(['customer_name' => $storeHours->closedMessage($now)]);
            }
        }

        $orderData = [
            'order_number' => 'ORD-' . now()->format('YmdHis') . rand(10, 99),
            'customer_name' => $request->customer_name,
            'address' => $request->address,
            'department' => $request->department,
            'id_number' => $request->id_number,
            'contact_number' => $request->contact_number,
            'delivery_option' => $request->delivery_option,
            'payment_method' => $request->payment_method,
            'total' => $total,
            'status' => 'pending',
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'approval_note' => null,
            'instruction' => $request->instruction,
            'payment_status' => $request->payment_method === 'cash' ? 'unpaid' : 'paid',
            'is_scheduled' => $isScheduled,
            'scheduled_for' => $scheduledFor,
            'schedule_slot' => $scheduleSlot,
        ];

        if (Schema::hasColumn('orders', 'email')) {
            $orderData['email'] = $customerEmail;
        }

        $order = Orders::create($orderData);

        foreach ($cart as $id => $item) {
            OrderItems::create([
                'order_id' => $order->id,
                'food_id' => $item['food_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'instruction' => $item['instruction'] ?? null,
            ]);

            $food = $foodsById->get($item['food_id']);
            if ($food && !is_null($food->available_quantity)) {
                $remaining = max(0, (int) $food->available_quantity - (int) $item['quantity']);
                $food->update([
                    'available_quantity' => $remaining,
                    'is_available' => $remaining > 0 ? (bool) $food->is_available : false,
                ]);
            }
        }

        $orders = session()->get('my_orders', []);
        $orders[] = $order->id;
        session()->put('my_orders', $orders);

        $order->load('items.food');

        try {
            Mail::to($customerEmail)->send(new OrderReceiptMail($order));
        } catch (\Throwable $e) {
            Log::error('Customer checkout email sending failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $e->getMessage(),
            ]);
        }

        $adminRecipients = collect();

        if (config('mail.admin_address')) {
            $adminRecipients->push(config('mail.admin_address'));
        }

        $adminRecipients = $adminRecipients
            ->merge(
                User::query()
                    ->where('is_admin', 1)
                    ->whereNotNull('email')
                    ->pluck('email')
            )
            ->filter()
            ->unique()
            ->values();

        foreach ($adminRecipients as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new AdminNewOrderMail($order));
            } catch (\Throwable $e) {
                Log::error('Admin checkout email sending failed.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'admin_email' => $adminEmail,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        session()->forget('cart');

        if (!$request->header('X-Inertia') && $request->ajax()) {
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'track_url' => route('orders.track', $order->order_number),
            ]);
        }

        return redirect()
            ->route('orders.track', $order->order_number);
    }

    public function track($order_number)
    {
        $order = Orders::where('order_number', $order_number)
            ->with('items.food')
            ->first();

        if (!$order) {
            abort(404);
        }

        $this->ensureSessionOwnsOrderId($order->id);

        return Inertia::render('Orders/Track', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'total' => (float) $order->total,
                'status' => $order->status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                'approval_status' => $order->approval_status ?? 'pending',
                'approval_status_label' => match ($order->approval_status ?? 'pending') {
                    'approved' => 'Approved',
                    'disapproved' => 'Disapproved',
                    default => 'Awaiting approval',
                },
                'approval_note' => $order->approval_note,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'delivery_option' => $order->delivery_option,
                'created_at_label' => $order->created_at?->format('M d, Y h:i A'),
            ],
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function increase($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $foodId = $cart[$id]['food_id'] ?? (int) $id;
            $food = Foods::find($foodId);

            if (!$food || !$food->is_available || ($food->approval_status ?? 'pending') !== 'approved') {
                return back()->with('error', 'This food is no longer available.');
            }

            if (!is_null($food->available_quantity) && (int) $cart[$id]['quantity'] >= (int) $food->available_quantity) {
                return back()->with('error', $food->name . ' has reached the available stock limit.');
            }

            $cart[$id]['quantity']++;
        }

        session()->put('cart', $cart);

        return back();
    }

    public function decrease($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = max(1, ((int) $cart[$id]['quantity']) - 1);
        }

        session()->put('cart', $cart);

        return back();
    }

    public function updateInstruction(Request $request, $id)
    {
        $request->validate([
            'instruction' => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return back();
        }

        $cart[$id]['instruction'] = $request->input('instruction') ?: null;
        session()->put('cart', $cart);

        return back();
    }

    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return back()->with('error', 'Cart item not found.');
        }

        $foodId = $cart[$id]['food_id'] ?? (int) $id;
        $food = Foods::find($foodId);

        if (!$food || !$food->is_available || ($food->approval_status ?? 'pending') !== 'approved') {
            return back()->with('error', 'This food is no longer available.');
        }

        $quantity = (int) $request->input('quantity');

        if (!is_null($food->available_quantity) && $quantity > (int) $food->available_quantity) {
            return back()->with('error', $food->name . ' only has ' . $food->available_quantity . ' item(s) left in stock.');
        }

        $cart[$id]['quantity'] = $quantity;
        session()->put('cart', $cart);

        return back();
    }

    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required',
        ]);

        $orderIds = $this->sessionOrderIds();
        $order = Orders::whereIn('id', $orderIds)
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return back()->with('error', 'Order not found in your history.');
        }

        return redirect()->route('orders.track', $order->order_number);
    }

    public function myOrders(Request $request)
    {
        $orderIds = session()->get('my_orders', []);

        $query = Orders::whereIn('id', $orderIds)
            ->with('items.food')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(3)->withQueryString()
            ->through(function ($order) {
                $approvalStatus = $order->approval_status ?? 'pending';
                $normalizedStatus = match ($order->status) {
                    'out_of_delivery' => 'out_for_delivery',
                    'delivered', 'completed' => 'completed',
                    default => $order->status,
                };

                if ($normalizedStatus === 'cancelled') {
                    $displayStatus = 'cancelled';
                    $statusColor = 'bg-red-100 text-red-800';
                    $progressWidth = '100%';
                    $progressColor = 'bg-red-500';
                } elseif ($approvalStatus === 'pending') {
                    $displayStatus = 'awaiting_approval';
                    $statusColor = 'bg-amber-100 text-amber-800';
                    $progressWidth = '10%';
                    $progressColor = 'bg-amber-500';
                } elseif ($approvalStatus === 'disapproved') {
                    $displayStatus = 'disapproved';
                    $statusColor = 'bg-red-100 text-red-800';
                    $progressWidth = '100%';
                    $progressColor = 'bg-red-500';
                } else {
                    $displayStatus = $normalizedStatus;
                    $statusColor = match ($normalizedStatus) {
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'preparing' => 'bg-blue-100 text-blue-800',
                        'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-700',
                    };

                    $progressWidth = match ($normalizedStatus) {
                        'pending' => '25%',
                        'preparing' => '50%',
                        'out_for_delivery' => '75%',
                        'completed' => '100%',
                        'cancelled' => '100%',
                        default => '25%',
                    };

                    $progressColor = $normalizedStatus === 'cancelled'
                        ? 'bg-red-500'
                        : 'bg-green-500';
                }

                $paymentColor = $order->payment_status === 'paid'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800';

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'created_at_label' => $order->created_at?->format('M d, Y • h:i A'),
                    'items_count' => $order->items->count(),
                    'total' => (float) $order->total,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'approval_status' => $approvalStatus,
                    'approval_note' => $order->approval_note,
                    'normalized_status' => $normalizedStatus,
                    'display_status' => $displayStatus,
                    'status_label' => match ($displayStatus) {
                        'awaiting_approval' => 'Awaiting approval',
                        'disapproved' => 'Disapproved',
                        default => ucfirst(str_replace('_', ' ', $displayStatus)),
                    },
                    'status_color' => $statusColor,
                    'payment_color' => $paymentColor,
                    'progress_width' => $progressWidth,
                    'progress_color' => $progressColor,
                ];
            });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => (string) $request->input('search', ''),
            ],
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function show($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);
        $status = $order->status ?? 'pending';
        $paymentStatus = $order->payment_status ?? 'unpaid';
        $statusKey = $status === 'out_of_delivery' ? 'out_for_delivery' : $status;
        $approvalStatus = $order->approval_status ?? 'pending';

        $statusClass = match ($status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'preparing' => 'bg-blue-100 text-blue-800',
            'out_for_delivery', 'out_of_delivery' => 'bg-indigo-100 text-indigo-800',
            'delivered', 'completed' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-700',
        };

        $approvalClass = match ($approvalStatus) {
            'approved' => 'bg-green-100 text-green-800',
            'disapproved' => 'bg-red-100 text-red-800',
            default => 'bg-amber-100 text-amber-800',
        };

        $paymentClass = match ($paymentStatus) {
            'paid' => 'bg-green-100 text-green-800',
            'refunded' => 'bg-purple-100 text-purple-800',
            default => 'bg-red-100 text-red-700',
        };

        $steps = [
            ['key' => 'pending', 'label' => 'Pending', 'value' => 1],
            ['key' => 'preparing', 'label' => 'Preparing', 'value' => 2],
            ['key' => 'out_for_delivery', 'label' => 'Out for delivery', 'value' => 3],
            ['key' => 'delivered', 'label' => 'Delivered', 'value' => 4],
        ];

        $stepLookup = collect($steps)->mapWithKeys(fn ($step) => [$step['key'] => $step['value']])->all();
        $currentStep = $stepLookup[$statusKey] ?? 1;

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'department' => $order->department,
                'id_number' => $order->id_number,
                'contact_number' => $order->contact_number,
                'address' => $order->address,
                'delivery_option' => $order->delivery_option,
                'created_at_label' => $order->created_at?->format('M d, Y h:i A'),
                'status' => $status,
                'status_label' => ucfirst(str_replace('_', ' ', $status)),
                'status_class' => $statusClass,
                'approval_status' => $approvalStatus,
                'approval_status_label' => match ($approvalStatus) {
                    'approved' => 'Approved',
                    'disapproved' => 'Disapproved',
                    default => 'Awaiting approval',
                },
                'approval_class' => $approvalClass,
                'approval_note' => $order->approval_note,
                'payment_status' => $paymentStatus,
                'payment_status_label' => ucfirst($paymentStatus),
                'payment_class' => $paymentClass,
                'total' => (float) $order->total,
                'can_cancel' => $order->status === 'pending' && $approvalStatus !== 'disapproved',
                'current_step' => $currentStep,
                'progress_width' => ($currentStep - 1) * 33.33,
                'steps' => $steps,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->food->name ?? 'Food Deleted',
                        'price' => (float) $item->price,
                        'quantity' => (int) $item->quantity,
                        'subtotal' => (float) $item->subtotal,
                        'instruction' => $item->instruction,
                    ];
                })->values(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function cancelOrder(Orders $order)
    {
        $this->ensureSessionOwnsOrderId($order->id);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Order Cancelled');
    }

    public function receipt($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);
        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->stream('receipt-' . $order->order_number . '.pdf');
    }

    public function downloadReceipt($id)
    {
        $this->ensureSessionOwnsOrderId((int) $id);

        $order = Orders::with('items.food')->findOrFail($id);

        $pdf = Pdf::loadView('orders.recept-pdf', compact('order'));

        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    public function receiptModal(Orders $order)
    {
        $this->ensureSessionOwnsOrderId($order->id);

        $order->load('items.food');

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'address' => $order->address,
                'department' => $order->department,
                'id_number' => $order->id_number,
                'contact_number' => $order->contact_number,
                'delivery_option' => $order->delivery_option,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'approval_status' => $order->approval_status,
                'total' => (float) $order->total,
                'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'food_name' => $item->food->name ?? 'Food Deleted',
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'instruction' => $item->instruction,
                    ];
                })->values(),
            ],
        ]);
    }

    public function add(Request $request)
    {
        $food = Foods::findOrFail($request->food_id);

        if (!$food->is_available || ($food->approval_status ?? 'pending') !== 'approved') {
            return back()->with('error', 'This food is currently unavailable.');
        }

        $cart = session()->get('cart', []);

        $quantity = max(1, (int) ($request->quantity ?? 1));
        $instruction = $request->instruction ?? null;
        $currentQty = isset($cart[$food->id]) ? (int) $cart[$food->id]['quantity'] : 0;
        $nextQty = $currentQty + $quantity;

        if (!is_null($food->available_quantity) && $nextQty > (int) $food->available_quantity) {
            $message = $food->name . ' only has ' . $food->available_quantity . ' item(s) left in stock.';

            if (!$request->header('X-Inertia') && $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $cart[$food->id] = [
            'food_id' => $food->id,
            'name' => $food->name,
            'description' => $food->description,
            'image_url' => $food->image ? asset('storage/' . $food->image) : null,
            'price' => $food->price,
            'quantity' => $nextQty,
            'instruction' => $instruction,
        ];

        session()->put('cart', $cart);

        if (!$request->header('X-Inertia') && $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Added to cart');
    }

    // public function dashboard()
    // {
    //     $orderIds = session()->get('my_orders', []);

    //     if (empty($orderIds)) {
    //         $orders = collect();
    //     } else {
    //         $orders = Orders::whereIn('id', $orderIds)
    //             ->latest()
    //             ->get();
    //     }

    //     $totalOrders = $orders->count();
    //     $pendingOrders = $orders->where('status', 'pending')->count();
    //     $completedOrders = $orders->where('status', 'completed')->count();

    //     return view('dashboard.public', compact(
    //         'orders',
    //         'totalOrders',
    //         'pendingOrders',
    //         'completedOrders'
    //     ));
    // }
   

public function dashboard(Request $request)
{
    $orderIds = session()->get('my_orders', []);

    if (empty($orderIds)) {
        $orders = collect();
    } else {
        $orders = Orders::whereIn('id', $orderIds)
            ->latest()
            ->get();
    }

    $totalOrders = $orders->count();
    $pendingOrders = $orders->where('status', 'pending')->count();
    $completedOrders = $orders->whereIn('status', ['completed', 'delivered'])->count();

    $recentOrders = $orders->take(4)->map(function ($order) {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'created_at_label' => optional($order->created_at)->format('M d'),
            'status' => $order->status,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
        ];
    })->values();

    $bestSellers = Foods::query()
        ->where('is_available', 1)
        ->inRandomOrder()
        ->take(4)
        ->get()
        ->map(function ($food) {
            return [
                'id' => $food->id,
                'name' => $food->name,
                'price' => (float) $food->price,
                'image_url' => $food->image ? asset('storage/' . $food->image) : null,
            ];
        })
        ->values();

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_orders' => (int) $totalOrders,
            'pending_orders' => (int) $pendingOrders,
            'completed_orders' => (int) $completedOrders,
        ],
        'recent_orders' => $recentOrders,
        'best_sellers' => $bestSellers,
    ]);
}
}
