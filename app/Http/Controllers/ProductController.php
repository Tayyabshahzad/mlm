<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::get();;
        return view('product.index', compact('products'));
    }

    public function create()
    {
        $products = Product::get();;
        return view('product.create', compact('products'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        

         // Create the product
         $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description, 
        ]);

        if ($request->hasFile('photo')) {  
            $product->addMedia($request->file('photo'))
            ->toMediaCollection('product_cover');
        }    


        return redirect()->back()->with('success', 'Product created successfully!');
    }

    public function update($id)
    {
        $products = Product::find($id);;
        return view('product.update', compact('products'));
    }


    public function delete(Request $request)
    {
        $products = Product::get();;
        return view('product.create', compact('products'));
    }
    public function updateProcess(Request $request, $id)
    {
        // Validate the form inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Find the product by ID
        $product = Product::findOrFail($id);

        // Handle file upload
        if ($request->hasFile('photo')) {
            // Delete the old photo if it exists
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }

            // Store the new photo
            $photoPath = $request->file('photo')->store('product_photos', 'public');
            $product->image = $photoPath;
        }

        // Update the product
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        // Redirect with a success message
        return redirect()->back()->with('success', 'Product updated successfully!');
    }
}
