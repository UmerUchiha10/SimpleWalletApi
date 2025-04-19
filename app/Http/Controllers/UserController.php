<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',  
            'balance' => 'required|numeric|min:0',
        ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors(),  
                ], 422);  
            }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password'=>$request->password,
        
        
        ]);


          

            $user->wallet()->create([
                'balance' => $request->balance,
            ]);
            

            DB::commit();
            return response()->json([
                'message' => 'User created successfully.',
                'data' => new UserResource($user->load('wallet')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'User not Created'], 500);
        }
    }

    public function show($id)
    {
        $user = User::with('wallet')->find($id);
        return $user ? new UserResource($user) : response()->json(['error' => 'User not found'], 404);
    }
}
