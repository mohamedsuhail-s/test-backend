<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return an API token.
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string',
            'department' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'User',
            'department' => $validated['department'] ?? 'General',
            'status' => 'Active',
            'phone' => $validated['phone'] ?? null,
            'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($validated['name']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Authenticate user and return an API token.
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials. Please check your email and password.'
            ], 401);
        }

        // Revoke all previous tokens if desired, or just issue a new one
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Get the authenticated User profile.
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Revoke current API token (Logout).
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
        ], 200);
    }
}
