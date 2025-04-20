<?php
namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{

    // public function index()
    // {
    //     $user = auth()->user();
    
    //     if (!$user) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'You Are Not Authenticated',
    //         ], 401);
    //     }
    
    //     if ($user->role === 'admin') {
    //         // عرض المصارف التي تتبع لنفس فرع الأدمن
    //         $banks = Bank::with('branch')
    //             ->where('branch_id', $user->branch_id)
    //             ->get();
    //     } elseif ($user->role === 'user') {
    //         // يمكن عرض جميع المصارف أو تخصيصها حسب الحاجة
    //         $banks = Bank::with('branch')->get();
    //     } else {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Invalid user role',
    //         ], 403);
    //     }
    
    //     return response()->json([
    //         'banks' => $banks,
    //     ]);
    // }
    
    public function index()
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    if ($user->role === 'admin') {
        // جلب الفروع المخصصة للمستخدم
        $branchIds = $user->branches()->pluck('branches.id')->toArray();

        $banks = Bank::with('branch')
            ->when(!empty($branchIds), function ($query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds);
            }, function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->get();
    } elseif ($user->role === 'user') {
        $banks = Bank::with('branch')->get(); // أو يمكنك تحديد حسب الحاجة
    } else {
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    return response()->json([
        'banks' => $banks,
    ]);
}

    // إضافة مصرف جديد
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:banks,account_number',
            'branch' => 'required|string|max:255',
            'branch_id' => 'exists:branches,id',
            // 'status' => 'required|string|in:active,inactive,deleted',
        ]);
        $validated['status'] = 'active';

        $bank = Bank::create($validated);

        return response()->json([
            'message' => 'تم إضافة المصرف بنجاح',
            'bank' => $bank,
        ], 200);
    }

    // تحديث مصرف
// تحديث بيانات مصرف
public function update(Request $request, $id)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    // البحث عن المصرف
    $bank = Bank::find($id);

    // التحقق من وجود المصرف
    if (!$bank) {
        return response()->json([
            'error' => true,
            'message' => 'Bank not found',
        ], 404);
    }

    // التحقق من البيانات المدخلة
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'account_number' => 'required|string|max:255|unique:banks,account_number,' . $bank->id,
        'branch' => 'required|string|max:255',
        'status' => 'required|string|in:active,inactive,deleted',
    ]);

    // تحديث بيانات المصرف
    $bank->update($validated);

    return response()->json([
        'message' => 'تم تحديث بيانات المصرف بنجاح',
        'bank' => $bank,
    ], 200);
}

// حذف مصرف
public function destroy($id)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    // البحث عن المصرف
    $bank = Bank::find($id);

    // التحقق من وجود المصرف
    if (!$bank) {
        return response()->json([
            'error' => true,
            'message' => 'Bank not found',
        ], 404);
    }

    // تحديث الحالة إلى "deleted" بدلاً من حذف المصرف فعليًا
    $bank->update(['status' => 'deleted']);

    return response()->json([
        'message' => 'تم حذف المصرف ',
    ], 200);
}

}
