<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class CustomerOrder extends Controller
{
    public function store(Request $request){
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'department'=> 'required',
            'contact_number' => 'required|numeric',
        ]);

        Orders::create([
            'customer_name' => $request->customer_name,
            'department' => $request->department,
            'contact_number'=>$request->contact_number,
            'email'=> $request->email,
            'total' => $request->total ?? 0,
        ]);
        return redirect('/')->with('success', 'Orders Placed!');
    }
}
