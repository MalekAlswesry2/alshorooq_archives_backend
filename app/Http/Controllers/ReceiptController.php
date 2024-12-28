<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\UserTransaction;
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
// public function getReceipts(Request $request)
// {
//     if (!auth()->check()) {
//         return response()->json([
//             'error' => true,
//             'message' => 'You Are Not Authenticated',
//         ], 401);
//     }

//     $user = auth()->user();

//     if ($user->role === 'admin') {
//         // عرض جميع الإيصالات إذا كان المستخدم Admin
//         $receipts = Receipt::with(['user', 'market', 'bank'])->get();
//     } elseif ($user->role === 'user') {
//         // عرض الإيصالات المرتبطة بالمستخدم الحالي
//         $receipts = $user->receipts()->with(['market', 'bank'])->get();
//     } else {
//         return response()->json([
//             'error' => true,
//             'message' => 'Invalid user role',
//         ], 403);
//     }

//     if ($receipts->isEmpty()) {
//         return response()->json([
//             'message' => 'No receipts available',
//         ], 404);
//     }

//     return response()->json([
//         'message' => 'Receipts retrieved successfully',
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
        $receipts = Receipt::with(['user', 'market', 'bank','admin'])
        ->orderBy('created_at','desc')
        ->get();
    } elseif ($user->role === 'user') {
        // عرض الإيصالات المرتبطة بالمستخدم الحالي
        $receipts = $user->receipts()->with(['market', 'bank','admin'])
        ->orderBy('created_at', 'desc')  // ترتيب حسب التاريخ من الأحدث إلى الأقدم
        ->get();
    } else {
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    if ($receipts->isEmpty()) {
        return response()->json([
            'message' => 'No receipts available',
            'receipts' => $receipts,
        ], 200);
    }

    // تعديل الصورة في كل إيصال
    $receipts = $receipts->map(function ($receipt) {
        if ($receipt->image) {
            $receipt->image = asset('storage/' . $receipt->image); 
        }
        return $receipt;
    });

    return response()->json([
        'message' => 'Receipts retrieved successfully',
        'receipts' => $receipts,
    ], 200);
}

