<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->get();

        // If database has no users, return sample initial users structure
        if ($users->isEmpty()) {
            $initialUsers = [
                [
                    "id" => "usr_1",
                    "name" => "Alex Morgan",
                    "email" => "alex.morgan@company.com",
                    "role" => "Super Admin",
                    "department" => "Engineering",
                    "status" => "Active",
                    "avatar" => "https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80",
                    "phone" => "+1 (555) 234-5678",
                    "createdAt" => "2024-01-15",
                    "lastActive" => "2 mins ago"
                ],
                [
                    "id" => "usr_2",
                    "name" => "Sarah Chen",
                    "email" => "sarah.chen@company.com",
                    "role" => "Admin",
                    "department" => "Product",
                    "status" => "Active",
                    "avatar" => "https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=250&q=80",
                    "phone" => "+1 (555) 876-5432",
                    "createdAt" => "2024-02-01",
                    "lastActive" => "15 mins ago"
                ]
            ];

            return response()->json([
                'status' => true,
                'message' => 'Sample users retrieved successfully',
                'data' => $initialUsers
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users
        ], 200);
    }

    /**
     * Store a newly created user.
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
     * Update specified user.
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
     * Remove specified user.
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
