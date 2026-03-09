<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class AdminSheduleController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $orders = Orders::query()
            ->where('is_scheduled', true)
            ->whereDate('scheduled_for', $date)
            ->orderBy('scheduled_for')
            ->get();

        $grouped = $orders->groupBy('schedule_slot');

        return view('admin.schedule.index', compact('date', 'grouped'));
    }
}
