<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class TransactionController extends Controller
{
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id|different:sender_id',
            'amount' => 'required|numeric|min:0.01',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),  
            ], 422);  
        }

        $sender = User::findOrFail($request->sender_id);
        $receiver = User::findOrFail($request->receiver_id);

        if ($sender->wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        DB::beginTransaction();
        try {
            $sender->wallet->decrement('balance', $request->amount);
            $receiver->wallet->increment('balance', $request->amount);

            Transaction::create([
                'type' => 'transfer',
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $request->amount,
            ]);

            DB::commit();
            return response()->json(['message' => 'Transfer successful'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Transfer failed'], 500);
        }
    }

    public function userTransactions($userId)
    {
        $user = User::findOrFail($userId);
        $transactions = Transaction::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return TransactionResource::collection($transactions);
    }


}
