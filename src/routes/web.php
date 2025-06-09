<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/mypage', [ProfileController::class, 'index']);
    Route::get('/mypage/profile', [ProfileController::class, 'edit']);
    Route::patch('/mypage', [ProfileController::class, 'update']);
    Route::patch('/item/{item_id}', [ItemController::class, 'like']);
    Route::post('/item/{item_id}', [ItemController::class, 'comment']);
    Route::get('/purchase/{item_id}', [ItemController::class, 'purchase']);
    Route::post('/purchase/{item_id}', [ItemController::class, 'buy']);
    Route::post('/stripe/webhook', [ItemController::class, 'success']);
    Route::patch('/purchase/{item_id}', [ItemController::class, 'update']);
    Route::get('/purchase/address/{item_id}', [ItemController::class, 'edit']);
    Route::get('/sell', [ItemController::class, 'create']);
    Route::post('/', [ItemController::class, 'store']);
});

Route::post('/register', [AuthController::class,'store']);
Route::post('/login', [AuthController::class,'login']);
Route::post('/logout', [AuthController::class,'logout']);
Route::get('/', [ItemController::class, 'index']);
Route::get('/item/{item_id}', [ItemController::class, 'show']);

Route::get('/email/verify', [AuthController::class,'verifyNotice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
