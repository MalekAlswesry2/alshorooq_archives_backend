<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // إضافة قسم جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'status' => 'required|in:active,inactive',
        ]);

        $department = Department::create($validated);

        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department,
        ], 200);
    }

    // تعديل قسم
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 'status' => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department,
        ], 200);
    }

    // حذف قسم (تغيير الحالة إلى inactive)
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        $department->update(['status' => 'inactive']);

        return response()->json([
            'message' => 'Department marked as inactive',
        ], 200);
    }

    // استرجاع الأقسام
    public function index()
    {
        $departments = Department::where('status', 'active')->get();

        return response()->json([
            'message' => 'Departments retrieved successfully',
            'departments' => $departments,
        ], 200);
    }
}
