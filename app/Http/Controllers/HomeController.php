<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Foods;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');

        $preferredOrder = [
            'meals' => 0,
            'drinks' => 1,
            'desserts' => 2,
            'appetizer' => 3,
        ];

        $categories = Category::query()
            ->get(['id', 'name'])
            ->sortBy(function ($category) use ($preferredOrder) {
                $name = strtolower(trim($category->name));

                return [
                    $preferredOrder[$name] ?? 999,
                    $name,
                ];
            })
            ->values();

        $foods = Foods::query()
            ->where('approval_status', 'approved')
            ->where(function ($query) {
                $query->where('is_available', 1)
                    ->orWhere('available_quantity', 0);
            })
            ->when($selectedCategory, function ($query) use ($selectedCategory) {
                $query->where('category_id', $selectedCategory);
            })
            ->latest()
            ->get()
            ->map(function ($food) {
                $isOrderable = (bool) $food->is_available && ($food->available_quantity === null || (int) $food->available_quantity > 0);

                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'description' => $food->description,
                    'price' => (float) $food->price,
                    'image_url' => $food->image ? asset('storage/' . $food->image) : null,
                    'available_quantity' => $food->available_quantity,
                    'is_available' => (bool) $food->is_available,
                    'is_orderable' => $isOrderable,
                ];
            })
            ->values();

        return Inertia::render('Home/Index', [
            'categories' => $categories,
            'foods' => $foods,
            'selectedCategory' => $selectedCategory ? (int) $selectedCategory : null,
        ]);
    }

}
