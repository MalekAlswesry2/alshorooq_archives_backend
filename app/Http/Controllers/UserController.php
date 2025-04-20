<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * عرض جميع المستخدمين الذين لديهم دور user.
     */

//      public function getUsersWithUserRole()
// { 
//     if (!auth()->check()) {
//         return response()->json([
//             'error' => true,
//             'message' => 'You Are Not Authenticated',
//         ], 401);
//     }

//     $admin = auth()->user();

//     if ($admin->role !== 'admin') {
//         return response()->json([
//             'error' => true,
//             'message' => 'Unauthorized. Only admin can access this.',
//         ], 403);
//     }

//     $users = User::where('role', '!=', 'master')
//         ->where('department_id', $admin->department_id)
//         ->where('branch_id', $admin->branch_id)
//         ->with('permissions:id,name,key')
//         ->get(['id', 'name', 'email', 'phone', 'zone_id', 'department_id', 'branch_id', 'role']);

//     $users->each(function ($user) {
//         $user->permissions->makeHidden('pivot');
//     });

//     if ($users->isEmpty()) {
//         return response()->json([
//             'message' => 'No users found in your department and branch',
//             'users' => $users,
//         ], 200);
//     }

//     return response()->json([
//         'message' => 'Users retrieved successfully',
//         'users' => $users,
//     ], 200);
// }
public function getUsersWithUserRole()
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
            'message' => 'Unauthorized. Only admin can access this.',
        ], 403);
    }

    // جلب الفروع والأقسام المرتبطة بالمشرف من الجداول الوسيطة
    $branchIds = $admin->branches()->pluck('branches.id')->toArray();
    $departmentIds = $admin->departments()->pluck('departments.id')->toArray();

    // في حال لم يتم تحديدها من الجداول الوسيطة نرجع للقيم الفردية
    if (empty($branchIds) && $admin->branch_id) {
        $branchIds[] = $admin->branch_id;
    }

    if (empty($departmentIds) && $admin->department_id) {
        $departmentIds[] = $admin->department_id;
    }

    // جلب المستخدمين المرتبطين بهذه الفروع أو الأقسام
    $users = User::where('role', '!=', 'master')
        ->where(function ($query) use ($branchIds, $departmentIds) {
            $query->whereIn('branch_id', $branchIds)
                  ->whereIn('department_id', $departmentIds);
        })
        ->with('permissions:id,name,key')
        ->get(['id', 'name', 'email', 'phone', 'zone_id', 'department_id', 'branch_id', 'role']);

    // إزالة الـ pivot
    $users->each(fn($user) => $user->permissions->makeHidden('pivot'));

    return response()->json([
        'message' => 'Users retrieved successfully',
        'users' => $users,
    ], 200);
}



public function getUsersWithReceiptsOrder()
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
            'message' => 'Unauthorized. Only admin can access this.',
        ], 403);
    }

    // جلب الفروع والأقسام المخصصة من خلال الجداول الوسيطة
    $branchIds = $admin->branches()->pluck('branches.id')->toArray();
    $departmentIds = $admin->departments()->pluck('departments.id')->toArray();

    // fallback في حال لم تُخصص الفروع أو الأقسام
    if (empty($branchIds) && $admin->branch_id) {
        $branchIds[] = $admin->branch_id;
    }

    if (empty($departmentIds) && $admin->department_id) {
        $departmentIds[] = $admin->department_id;
    }

    $users = User::where('role', 'user')
        ->where('status', 'active')
        ->whereIn('branch_id', $branchIds)
        ->whereIn('department_id', $departmentIds)
        ->withCount([
            'receipts as receipts_not_received_count' => function ($query) {
                $query->where('status', 'not_received');
            }
        ])
        ->with([
            'receipts' => function ($query) {
                $query->latest()->limit(1)->select('id', 'user_id', 'created_at', 'status');
            }
        ])
        ->orderByDesc(
            Receipt::select('created_at')
                ->whereColumn('user_id', 'users.id')
                ->latest()
                ->limit(1)
        )
        ->get(['id', 'name', 'phone', 'zone_id', 'role']);

    return response()->json([
        'message' => 'Users retrieved successfully',
        'users' => $users,
    ], 200);
}

    

