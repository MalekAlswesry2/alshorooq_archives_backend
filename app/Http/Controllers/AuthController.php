<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'department_id' => 'required|exists:departments,id',
            'branch_id' => 'required|exists:branches,id',
            'zone_id' => 'required|exists:zones,id',
            // 'status' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:master,admin,user', // التحقق من الدور

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'department_id' => $request->department_id,
            'branch_id' => $request->branch_id,
            'zone_id' => $request->zone_id,
            'status' => 'active', // الحالة الافتراضية
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user', // تعيين الدور أو القيمة الافتراضية

        ]);

                // إنشاء التوكن للمستخدم
                $token = $user->createToken('auth_token')->plainTextToken;

                
        return response()->json(['message' => 'تم التسجيل بنجاح', 'user' => $user ,'token' => $token,], 200);
    }

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $user->load('department:id,name','branch:id,name','permissions:id,name,key');
        $token = $user->createToken('auth_token')->plainTextToken;
        $user['balance'] = (double)$user->balance;


        $user->permissions->each(function ($permission) {
            unset($permission->pivot);
        });
        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    return response()->json([
        'message' => 'بيانات الدخول غير صحيحة',
    ], 401);
}

public function profile()
{
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user(); // الحصول على المستخدم المصادق عليه
    $user->load('department:id,name','branch:id,name','zone:id,name','permissions:id,name,key');
    $user->permissions->each(function ($permission) {
        unset($permission->pivot);
    });
    $user['balance'] = (double)$user->balance;

    return response()->json([
        'message' => 'User profile retrieved successfully',
        'user' => $user,
    ], 200);
}
public function logout(Request $request) {
    if (!auth()->user()) {
        return response()->json(['error' => 'already signed out'], 404);
    }

    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'تم تسجيل الخروج بنجاح'], 200);
}
public function updateProfile(Request $request)
{
    // التحقق من أن المستخدم مصادق عليه
    if (!auth()->check()) {
        return response()->json([
            'error' => true,
            'message' => 'You Are Not Authenticated',
        ], 401);
    }

    $user = auth()->user(); // الحصول على المستخدم المصادق عليه

    // التحقق من البيانات المُرسلة
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|unique:users,phone,' . $user->id, // 
        'address' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // تحديث بيانات المستخدم
    $user->update([
        'name' => $request->name ?? $user->name,
        'phone' => $request->phone ?? $user->phone,
        'address' => $request->address ?? $user->address,
    ]);

    return response()->json([
        'message' => 'تم تحديث البيانات بنجاح',
        'user' => $user,
    ], 200);
}


}
