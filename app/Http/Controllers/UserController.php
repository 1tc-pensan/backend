<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task_assigment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get current user's profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // ===== ADMIN: USER MANAGEMENT =====

    /**
     * Get all users (Admin)
     */
    public function index()
    {
        $users = User::withTrashed()->with('tasks')->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Create a new user (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'department' => $validated['department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Show a specific user (Admin)
     */
    public function show($id)
    {
        $user = User::withTrashed()->with('tasks')->findOrFail($id);

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update a user (Admin)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'is_admin' => 'boolean',
        ]);

        $user = User::findOrFail($id);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Soft delete a user (Admin)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
