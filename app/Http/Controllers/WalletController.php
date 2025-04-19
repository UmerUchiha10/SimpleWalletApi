<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WalletController extends Controller
{
    public function deposit(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),  
            ], 422);  
        }

        $user = User::findOrFail($userId);
        $wallet = $user->wallet;
        DB::beginTransaction();
        try {
            $wallet->increment('balance', $request->amount);
            Transaction::create([
                'type' => 'deposit',
                'receiver_id' => $user->id,
                'sender_id' => null,
                'amount' => $request->amount,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Deposit successful',
                'balance' => $wallet->balance
            ],201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Deposit Failed'], 500);
        }
    }

    public function withdraw(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),  
            ], 422);  
        }

        $user = User::findOrFail($userId);
        $wallet = $user->wallet;

        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        DB::beginTransaction();
        try {
            $wallet->decrement('balance', $request->amount);
            Transaction::create([
                'type' => 'withdraw',
                'sender_id' => $user->id,
                'receiver_id' => null,
                'amount' => $request->amount,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Withdrawal successful',
                'balance' => $wallet->balance
            ],201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Withdrawal failed'], 500);
        }
    }
}
