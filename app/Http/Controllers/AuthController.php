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
            'department' => 'required|string|max:255',
            'address' => 'required|string|max:255',
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
            'department' => $request->department,
            'address' => $request->address,
            'status' => 'active', // الحالة الافتراضية
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user', // تعيين الدور أو القيمة الافتراضية

        ]);

                // إنشاء التوكن للمستخدم
                $token = $user->createToken('auth_token')->plainTextToken;
                
        return response()->json(['message' => 'User registered successfully!', 'user' => $user ,'token' => $token,], 201);
    }

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    return response()->json([
        'message' => 'Invalid email or password',
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

    return response()->json(['message' => 'User successfully signed out']);
}

}
