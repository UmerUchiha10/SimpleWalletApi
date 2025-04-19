<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//Authenticated Routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:api')->group(function () {

    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // User Routes
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);

    // Wallet Routes
    Route::post('/wallets/{userId}/deposit', [WalletController::class, 'deposit']);
    Route::post('/wallets/{userId}/withdraw', [WalletController::class, 'withdraw']);

    // Transaction Routes
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
    Route::get('/transactions/user/{userId}', [TransactionController::class, 'userTransactions']);
});