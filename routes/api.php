<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\BankController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });
// });

Route::prefix('mobile')->group(function () {
    // Auth Section
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
    // End Auth Section
    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/markets', [MarketController::class, 'index']); // عرض كل الأسواق
    Route::post('/markets', [MarketController::class, 'store']); // إضافة سوق جديد
    Route::put('/markets/{id}', [MarketController::class, 'update']); // تحديث سوق
    Route::delete('/markets/{id}', [MarketController::class, 'destroy']); // حذف سوق

    Route::get('/banks', [BankController::class, 'index']); // عرض كل المصارف
    Route::post('/banks', [BankController::class, 'store']); // إضافة مصرف جديد
    Route::put('/banks/{id}', [BankController::class, 'update']); // تحديث مصرف
    Route::delete('/banks/{id}', [BankController::class, 'destroy']); // حذف مصرف
    });
    // Get Section

    // End Get Section



});
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/markets', [MarketController::class, 'index']); // عرض كل الأسواق
//     Route::post('/markets', [MarketController::class, 'store']); // إضافة سوق جديد
//     Route::put('/markets/{id}', [MarketController::class, 'update']); // تحديث سوق
//     Route::delete('/markets/{id}', [MarketController::class, 'destroy']); // حذف سوق
// });

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

