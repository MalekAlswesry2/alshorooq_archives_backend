<?php
namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\Market;
use App\Models\Bank;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request)
{
    $receipts = Receipt::query();

    // التحقق من وجود فلتر للحالة
    if ($request->has('status')) {
        $receipts->where('status', $request->status);
    }

    return response()->json([
        'receipts' => $receipts->get(),
    ]);
}


    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }
    
        // التحقق من صحة البيانات
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
    
        // التعامل مع رفع الصورة
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('receipts', 'public'); // حفظ الصورة في مجلد `receipts`
        }
    
        // إنشاء الإيصال
        $receipt = Receipt::create([
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
