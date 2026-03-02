<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Foods;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
   public function index(Request $request)
{
    $categories = Category::all();

    $foods = Foods::where('is_available', 1)
        ->when($request->category, function($query) use ($request) {
            $query->where('category_id', $request->category);
        })
        ->get();

    return view('dashboard', compact('foods', 'categories'));
}

}
