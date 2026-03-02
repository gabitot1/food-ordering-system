<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Foods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class FoodsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foods = Foods::where('is_available',1)->get();
        return view('foods.index',compact('foods'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function adminOnly(){
           if (!Auth::check() || !Auth::user()->is_admin) abort(403);

    }

    public function adminIndex(Request $request){
        $query = Foods::query();

        if($request->filled('q')){
            $query->where('name','like','%' .$request->q. '%');
        }
        if($request->filled('availability')){
            if($request->availability === 'available'){
                $query->where('is_available', 1);
            }elseif($request->availabiliy === 'unavailable'){
                $query->where('is_available',0);
            }
        }

        $this->adminOnly();
        $foods = Foods::latest()->paginate(10)->withQueryString();
        return view('admin.foods.index', compact('foods'));
    }
    public function create()
    {
        $this->adminOnly();
        $categories = Category::orderBy('name')->get();
        return view('admin.foods.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->adminOnly();
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'is_available' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if($request->hasFile('image')){
            $imagePath = $request->file('image')->store('foods','public');
        }
        Foods::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'is_available' => $request->has('is_available'),
            'image' => $imagePath,
        ]);
        return redirect()->route('admin.foods.create')->with('success', 'Food added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Foods $food)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.foods.edit', compact('food','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foods $food)
    {
         // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'is_available' => 'sometimes|boolean',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'category_id'=> 'nullable|exists:categories,id',
    ]);

    // Prepare data
    $data = [
        'name' => $request->name,
        'price' => $request->price,
        'is_available' => $request->has('is_available'),
        'category_id' => $request->category_id,

    ];

    // If new image uploaded
    if ($request->hasFile('image')) {

        // Delete old image if exists
        if ($food->image && \Storage::disk('public')->exists($food->image)) {
            \Storage::disk('public')->delete($food->image);
        }

        // Store new image
        $data['image'] = $request->file('image')->store('foods', 'public');
    }

    // Update record
    $food->update($data);

    return redirect()->route('admin.foods.index')
        ->with('success', 'Food updated successfully!');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Foods $food)
    {
        $food->delete();
        return redirect()->route('admin.foods.index')->with('success','Food Deleted!');
    }
    public function toggleAvailability(Foods $food){
        if(!Auth::user() || !Auth::user()->is_admin){
            abort(403);
        }
        $food->update([
            'is_available' => !$food->is_available,
        ]);
        return back()->with('success','Updated');
    }

}
