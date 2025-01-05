<?php

namespace App\Http\Controllers;

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
        $users = User::all(['id', 'name', 'email', 'phone', 'address', 'department_id', 'branch_id', 'role']);

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
            'department' => $validatedData['department'],
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
}