public function addAdmin(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|unique:users,phone|max:15',
        'department_id' => 'required|exists:departments,id',
        'branch_id' => 'required|exists:branches,id',

        'permissions' => 'nullable|array',
        'permissions.*' => 'exists:permissions,id',

        'branches' => 'nullable|array',
        'branches.*' => 'exists:branches,id',

        'departments' => 'nullable|array',
        'departments.*' => 'exists:departments,id',
    ], [
        'name.required' => 'الاسم مطلوب',
        'permissions.*.exists' => 'صلاحية غير صالحة',
        'branches.*.exists' => 'فرع غير صالح',
        'departments.*.exists' => 'قسم غير صالح',
    ]);

    // إنشاء المستخدم
    $admin = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'phone' => $validatedData['phone'],
        'department_id' => $validatedData['department_id'],
        'branch_id' => $validatedData['branch_id'],
        'password' => Hash::make('12345678'),
        'role' => 'admin',
    ]);

    // إرفاق الصلاحيات إن وُجدت
    if (!empty($validatedData['permissions'])) {
        $admin->permissions()->sync($validatedData['permissions']);
    }

    // إرفاق الفروع من الجدول الوسيط
    if (!empty($validatedData['branches'])) {
        $admin->branches()->sync($validatedData['branches']);
    }

    // إرفاق الأقسام من الجدول الوسيط
    if (!empty($validatedData['departments'])) {
        $admin->departments()->sync($validatedData['departments']);
    }

    return response()->json([
        'message' => 'تم إضافة المسؤول بنجاح مع الصلاحيات والفروع والأقسام',
        'admin' => $admin,
    ], 200);
}


    // ربط ملف الببلك
    // public function createStorageLink()
    // {
    //     // تنفيذ أمر Artisan
    //     Artisan::call('storage:link');

    //     // التحقق من النتيجة وإعادة الرد
    //     return response()->json([
    //         'message' => 'Storage link created successfully!',
    //         'output' => Artisan::output(),
    //     ]);
    // }


    public function showAllRoles()
    {
        $permissions = Permission::select('id','name','key')->get(); // جلب كل الصلاحيات

        return response()->json([
            'permissions' => $permissions
        ], 200);
    }

    
    public function assignPermission(Request $request, $userId)
    {
        // التأكد من أن البيانات المرسلة صحيحة
        if (!$request->has('permissions') || !is_array($request->permissions)) {
            return response()->json(['error' => 'يجب إرسال قائمة بالصلاحيات'], 400);
        }
    
        $user = User::findOrFail($userId);
    
        // جلب كل الصلاحيات المحددة في الطلب
        $permissions = Permission::whereIn('id', $request->permissions)->pluck('id')->toArray();
    
        // التحقق من وجود صلاحيات صالحة
        if (empty($permissions)) {
            return response()->json(['error' => 'لم يتم العثور على أي صلاحية'], 404);
        }
    
        // مزامنة الصلاحيات: إزالة غير المحددة وإضافة الجديدة
        $user->permissions()->sync($permissions);
        $this->killTheToken($userId);

        return response()->json([
            'message' => 'تم تحديث الصلاحيات بنجاح',
            'assigned_permissions' => $permissions
        ], 200);
    }
    
    


public function removePermission(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    $permission = Permission::where('name', $request->permission)->first();

    if (!$permission) {
        return response()->json(['error' => 'الصلاحية غير موجودة'], 404);
    }

    $user->permissions()->detach($permission->id);

    return response()->json(['message' => 'تمت إزالة الصلاحية بنجاح'], 200);
}

public function checkUserPermissions($userId)
{
    $user = User::findOrFail($userId);

    $permissions = $user->permissions;
    
    $permissions->each(function ($permission) {
        unset($permission->pivot);
        unset($permission->created_at);
        unset($permission->updated_at);
    });
    return response()->json([
        // 'user' => $user->name,
        'permissions' => $permissions,
    ]);
}

public function killTheToken($userId){

    $user = User::find($userId);

    $user->name;
    // auth()->user()->tokens->each(function ($token, $key) {
    //     $token->delete();
    // });

    //  $userToken = Auth::token();;
    $user->tokens->each(function ($token, $key) {
        $token->delete();
    });
    
    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}


public function assignBranches(Request $request, User $user)
{
    $request->validate([
        'branch_ids' => 'required|array',
        'branch_ids.*' => 'exists:branches,id',
    ]);

    $user->branches()->sync($request->branch_ids);

    return response()->json(['message' => 'تم تعيين الفروع بنجاح']);
}

public function assignDepartments(Request $request, User $user)
{
    $request->validate([
        'department_ids' => 'required|array',
        'department_ids.*' => 'exists:departments,id',
    ]);

    $user->departments()->sync($request->department_ids);

    return response()->json(['message' => 'تم تعيين الأقسام بنجاح']);
}

public function getTheToken($userId){

    $user = User::find($userId);

    $user->name;
    // auth()->user()->tokens->each(function ($token, $key) {
    //     $token->delete();
    // });

    //  $userToken = Auth::token();;
   
    return  $user->tokens;
}

}
