<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\UserRank;
use App\Models\Wallet;
use App\Services\BinarySystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function purchase(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $product = Product::findOrFail($request->product_id);
            $totalAmount = $product->price * $request->quantity;

            // Check if user has sufficient balance
            $availableBalance = $user->wallets()
                ->whereIn('wallet_type', ['commission', 'direct_commission', 'roi'])
                ->sum('balance');

            if ($availableBalance < $totalAmount) {
                throw new \Exception('Insufficient balance for this purchase.');
            }

            // Deduct amount from user's wallet
            $this->deductFromWallet($user, $totalAmount);

            // Record the product purchase
            Wallet::create([
                'user_id' => $user->id,
                'wallet_type' => 'product_purchase',
                'commission_type' => 'purchase',
                'balance' => -$totalAmount,
                'total_amount' => $totalAmount,
                'source' => 'product_purchase',
                'description' => "Purchased {$request->quantity}x {$product->name}"
            ]);

            // Update agreement balance automatically
            $user->updateAgreementBalance();

            // Process binary system integration
            $binaryService = new BinarySystemService();
            $binaryService->processCompletedProductPurchase($user->id, $totalAmount);

            // Update user rank after purchase
            UserRank::updateUserRank($user->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product purchased successfully! Your 2x/7x systems have been updated automatically.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Product purchase failed for user {$user->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function deductFromWallet(User $user, $amount)
    {
        $remainingAmount = $amount;

        // Deduct from wallets in order of preference
        $walletTypes = ['commission', 'direct_commission', 'roi'];

        foreach ($walletTypes as $walletType) {
            if ($remainingAmount <= 0) break;

            $wallets = $user->wallets()
                ->where('wallet_type', $walletType)
                ->where('balance', '>', 0)
                ->get();

            foreach ($wallets as $wallet) {
                if ($remainingAmount <= 0) break;

                $deduction = min($wallet->balance, $remainingAmount);
                $wallet->decrement('balance', $deduction);
                $remainingAmount -= $deduction;
            }
        }

        if ($remainingAmount > 0) {
            throw new \Exception('Unable to complete payment deduction');
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);

        try {
            $product = Product::findOrFail($request->id);
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 400);
        }
    }
}
