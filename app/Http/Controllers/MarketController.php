<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use App\Models\Market;
use App\Models\User;
use Illuminate\Validation\Rule;

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
            'phone' => [
                'required',
                Rule::unique('markets')->where(function ($query) use ($user, $request) {
                    return $query->where('department_id', $user->department_id)
                                ->where('phone', $request->phone);
                }),
            ],
            'address' => 'required|string|max:255',
            'system_market_number' => 'required|string|unique:markets,system_market_number',

        ]);


        if ($user->role === 'admin') {
            // التحقق من القسم والفرع في حالة الادمن

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
        Log::addLog(
            'إضافة سوق جديد',
            "تم إضافة سوق {$market->name} بواسطة {$user->name}",
            $user->id
        );
        return response()->json([
            'error' => false,
            'message' => 'تم اضافة السوق ',
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


public function getMarkets(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user();
    $perPage = $request->query('per_page', 20); 

    if ($user->role === 'admin') {
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        $departmentIds = $user->departments()->pluck('departments.id')->toArray();

        $markets = Market::with(['user:id,name', 'branch:id,name', 'department:id,name'])
            ->when(!empty($branchIds), function ($query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds);
            }, function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->when(!empty($departmentIds), function ($query) use ($departmentIds) {
                $query->whereIn('department_id', $departmentIds);
            }, function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->paginate($perPage);

    } elseif ($user->role === 'user') {
        $markets = $user->markets()->with(['user:id,name', 'branch:id,name', 'department:id,name'])->paginate($perPage);
    } else {
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    return response()->json([
        'message' => $markets->isEmpty() ? 'No markets available' : 'Markets retrieved successfully',
        'markets' => $markets->items(),
        'meta' => [
            'current_page' => $markets->currentPage(),
            'last_page' => $markets->lastPage(),
            'per_page' => $markets->perPage(),
            'total' => $markets->total(),
        ],
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
        // Log::addLog(
        //     'إضافة سوق جديد',
        //     "تم إضافة سوق {$market->name} بواسطة {$user->name}",
        //     $user->id
        // );
        return response()->json([
            'message' => 'تم تحديث السوق',
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
    
        return response()->json(['message' => 'تم حذف السوق'], 200);
    }
    
}
