<?php
namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    // عرض كل المصارف
    public function index()
    {

        if (!auth()->user()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }

        $banks = Bank::with(['branch'])->get();
        // $markets = $user->markets()->with(['user:id,name','branch:name','department:id,name'])->get();

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
            'message' => 'Bank created successfully',
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
        'message' => 'Bank updated successfully',
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
        'message' => 'Bank marked as deleted',
    ], 200);
}

}
