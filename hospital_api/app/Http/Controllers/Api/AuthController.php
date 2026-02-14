<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ===============================
    // USER REGISTRATION
    // ===============================
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:patient,doctor,admin',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'],
        ]);

        return response()->json([
            'message' => 'Registration Successful',
            'user' => $user
        ]);
    }

    // ===============================
    // USER LOGIN
    // ===============================
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:patient,doctor,admin',
        ]);

        // Find user with matching role
        $user = User::where('email', $fields['email'])
                    ->where('role', $fields['role'])
                    ->first();

        // If user not found or password mismatch
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create Token (VERY IMPORTANT for React)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
}
