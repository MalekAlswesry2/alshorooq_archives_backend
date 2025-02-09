<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class UserController extends Controller
{
    /**
     * عرض جميع المستخدمين الذين لديهم دور user.
     */
    public function getUsersWithUserRole()
    { 
        if (!auth()->check()) {
            return response()->json([
                'error' => true,
                'message' => 'You Are Not Authenticated',
            ], 401);
        }

        // $users = User::where('role', 'user')->get(['id', 'name', 'email', 'phone', 'address', 'department']);
        $users = User::all(['id', 'name', 'email', 'phone', 'zone_id', 'department_id', 'branch_id', 'role'])
        ->load('permissions:id,name');
        $users = User::with('permissions:id,name')->get(['id', 'name', 'email', 'phone', 'zone_id', 'department_id', 'branch_id', 'role']);
        $users->each(function ($user) {
            $user->permissions->makeHidden('pivot');
        });
    
        
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found with the "user" role',
                'users' => $users,
            ], 200);
        }



        // $users = $users->map(function ($user) {
         
    
        //     $user['balance'] = (double)$user->balance;

        //     return $user;
        // });

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users,
        ], 200);
    }

    public function addAdmin(Request $request)
    {
        // التحقق من البيانات المدخلة
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:15',
            // 'department_id' => 'required|string|max:255',
            // 'bramch_id' => 'required|string|max:255',
        ]);

        // إنشاء المسؤول الجديد
        $admin = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            // 'department' => $validatedData['department'],
            'password' => Hash::make('12345678'), // كلمة مرور افتراضية يمكن تغييرها لاحقًا
            'role' => 'admin', // الدور يحدد كـ admin تلقائيًا
        ]);

        return response()->json([
            'message' => 'Admin created successfully!',
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


}
