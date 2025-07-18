<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\UserTransaction;
use App\Models\Market;
use App\Models\Bank;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;


class ReceiptController extends Controller
{


public function getReceipts(Request $request, $userId = null)
{
    if (!auth()->check()) {
        return response()->json([
            'error'   => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user();

    $query = Receipt::with([
        'user:id,name',
        'market',
        'bank',
        'department:id,name',
        'branch:id,name',
        'bank:id,name,account_number',
        'admin:id,name'
    ]);




    if ($request->has('from') && $request->has('to')) {
        $from = $request->input('from') . ' 00:00:00';
        $to = $request->input('to') . ' 23:59:59';

        $query->whereBetween('created_at', [$from, $to]);
    }

    if ($request->has('user')) {
        $query->where('user_id', $request->input('user'));
    }
    if ($request->has('bank')) {
        $query->where('bank_id', $request->input('bank'));
    }
    if ($request->has('payment_methods')) {
        $query->where('payment_method', $request->input('payment_methods'));
    }
    if ($request->has('status')) {

        $query->where('status', $request->input('status'));
    }

    // Role check
    if ($user->role === 'admin') {
        // Admin sees all
        if($userId != null){
            $receipts = $query->where('user_id', $userId)->orderBy('created_at', 'desc')
            ->paginate(5);
        }else{
            $receipts = $query->orderBy('created_at', 'desc')->paginate(10);
        }
    } elseif ($user->role === 'user') {
        // User sees only his receipts
        $receipts = $query->where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
    } else {
        return response()->json([
            'error'   => true,
            'message' => 'Invalid user role',
        ], 403);
    }

    // Transform each receipt in the current page collection
    $receipts->getCollection()->transform(function ($receipt) {
        $receipt['created_by_me'] = $receipt->created_at->format('Y-m-d H:i:s');

        if ($receipt->image) {
            $receipt['image'] = asset('storage/' . $receipt->image);
        }

        $receipt['amount'] = (double) $receipt->amount;

        return $receipt;
    });

    return response()->json([
        'message'  => 'Receipts retrieved successfully',
        'receipts' => $receipts->items(),
        'meta'     => [
            'current_page' => $receipts->currentPage(),
            'total'        => $receipts->total(),
            'per_page'     => $receipts->perPage(),
            'last_page'    => $receipts->lastPage(),
            'next_page_url' => $receipts->nextPageUrl(),
            'prev_page_url' => $receipts->previousPageUrl(),
        ]
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
            'payment_method' => 'required|in:cash,transfer,check',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8096',
        ]);
        $validated['role'] = $user->role;
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
        $validated['role'] = $user->role;

    } elseif ($user->role === 'user') {
        $validated = $request->validate([
            'market_id' => 'required|exists:markets,id',
            'reference_number' => 'required|string|unique:receipts,reference_number|max:50',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,check',
            'check_number' => 'nullable|string|required_if:payment_method,transfer',
            'bank_id' => 'nullable|exists:banks,id|required_if:payment_method,transfer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8096',
        ]);

        // استخدام قسم وفرع المستخدم الحالي
        $validated['department_id'] = $user->department_id;
        $validated['branch_id'] = $user->branch_id;
        $userId = $user->id;
        $validated['user_id'] = $userId;
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

    if (empty($market->system_market_number)) {
        return response()->json([
            'error' => true,
            'message' => 'Market does not have a client number.',
        ], 400);
    }

    $clientNumber = $market->system_market_number;

    // التعامل مع رفع الصورة
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('receipts', 'public');
    }

    // custom_id
    $lastReceipt = Receipt::orderBy('id', 'desc')->first();
    $newCustomId = $lastReceipt ? str_pad($lastReceipt->id + 1, 6, '0', STR_PAD_LEFT) : '000001';

    $validated['custom_id'] = $newCustomId;
    $validated['client_number'] = $clientNumber;
    $validated['custom_id'] = $newCustomId;
    $validated['image'] = $imagePath;

    // إنشاء الإيصال
    $receipt = Receipt::create($validated);

    $newBalance = $user->balance;
    $user->increment('balance', $validated['amount']);

    // تسجيل الحركة المالية
    // UserTransaction::create([
    //     'user_id' => $user->id,
    //     'receipt_id' => $receipt->id,
    //     'type' => 'not_received',
    //     'amount' => $validated['amount'],
    //     'balance_after' => $newBalance,
    // ]);

    Log::addLog(
        'إضافة إيصال',
        "تم إضافة إيصال جديد بواسطة {$user->name}",
        $user->id
    );

    return response()->json([
        'message' => 'تم إضافة الإيصال بنجاح',
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

    if ($admin->role !== 'admin') {
        return response()->json([
            'error' => true,
            'message' => 'You are not authorized to perform this action',
        ], 403);
    }

    $validated = $request->validate([
        'barcode' => 'required|string',
        'system_receipt_number' => 'required|string|max:255',
    ]);

    $barcode = $validated['barcode'];

    if (!str_contains($barcode, '-')) {
        return response()->json([
            'error' => true,
            'message' => 'Invalid barcode format',
        ], 400);
    }

    [$id, $customId] = explode('-', $barcode);

    if (!is_numeric($id) || empty($customId)) {
        return response()->json([
            'error' => true,
            'message' => 'Invalid barcode components',
        ], 400);
    }

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

    // ✅ تحقق من uniqueness داخل نفس القسم والفرع
    $exists = Receipt::where('system_receipt_number', $validated['system_receipt_number'])
        ->where('department_id', $receipt->department_id)
        ->where('branch_id', $receipt->branch_id)
        ->exists();

    if ($exists) {
        return response()->json([
            'error' => true,
            'message' => 'الرقم موجود مسبقا في القسم او الفرع الخاص بك',
        ], 422);
    }

    $receipt->update([
        'system_receipt_number' => $validated['system_receipt_number'],
        'admin_id' => $admin->id,
        'status' => 'received',
    ]);

    Log::addLog(
        'استلام إيصال',
        "تم استلام إيصال رقم {$customId} بواسطة {$admin->name}",
        $admin->id
    );

    return response()->json([
        'message' => 'تم استلام الواصل بنجاح',
        'receipt' => $receipt,
    ], 200);
}




public function cancelReceipt($receiptId)
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user();

    $receipt = Receipt::find($receiptId);

    if (!$receipt) {
        return response()->json([
            'error' => true,
            'message' => 'الايصال غير موجود',
        ], 404);
    }

    if ($receipt->status === 'received') {
        return response()->json([
            'error' => true,
            'message' => 'لا يمكنك الغاء ايصال قد تم استلامه مسبقا',
        ], 400);
    }

    $receipt->update([
        'status' => 'canceled',
    ]);

    Log::addLog(
        'إلغاء إيصال',
        "تم إلغاء الإيصال رقم {$receipt->id} بواسطة {$user->name}",
        $user->id
    );

    return response()->json([
        'message' => 'تم الغاء الإيصال بنجاح',
        'receipt' => $receipt,
    ], 200);
}

public function printReceiptAsPDF($receiptId)
{
    $receipt = Receipt::with(['user', 'market', 'bank', 'admin', 'department', 'branch'])->find($receiptId);

    if (!$receipt) {
        return response()->json([
            'error' => true,
            'message' => 'Receipt not found',
        ], 404);
    }


}


}
