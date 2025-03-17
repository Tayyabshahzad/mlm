<?php

namespace App\Http\Controllers;

use App\Models\ActivationCode;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class ActivationCodeController extends Controller
{
    public function index(){
        $user = Auth::user();
        $setting = Setting::first();
        $totalBalance =  Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->sum('balance'); 
        $activationCodes = ActivationCode::with('generatedBy','usedBy')->orderby('id','desc')->where('generated_by',$user->id)->get();
        return view('product.activation-code',compact('user','setting','totalBalance','activationCodes'));
    }

    public function generateCode(Request $request)
    {
        $user = Auth::user(); 
        $setting = Setting::first();
        $userTotalBalance = Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->sum('balance'); 
        if ($userTotalBalance < $setting->activation_code) {
            return response()->json(['message' => 'Insufficient PV balance. You need at least '.$setting->activation_code.' PV to generate a code.'], 400);
        } 
        $wallets = Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->orderBy('id', 'asc')
        ->get(); 
        $amountToDeduct = $setting->activation_code;
        $originalAmount = $amountToDeduct;
        foreach ($wallets as $wallet) {
            if ($amountToDeduct <= 0) break;
            if ($wallet->balance >= $amountToDeduct) {
                $wallet->balance -= $amountToDeduct;
                $wallet->save();
                $amountToDeduct = 0;
            }else{
                $amountToDeduct -= $wallet->balance;
                $wallet->balance = 0;
                $wallet->save();
            }
        } 
        $code = strtoupper(Str::random(10));
        $activationCode = ActivationCode::create([
            'code' => $code,
            'generated_by' => $user->id,
            'amount' => $originalAmount,
            'status' => 'unused',
            'expires_at' => now()->addDays(30), // Set expiration (optional)
            'updated_at' => null ,
            'admin_approval' => 'pending'
        ]);
        $activationCode->load('generatedBy','usedBy');
        $newBalance = Wallet::where('wallet_type', 'online')
        ->where('user_id', $user->id)
        ->sum('balance');
        $this->logTransaction(
            $user->id,
            'online',
            'activation_code', 
            $originalAmount,
            $originalAmount,
            'Deducted ' . $originalAmount . ' PV for generating an activation code.',
            'debit'
        );
        
        return response()->json([
            'message' => 'Activation code generated successfully!',
            'data' => $activationCode,
            'new_balance' => $newBalance,  
        ]);

      
          
    }

    public function destroy($id)
    {
        $code = ActivationCode::find($id); 
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Code not found'], 404);
        } 
        if ($code->admin_approval === 'approved') {
            return response()->json(['success' => false, 'message' => 'This activation code cannot be deleted as it is already approved.'], 403);
        } 
        $userWallet = Wallet::where('wallet_type', 'online')
        ->where('user_id', $code->generated_by)
        ->orderBy('id', 'asc')
        ->get();
        $amountToRefund = $code->amount;
        $originalAmount = $code->amount;
        foreach ($userWallet as $wallet) {
            if ($amountToRefund <= 0) break; 
            $refundAmount = min($amountToRefund, $code->amount); 
            $wallet->balance += $refundAmount;
            $wallet->save();
            $amountToRefund -= $refundAmount;
        }
        $this->logTransaction(
            $code->generated_by,
            'activation_code',
            'online', 
            $originalAmount,
            $originalAmount,
           'Refunded ' . $code->amount . ' PV due to activation code deletion.',
           'credit'
        );
        $code->delete(); 
        return response()->json(['success' => true, 'message' => 'Code deleted successfully']);
    }

    private function logTransaction($userId,$toAddress,$fromAddress,$amount, $finalAmount,$description,$status)
    {
        TransactionLog::create([
            'user_id' => $userId,
            'from_wallet_type' => $toAddress,
            'to_wallet_type' => $fromAddress,
            'charge' =>0, 
            'amount' => $amount, 
            'final_amount' => $finalAmount,
            'description' => $description,
            'status' =>$status
        ]);
    } 
    
}
