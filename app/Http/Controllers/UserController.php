<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        $users = User::all(['id', 'name', 'email', 'phone', 'address', 'department', 'role']);

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found with the "user" role',
                'users' => $users,
            ], 200);
        }

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users,
        ], 200);
    }
}
