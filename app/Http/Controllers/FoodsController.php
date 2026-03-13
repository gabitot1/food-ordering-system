<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Foods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FoodsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $foods = Foods::where('is_available',1)->get();
    //     return view('foods.index',compact('foods'));
    // }
        public function index()
    {
        $foods = Foods::where('is_available', 1)->get();

        return Inertia::render('Foods/Index', [
            'foods' => $foods
        ]);
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
            }elseif($request->availability === 'unavailable'){
                $query->where('is_available',0);
            }
        }

        $this->adminOnly();
        $foods = $query->with(['category', 'approver'])->latest()->paginate(10)->withQueryString()
            ->through(function ($food) {
                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'category_name' => $food->category->name ?? 'Uncategorized',
                    'price' => (float) $food->price,
                    'available_quantity' => $food->available_quantity,
                    'is_available' => (bool) $food->is_available,
                    'approval_status' => $food->approval_status ?? 'pending',
                    'approved_by_name' => $food->approver?->name,
                    'approved_at_label' => $food->approved_at?->format('M d, Y h:i A'),
                    'rejection_reason' => $food->rejection_reason,
                    'image_url' => $food->image ? asset('storage/' . $food->image) : null,
                    'payment_method' => $food->payment_method ?? null,
                ];
            });

        return Inertia::render('Admin/Foods/Index', [
            'foods' => $foods,
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'availability' => (string) $request->query('availability', ''),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }
    public function create()
    {
        $this->adminOnly();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Foods/Create', [
            'categories' => $categories,
        ]);
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
            'available_quantity' => 'nullable|integer|min:0',
            'is_available' => 'sometimes|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if($request->hasFile('image')){
            $imagePath = $request->file('image')->store('foods','public');
        }
        $availableQuantity = $request->filled('available_quantity') ? (int) $request->available_quantity : null;
        Foods::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'available_quantity' => $availableQuantity,
            'is_available' => $availableQuantity === 0 ? false : $request->has('is_available'),
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'image' => $imagePath,
        ]);
        return redirect()->route('admin.foods.create')->with('success', 'Food added and pending approval.');
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
        $this->adminOnly();
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Admin/Foods/Edit', [
            'food' => [
                'id' => $food->id,
                'name' => $food->name,
                'price' => (float) $food->price,
                'available_quantity' => $food->available_quantity,
                'category_id' => $food->category_id,
                'is_available' => (bool) $food->is_available,
                'approval_status' => $food->approval_status ?? 'pending',
                'rejection_reason' => $food->rejection_reason,
                'image_url' => $food->image ? asset('storage/' . $food->image) : null,
            ],
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })->values(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foods $food)
    {
         $this->adminOnly();
         // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'available_quantity' => 'nullable|integer|min:0',
        'is_available' => 'sometimes|boolean',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'category_id'=> 'nullable|exists:categories,id',
    ]);

    // Prepare data
    $availableQuantity = $request->filled('available_quantity') ? (int) $request->available_quantity : null;

    $data = [
        'name' => $request->name,
        'price' => $request->price,
        'available_quantity' => $availableQuantity,
        'is_available' => $availableQuantity === 0 ? false : $request->has('is_available'),
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
        $this->adminOnly();
        $food->delete();
        return redirect()->route('admin.foods.index')->with('success','Food Deleted!');
    }
    public function toggleAvailability(Foods $food){
        if(!Auth::user() || !Auth::user()->is_admin){
            abort(403);
        }
        if (($food->approval_status ?? 'pending') !== 'approved') {
            return back()->with('error', 'Only approved foods can be toggled available.');
        }
        if ((int) ($food->available_quantity ?? 1) === 0 && !$food->is_available) {
            return back()->with('error', 'Cannot mark food as available while stock is 0.');
        }
        $food->update([
            'is_available' => !$food->is_available,
        ]);
        return back()->with('success','Updated');
    }

    public function updateApproval(Request $request, Foods $food)
    {
        $this->adminOnly();

        $validated = $request->validate([
            'approval_status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $status = $validated['approval_status'];
        $reason = $status === 'rejected'
            ? (string) ($validated['rejection_reason'] ?? '')
            : null;

        if ($status === 'rejected' && trim($reason) === '') {
            return back()->with('error', 'Rejection reason is required.');
        }

        $food->update([
            'approval_status' => $status,
            'approved_by' => $status === 'approved' ? Auth::id() : null,
            'approved_at' => $status === 'approved' ? now() : null,
            'rejection_reason' => $status === 'rejected' ? trim($reason) : null,
            'is_available' => $status === 'approved' ? (bool) $food->is_available : false,
        ]);

        return back()->with('success', 'Food approval updated.');
    }

}
