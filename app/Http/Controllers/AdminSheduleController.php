<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Services\StoreHoursService;

class AdminSheduleController extends Controller
{
    public function index(Request $request, StoreHoursService $storeHours)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $date = $request->get('date', now()->toDateString());
        try {
            $selectedDate = Carbon::parse($date);
        } catch (\Throwable) {
            $selectedDate = now();
        }

        $month = $request->get('month', $selectedDate->format('Y-m'));
        try {
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            $monthStart = now()->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();
        $month = $monthStart->format('Y-m');

        $orders = Orders::query()
            ->where('is_scheduled', true)
            ->whereDate('scheduled_for', $selectedDate->toDateString())
            ->orderBy('scheduled_for')
            ->get();

        $calendarCounts = Orders::query()
            ->where('is_scheduled', true)
            ->whereBetween('scheduled_for', [$monthStart, $monthEnd])
            ->selectRaw('DATE(scheduled_for) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $grouped = $orders->groupBy('schedule_slot')
            ->map(function ($slotOrders, $slot) {
                return [
                    'slot' => $slot ?: 'No slot',
                    'orders' => $slotOrders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'customer_name' => $order->customer_name,
                            'status' => $order->status,
                            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                        ];
                    })->values(),
                ];
            })
            ->values();

        $perPage = 3;
        $currentPage = max(1, (int) $request->integer('page', 1));
        $paginatedGroups = new LengthAwarePaginator(
            $grouped->forPage($currentPage, $perPage)->values(),
            $grouped->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return Inertia::render('Admin/Schedule/Index', [
            'date' => $selectedDate->toDateString(),
            'month' => $month,
            'today' => now()->toDateString(),
            'calendarCounts' => $calendarCounts,
            'totalOrdersForDay' => $orders->count(),
            'groups' => $paginatedGroups,
            'storeHours' => $storeHours->get(),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function updateControls(Request $request, StoreHoursService $storeHours)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $rules = [
            'is_open' => 'nullable|boolean',
        ];

        foreach ($storeHours->dayKeys() as $day) {
            $rules["weekly.{$day}.enabled"] = 'nullable|boolean';
            $rules["weekly.{$day}.open"] = 'required_if:weekly.' . $day . '.enabled,1|date_format:H:i';
            $rules["weekly.{$day}.close"] = 'required_if:weekly.' . $day . '.enabled,1|date_format:H:i|after:weekly.' . $day . '.open';
        }

        $validated = $request->validate($rules);

        $payload = [
            'is_open' => (bool) ($validated['is_open'] ?? false),
            'weekly' => [],
        ];

        foreach ($storeHours->dayKeys() as $day) {
            $dayInput = $validated['weekly'][$day] ?? [];
            $payload['weekly'][$day] = [
                'enabled' => (bool) ($dayInput['enabled'] ?? false),
                'open' => $dayInput['open'] ?? '09:00',
                'close' => $dayInput['close'] ?? '17:00',
            ];
        }

        $storeHours->save($payload);

        return back()->with('success', 'Operating schedule updated.');
    }
}
