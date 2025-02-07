<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\UserController;
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

    Route::get('/zones', [ZoneController::class, 'index']);
    Route::get('/areas', [AreaController::class, 'allAreas']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/branches', [BranchController::class, 'index']);

    // Auth Section
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
    // End Auth Section
    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/permissions', [UserController::class, 'showAllRoles']);
        Route::post('/assign-permission/{userId}', [UserController::class, 'assignPermission']);
        Route::post('/remove-permission/{userId}', [UserController::class, 'removePermission']);
        Route::get('/user-permissions/{userId}', [UserController::class, 'checkUserPermissions']);
    
    // add admins
    Route::post('/add-admin', [UserController::class, 'addAdmin']);

    // Route::middleware(['auth:sanctum', 'admin'])->post('/add-admin', [UserController::class, 'addAdmin']);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);

    Route::get('/markets', [MarketController::class, 'getMarkets']);
    Route::get('/receipts', [ReceiptController::class, 'getReceipts']);

    // Route::get('/current_user_markets', [MarketController::class, 'userMarkets']); // عرض كل الأسواق
    Route::post('/markets', [MarketController::class, 'store']); // إضافة سوق جديد
    Route::put('/markets/{id}', [MarketController::class, 'update']); // تحديث سوق
    Route::delete('/markets/{id}', [MarketController::class, 'destroy']); // حذف سوق

    Route::get('/banks', [BankController::class, 'index']); // عرض كل المصارف
    Route::post('/banks', [BankController::class, 'store']); // إضافة مصرف جديد
    Route::put('/banks/{id}', [BankController::class, 'update']); // تحديث مصرف
    Route::delete('/banks/{id}', [BankController::class, 'destroy']); // حذف مصرف

    // Route::post('/receipts', [ReceiptController::class, 'store']);
    Route::prefix('receipts')->group(function () {
        // Route::get('/current_user-receipts', [ReceiptController::class, 'userReceipts']);
        // Route::get('/', [ReceiptController::class, 'index']); // عرض جميع الإيصالات
        Route::post('/', [ReceiptController::class, 'store']); // إضافة إيصال جديد
        // Route::put('/{id}/status', [ReceiptController::class, 'updateStatus']); // تحديث حالة الإيصال
        Route::put('/update-status', [ReceiptController::class, 'updateStatus']); // تحديث حالة الإيصال
    });
    // Route::get('/markets', [MarketController::class, 'index'])->middleware('permission:can_view');
    // Route::post('/markets', [MarketController::class, 'store'])->middleware('permission:can_edit');


        Route::post('/zones', [ZoneController::class, 'store']);
        Route::get('/areas_on_zone', [AreaController::class, 'getAreasDebOnZone']);


        // Route::get('/areas', [AreaController::class, 'allAreas']);


        Route::get('/users', [UserController::class, 'getUsersWithUserRole']);

        Route::get('/zones/{zoneId}/areas', [AreaController::class, 'index']);
        // إنشاء منطقة جديدة
        Route::post('/areas', [AreaController::class, 'store']);
    

        // الاقسام

        // Route::get('/departments', [DepartmentController::class, 'index']);
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{id}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

        // الفروع

        // Route::get('/branches', [BranchController::class, 'index']);
        Route::post('/branches', [BranchController::class, 'store']);
        Route::put('/branches/{id}', [BranchController::class, 'update']);
        Route::delete('/branches/{id}', [BranchController::class, 'destroy']);

        
        Route::get('/receipts/{id}/pdfs', [ReceiptController::class, 'printReceiptAsPDF']);

    // Route::get('/user/permissions', function () {
    //     return response()->json([
    //         'permissions' => auth()->user()->permissions->pluck('name'),
    //     ]);
    // });
    

    // Route::post('/user/{user}/permissions', function (\App\Models\User $user, Request $request) {
    //     $permissions = \App\Models\Permission::whereIn('name', $request->permissions)->pluck('id');
    //     $user->permissions()->sync($permissions); // sync لتحديث الصلاحيات
    //     return response()->json(['message' => 'Permissions updated successfully']);
    // });

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