public function store(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user();

    if (!$user->role) {
        return response()->json([
            'error' => true,
            'message' => 'User role is not defined. Please check the role field.',
        ], 500);
    }

    if ($user->role === 'admin') {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'market_id' => 'required|exists:markets,id',
            'reference_number' => 'required|string|unique:receipts,reference_number|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userId = $validated['user_id'];
    } elseif ($user->role === 'user') {
        $validated = $request->validate([
            'market_id' => 'required|exists:markets,id',
            'reference_number' => 'required|string|unique:receipts,reference_number|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $userId = $user->id;
    } else {
        return response()->json([
            'error' => true,
            'message' => 'Invalid user role',
        ], 403);
    }

   // جلب بيانات السوق للتحقق من وجوده والحصول على client_number
   $market = Market::find($validated['market_id']);
   if (!$market) {
       return response()->json([
           'error' => true,
           'message' => 'Market not found',
       ], 404);
   }

   // تحقق من وجود client_number في السوق
   if (empty($market->system_market_number)) {
       return response()->json([
           'error' => true,
           'message' => 'Market does not have a client number.',
       ], 400);
   }

   // تعيين client_number من بيانات السوق
   $clientNumber = $market->system_market_number;

    // التعامل مع رفع الصورة
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('receipts', 'public');
    }

        // توليد custom_id
        $lastReceipt = Receipt::orderBy('id', 'desc')->first();
        $newCustomId = $lastReceipt ? str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : '000001';
    

    // إنشاء الإيصال
    $receipt = Receipt::create([
        'user_id' => $userId,
        'market_id' => $validated['market_id'],
        'client_number' => $clientNumber, // استخدام client_number من السوق
        'reference_number' => $validated['reference_number'],
        'amount' => $validated['amount'],
        'payment_method' => $validated['payment_method'],
        'check_number' => $validated['payment_method'] === 'transfer' ? $validated['check_number'] : null,
        'bank_id' => $validated['payment_method'] === 'transfer' ? $validated['bank_id'] : null,
        'image' => $imagePath,
        'status' => 'not_received',
        'custom_id' => $newCustomId,
        'department' => $user->department,
    ]);

    $newBalance = $user->balance;
    $user->increment('balance', $validated['amount']);

    // تسجيل الحركة المالية
    UserTransaction::create([
        'user_id' => $user->id,
        'receipt_id' => $receipt->id,
        'type' => 'not_received',
        'amount' => $validated['amount'],
        'balance_after' => $newBalance,
    ]);

    return response()->json([
        'message' => 'Receipt created successfully',
        'receipt' => $receipt,
    ], 200);
}



public function updateStatus(Request $request)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $admin = auth()->user();

    // التحقق إذا كان المستخدم الحالي هو أدمن
    if ($admin->role !== 'admin') {
        return response()->json([
            'error' => true,
            'message' => 'You are not authorized to perform this action',
        ], 403);
    }

    // التحقق من البيانات
    $validated = $request->validate([
        'barcode' => 'required|string', // الباركود
        'system_receipt_number' => 'required|string|max:255|unique:receipts,system_receipt_number',
    ]);

    // استخراج الباركود
    $barcode = $validated['barcode'];

    // التحقق من تنسيق الباركود
    if (!str_contains($barcode, '-')) {
        return response()->json([
            'error' => true,
            'message' => 'Invalid barcode format',
        ], 400);
    }

    // تقسيم الباركود للحصول على id و custom_id
    [$id, $customId] = explode('-', $barcode);

    // التحقق من صحة الأجزاء
    if (!is_numeric($id) || empty($customId)) {
        return response()->json([
            'error' => true,
            'message' => 'Invalid barcode components',
        ], 400);
    }

    // التحقق من الواصل
    $receipt = Receipt::where('id', $id)
        ->where('custom_id', $customId)
        ->first();

    if (!$receipt) {
        return response()->json([
            'error' => true,
            'message' => 'Receipt not found or barcode mismatch',
        ], 404);
    }

    if ($receipt->status === 'received') {
        return response()->json([
            'error' => true,
            'message' => 'The receipt status is already received. No changes will be made.',
        ], 400);
    }


    // تحديث حالة الواصل
    $receipt->update([
        'system_receipt_number' => $validated['system_receipt_number'],
        'admin_id' => $admin->id,      
        'status' => 'received',
    ]);


    return response()->json([
        'message' => 'Receipt status updated successfully',
        'receipt' => $receipt,
    ], 200);
}


    // public function updateStatus(Request $request, $id)
    // {
    //     if (!auth()->check()) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'You Are Not Authenticated',
    //         ], 401);
    //     }
    //     $user = auth()->user();
    //     $validated = $request->validate([
    //         'status' => 'required|in:received,not_received',
    //         'system_receipt_number' => 'required|string|max:255|unique:receipts,system_receipt_number',

    //     ]);

    //     $receipt = Receipt::find($id);

    //     if (!$receipt) {
    //         return response()->json([
    //             'error' => true,
    //             'message' => 'Receipt not found',
    //         ], 404);
    //     }
    //     $newBalance = $user->balance;

    //     $receipt->update([
    //         'status' => 'received',
    //         'system_receipt_number' => $validated['system_receipt_number']
    //     ]);
    //     $user->decrement('balance', $validated['amount']); // خفض الرصيد
    //     UserTransaction::create([
    //         'user_id' => $user->id,
    //         'receipt_id' => $receipt->id,
    //         'type' => 'received',
    //         'amount' => $validated['amount'],
    //         'balance_after' => $newBalance,
    //     ]);
    //     return response()->json([
    //         'message' => 'Receipt status updated successfully',
    //         'receipt' => $receipt,
    //     ], 200);
    // }

    
}
