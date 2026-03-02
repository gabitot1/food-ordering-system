<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
            public function index()
        {
            $categories = Category::withCount('foods')
                            ->orderBy('name')
                            ->get();

            return view('admin.categories.index', compact('categories'));
        }

        public function edit(Category $category)
        {
            return view('admin.categories.edit', compact('category'));
        }

        public function update(Request $request, Category $category)
        {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $category->update([
                'name' => $request->name,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category Updated');
        }

        public function destroy(Category $category)
        {
            $category->delete();
            return back()->with('success', 'Deleted');
        }
 public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category added successfully!');
    }

}