<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Market;

class MarketController extends Controller
{
    
    // إضافة سوق جديد
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
            // التحقق من البيانات
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:15',
                'address' => 'required|string|max:255',
                'system_market_number' => 'required|string|unique:markets,system_market_number',
            ]);

            // إضافة user_id للمستخدم المصادق عليه
            $validated['user_id'] = auth()->id();
            $validated['status'] = 'active'; // تعيين القيمة الافتراضية

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

        

    // عرض الأسواقW
    // public function index()
    // {

    //     if (!auth()->check()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'You Are Not Authenticated',
    //         ], 401);
    //     }
        
        
    //     $markets = Market::where('status', '!=', 'deleted')->with('user')->get();

    //     return response()->json($markets, 200);
    // }
    // public function index(Request $request)
    // {
    //     if (!auth()->check()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'You Are Not Authenticated',
    //         ], 401);
    //     }
    
    //     // الحصول على استعلام البحث إذا وُجد
    //     $query = $request->query('search');
    
    //     // استعلام الأسواق
    //     $markets = Market::query();
    
    //     // تطبيق الفلاتر إذا وُجد استعلام بحث
    //     if ($query) {
    //         $markets = $markets->where('name', 'LIKE', "%$query%")
    //             ->orWhere('phone', 'LIKE', "%$query%")
    //             ->orWhere('system_market_number', 'LIKE', "%$query%");
    //     }
    
    //     // تنفيذ الاستعلام وجلب النتائج
    //     $markets = $markets->get();
    
    //     if ($markets->isEmpty()) {
    //         return response()->json([
    //             'message' => $query ? 'No markets found for the given query' : 'No markets available',
    //         ], 404);
    //     }
    
    //     return response()->json([
    //         'message' => $query ? 'Markets retrieved successfully for the given query' : 'Markets retrieved successfully',
    //         'markets' => $markets,
    //     ], 200);
    // }
    
    // public function userMarkets(Request $request)
    // {
    //     if (!auth()->check()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'You Are Not Authenticated',
    //         ], 401);
    //     }
    
    //     // الحصول على المستخدم الحالي
    //     $userId = auth()->id();
    
    //     // جلب الأسواق المرتبطة بالمستخدم الحالي فقط
    //     $markets = Market::where('user_id', $userId)->get();
    
    //     if ($markets->isEmpty()) {
    //         return response()->json([
    //             'message' => 'No markets associated with the current user',
    //         ], 404);
    //     }
    
    //     return response()->json([
    //         'message' => 'User markets retrieved successfully',
    //         'markets' => $markets,
    //     ], 200);
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
            $markets = Market::with(['user:id,name'])->get();
        } elseif ($user->role === 'user') {
            // عرض الأسواق المرتبطة بالمستخدم الحالي
            // $markets = $user->markets()->with('user')->get();
            $markets = $user->markets()->with(['user:id,name'])->get();

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
