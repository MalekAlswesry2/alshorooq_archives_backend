<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    // استرجاع الفروع
    public function index()
    {
        $branches = Branch::where('status', 'active')->get();

        return response()->json([
            'message' => 'Branches retrieved successfully',
            'branches' => $branches,
        ], 200);
    }

    // إضافة فرع جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            // 'status' => 'required|in:active,inactive',
        ]);

        $branch = Branch::create($validated);

        return response()->json([
            'message' => 'تم إضافة الفرع',
            'branch' => $branch,
        ], 200);
    }

    // تعديل فرع
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            // 'status' => 'sometimes|in:active,inactive',
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'تم تحديث الفرع',
            'branch' => $branch,
        ], 200);
    }

    // حذف فرع (تغيير الحالة إلى inactive)
    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        $branch->update(['status' => 'inactive']);

        return response()->json([
            'message' => ' تم حذف الفرع',
        ], 200);
    }
}
