<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Market;
use App\Models\Bank;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    // public function index(Request $request)
    // {
    //     $receipts = Receipt::query();
    
    //     // التحقق من وجود فلتر للحالة
    //     if ($request->has('status')) {
    //         $receipts->where('status', $request->status);
    //     }
    
    //     // إحضار الإيصالات مع بيانات المستخدم، المصرف، والسوق
    //     $receipts = $receipts->with(['user', 'bank', 'market'])->get();
    
    //     return response()->json([
    //         'receipts' => $receipts,
    //     ]);
    // }
    
//     public function userReceipts(Request $request)
// {
//     if (!auth()->check()) {
//         return response()->json([
//             'error' => true,
//             'message' => 'You Are Not Authenticated',
//         ], 401);
//     }

//     // الحصول على المستخدم الحالي
//     $userId = auth()->id();

//     // استعلام الإيصالات مع البيانات المرتبطة
//     $receipts = Receipt::where('user_id', $userId)
//         ->with(['user', 'bank', 'market'])
//         ->get();

//     if ($receipts->isEmpty()) {
//         return response()->json([
//             'message' => 'No receipts associated with the current user',
//         ], 404);
//     }

//     return response()->json([
//         'message' => 'User receipts retrieved successfully',
//         'receipts' => $receipts,
//     ], 200);
// }
public function getReceipts(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user();

    if ($user->role === 'admin') {
        // عرض جميع الإيصالات إذا كان المستخدم Admin
        $receipts = Receipt::with(['user', 'market', 'bank'])->get();
    } elseif ($user->role === 'user') {
        // عرض الإيصالات المرتبطة بالمستخدم الحالي
        $receipts = $user->receipts()->with(['market', 'bank'])->get();
    } else {
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    if ($receipts->isEmpty()) {
        return response()->json([
            'message' => 'No receipts available',
        ], 404);
    }

    return response()->json([
        'message' => 'Receipts retrieved successfully',
        'receipts' => $receipts,
    ], 200);
}


public function store(Request $request)
{
    // التحقق من أن المستخدم مسجل دخوله
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    // الحصول على بيانات المستخدم الحالي
    $user = auth()->user();

    // تحقق من أن الدور (role) للمستخدم محدد
    if (!$user->role) {
        return response()->json([
            'error' => true,
            'message' => 'User role is not defined. Please check the role field.',
        ], 500);
    }

    // تسجيل قيمة role للمساعدة في التتبع
    \Log::info('User Role: ' . $user->role);

    // التحقق من الدور وتحديد البيانات المطلوبة بناءً عليه
    if ($user->role === 'admin') {
        // إذا كان الدور admin، تأكد من وجود user_id في البيانات المرسلة
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // التحقق من وجود user_id إذا كان admin
            'market_id' => 'required|exists:markets,id',
            'client_number' => 'required|string|max:20',
            'reference_number' => 'required|string|unique:receipts,reference_number|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // التحقق من الصورة
        ]);

        $userId = $validated['user_id']; // أخذ user_id المرسل من الـ request
    } elseif ($user->role === 'user') {
        // إذا كان الدور user، سيتم أخذ user_id من المستخدم الحالي
        $validated = $request->validate([
            'market_id' => 'required|exists:markets,id',
            'client_number' => 'required|string|max:20',
            'reference_number' => 'required|string|unique:receipts,reference_number|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // التحقق من الصورة
        ]);

        $userId = $user->id; // تعيين user_id للمستخدم الحالي
    } else {
        // إذا كان الدور غير معروف
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    // التعامل مع رفع الصورة
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('receipts', 'public'); // حفظ الصورة في مجلد `receipts`
    }

    // إنشاء الإيصال
    $receipt = Receipt::create([
        'user_id' => $userId,  // إضافة user_id
        'market_id' => $validated['market_id'],
        'client_number' => $validated['client_number'],
        'reference_number' => $validated['reference_number'],
        'amount' => $validated['amount'],
        'payment_method' => $validated['payment_method'],
        'check_number' => $validated['payment_method'] === 'transfer' ? $validated['check_number'] : null,
        'bank_id' => $validated['payment_method'] === 'transfer' ? $validated['bank_id'] : null,
        'image' => $imagePath,
        'status' => 'not_received', // الحالة الافتراضية
    ]);

    // استجابة بنجاح إنشاء الإيصال
    return response()->json([
        'message' => 'Receipt created successfully',
        'receipt' => $receipt,
    ], 200);
}

    public function updateStatus(Request $request, $id)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $validated = $request->validate([
        'status' => 'required|in:received,not_received',
    ]);

    $receipt = Receipt::find($id);

    if (!$receipt) {
        return response()->json([
            'error' => true,
            'message' => 'Receipt not found',
        ], 404);
    }

    $receipt->update(['status' => $validated['status']]);

    return response()->json([
        'message' => 'Receipt status updated successfully',
        'receipt' => $receipt,
    ], 200);
}

    
}
