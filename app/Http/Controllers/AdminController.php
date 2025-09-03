<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//use App\Http\Controllers\Controller;


class AdminController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'security_key' => 'required|string',
        ]);

        // if ($request->security_key !== env('ADMIN_SECURITY_KEY')) {
        //     return response()->json(['error' => 'Invalid security key'], 403);
        // }

        if (Admin::exists()) {
            return response()->json(['error' => 'An admin account already exists.'], 403);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // تأكد من تشفير كلمة المرور
            'role' => 'admin',
        ]);

        // إنشاء التوكن
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully!',
            'token' => $token,
            'admin' => $admin,
        ], 201);
    }



    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'security_key' => 'required|string',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();

        if (!$admin) {
            return response()->json(['error' => 'The email address is not registered.'], 404);
        }

        if (!Hash::check($validated['password'],$admin->password)) {

            return response()->json(['error' => 'Incorrect password.'], 401);
        }


        // if ($validated['security_key'] !== env('ADMIN_SECURITY_KEY')) {
        //     return response()->json(['error' => 'Invalid security key.'], 403);
        // }

        // إنشاء توكن جديد
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Welcome back!',
            'token' => $token,
            'admin' => $admin,
        ], 200);
    }



    public function logout(Request $request)
    {
        // إبطال جميع التوكنات
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }



    public function dashboard(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to the admin dashboard',
            'admin' => $request->user(), // الحصول على المسؤول المصادق
        ], 200);
    }

}
