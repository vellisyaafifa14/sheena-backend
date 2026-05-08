<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

if (!$admin || !Hash::check($request->password, $admin->password)) {
    return response()->json([
        'message' => 'Email atau password salah'
    ], 401);
}

$token = $admin->createToken('admin-token')->plainTextToken;

return response()->json([
    'message' => 'Login berhasil',
    'token' => $token,
    'data' => [
        'id_admin' => $admin->id_admin,
        'name' => $admin->name,
        'email' => $admin->email,
    ]
], 200);


        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'data' => [
                'id_admin' => $admin->id_admin,
                'name' => $admin->name,
                'email' => $admin->email,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ], 200);
    }
}