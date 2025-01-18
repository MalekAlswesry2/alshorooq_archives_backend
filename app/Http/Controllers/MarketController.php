<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Market;
use App\Models\User;

class MarketController extends Controller
{

    public function store(Request $request)
{
    // التحقق من المصادقة
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    try {
        $user = auth()->user();

        // التحقق من البيانات بناءً على دور المستخدم
        $userId = $user->id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'area_id' => 'required|exists:areas,id',
            'phone' => 'required|unique:markets|string|max:15',
            'address' => 'required|string|max:255',
            'system_market_number' => 'required|string|unique:markets,system_market_number',
        ]);

        


        if ($user->role === 'admin') {
            // التحقق من القسم والفرع في حالة الادمن
            // $validated['department_id'] = $request->input('department_id'); 
            // $validated['branch_id'] = $request->input('branch_id'); 
            $validated['user_id'] = $request->input('user_id'); 
            $selectedUserId = $validated['user_id'];
            $selectedUser = User::find($selectedUserId);

            if (!$selectedUser) {
                return response()->json([
                    'error' => true,
                    'message' => 'User not found',
                ], 404);
            }

                $department = $selectedUser->department; 
                $branch = $selectedUser->branch; 

                if (!$department || !$branch) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Department or branch not assigned to this user',
                    ], 400);
                }

                $validated['department_id'] = $department->id; 
                $validated['branch_id'] = $branch->id; 
                // $validated['user_id'] = $selectedUserId; 
        }

       
        if ($user->role === 'user') {
            $validated['department_id'] = $user->department_id;
            $validated['branch_id'] = $user->branch_id;
            $validated['user_id'] = $userId;
        }

        // تعيين الحالة الافتراضية
        $validated['status'] = 'active';

        // إنشاء السوق
        $market = Market::create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Market created successfully',
            'market' => $market,
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // الرد إذا فشل التحقق من البيانات
        return response()->json([
            'error' => true,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 400);
    } catch (\Exception $e) {
        // الرد إذا حدث خطأ آخر
        return response()->json([
            'error' => true,
            'message' => 'Failed to create market',
            'details' => $e->getMessage(),
        ], 500);
    }
}

    
//     public function store(Request $request)
// {
//     // التحقق من المصادقة
//     if (!auth()->check()) {
//         return response()->json([
//             'error' => true,
//             'message' => 'You Are Not Authenticated',
//         ], 401);
//     }

//     try {
//         $user = auth()->user();

//         // التحقق من البيانات بناءً على دور المستخدم
//         $userId = $user->id;
//         if ($user->role === 'admin') {
//             $validated = $request->validate([
//                 'name' => 'required|string|max:255',
//                 'phone' => 'nullable|string|max:15',
//                 'address' => 'required|string|max:255',
//                 'system_market_number' => 'required|string|unique:markets,system_market_number',
//                 'user_id' => 'required|exists:users,id',
//             ]);
//         }


//         // إذا كان المستخدم "user"، يتم تعيين user_id إلى معرف المستخدم المصادق عليه
//         if ($user->role === 'user') {
//             $validated = $request->validate([
//                 'name' => 'required|string|max:255',
//                 'phone' => 'nullable|string|max:15',
//                 'address' => 'required|string|max:255',
//                 'system_market_number' => 'required|string|unique:markets,system_market_number',
//             ]);
//             $validated['user_id'] = $userId;
//             $validated['branch_id'] = $user->branch_id;
//             $validated['department_id'] = $user->department_id;

//         }

//         // تعيين الحالة الافتراضية
//         $validated['status'] = 'active';

//         // إنشاء السوق
//         $market = Market::create($validated);

//         return response()->json([
//             'error' => false,
//             'message' => 'Market created successfully',
//             'market' => $market,
//         ], 200);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // الرد إذا فشل التحقق من البيانات
//         return response()->json([
//             'error' => true,
//             'message' => 'Validation failed',
//             'errors' => $e->errors(),
//         ], 400);
//     } catch (\Exception $e) {
//         // الرد إذا حدث خطأ آخر
//         return response()->json([
//             'error' => true,
//             'message' => 'Failed to create market',
//             'details' => $e->getMessage(),
//         ], 500);
//     }
// }


    
    public function getMarkets(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }
    
        $user = auth()->user();
    
        if ($user->role === 'admin') {
            // عرض جميع الأسواق إذا كان المستخدم Admin
            $markets = Market::with(['user:id,name','branch:id,name','department:id,name'])->get();
        } elseif ($user->role === 'user') {
            // عرض الأسواق المرتبطة بالمستخدم الحالي
            // $markets = $user->markets()->with('user')->get();
            $markets = $user->markets()->with(['user:id,name','branch:name','department:id,name'])->get();

        } else {
            return response()->json([
                'error' => true,
                'message' => 'Invalid user role',
            ], 403);
        }
    
        if ($markets->isEmpty()) {
            return response()->json([
                'message' => 'No markets available',
                'markets' => $markets,
            ], 200);
        }
    
        return response()->json([
            'message' => 'Markets retrieved successfully',
            'markets' => $markets,
        ], 200);
    }
    
    
    public function update(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }
    
        // البحث عن السوق
        $market = Market::find($id);
    
        if (!$market) {
            return response()->json([
                'error' => true,
                'message' => 'Market not found',
            ], 404);
        }
    
        // التحقق من البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'required|string|max:255',
            'system_market_number' => 'required|string|unique:markets,system_market_number,' . $id,
            'status' => 'required|string|in:active,inactive,deleted',
        ]);
    
        // تحديث بيانات السوق
        $market->update($validated);
    
        return response()->json([
            'message' => 'Market updated successfully',
            'market' => $market,
        ], 200);
    }
    


    // حدف السوق
    public function destroy($id)
    {
        // التحقق من المصادقة
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }
    
        // الحصول على السوق بناءً على id
        $market = Market::find($id);
    
        if (!$market) {
            return response()->json([
                'error' => true,
                'message' => 'Market not found',
            ], 404);
        }
    
        // التحقق من أن المستخدم هو من قام بإضافة السوق
        if ($market->user_id !== auth()->id()) {
            return response()->json([
                'error' => true,
                'message' => 'You are not authorized to delete this market',
            ], 403);
        }
    
        // تحديث الحالة إلى "deleted" بدلاً من حذف السوق فعليًا
        $market->update(['status' => 'deleted']);
    
        return response()->json(['message' => 'Market marked as deleted'], 200);
    }
    
}
