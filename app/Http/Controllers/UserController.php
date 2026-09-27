<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users from database.
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }

    /**
     * Store a newly created user in database.
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'nullable|string',
            'department' => 'nullable|string',
            'status' => 'nullable|string',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|string',
        ]);

        $validated['password'] = bcrypt('password123'); // Default password

        $user = User::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display specified user.
     * GET /api/users/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Update specified user in database.
     * PUT/PATCH /api/users/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'role' => 'nullable|string',
            'department' => 'nullable|string',
            'status' => 'nullable|string',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    /**
     * Remove specified user from database.
     * DELETE /api/users/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ], 200);
    }
}
