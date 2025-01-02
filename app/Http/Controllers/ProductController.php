<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        // Retrieve the selected category from the session, default to 'daily'
        $selectedCategory = session('category', 'daily');

        // Fetch the category from the database
        $category = Category::where('name', $selectedCategory)->firstOrFail();

        // Fetch products that belong to the selected category
        $products = Product::where('category_id', $category->id)->get();

        // Pass the products and selected category to the view
        return view('products.index', compact('products'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
        ]);

        // Retrieve the selected category from the session
        $categoryName = session('category', 'daily');
        $category = Category::where('name', $categoryName)->firstOrFail();

        // Create a new Product
        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->category_id = $category->id; // Assign the category from the session
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
        ]);

        // Find the product
        $product = Product::findOrFail($id);

        // Update product details
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            // Delete the product
            $product->delete();


            return response()->json(['status' => 'success', 'message' => 'Deleted Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }


    public function rentalDetails($id)
    {
        // Fetch the product
        $product = Product::with(['rentals.invoice.customer'])->findOrFail($id);

        // Get the rentals (invoice items) for this product with related invoice and customer
        $rentals = $product->rentals()->with('invoice.customer')->get();

        return view('products.rental-details', compact('product', 'rentals'));
    }
}
