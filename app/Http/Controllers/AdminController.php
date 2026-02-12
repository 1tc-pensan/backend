<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Task_assigment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ===== USER MANAGEMENT =====

    /**
     * Get all users
     */
    public function users()
    {
        $users = User::withTrashed()->with('tasks')->get();

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
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
     * Update a user
     */
    public function updateUser(Request $request, $id)
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
     * Soft delete a user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Restore a soft deleted user
     */
    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $user,
        ]);
    }

    // ===== TASK ASSIGNMENT MANAGEMENT =====

    /**
     * Get all task assignments
     */
    public function assignments()
    {
        $assignments = Task_assigment::with(['user', 'task'])->get();

        return response()->json([
            'assignments' => $assignments,
        ]);
    }

    /**
     * Assign a task to a user
     */
    public function assignTask(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task_id' => 'required|exists:tasks,id',
            'assigned_at' => 'nullable|date',
        ]);

        $assignment = Task_assigment::create([
            'user_id' => $validated['user_id'],
            'task_id' => $validated['task_id'],
            'assigned_at' => $validated['assigned_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Task assigned successfully',
            'assignment' => $assignment->load(['user', 'task']),
        ], 201);
    }

    /**
     * Update task assignment
     */
    public function updateAssignment(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'task_id' => 'sometimes|required|exists:tasks,id',
            'assigned_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $assignment = Task_assigment::findOrFail($id);
        $assignment->update($validated);

        return response()->json([
            'message' => 'Assignment updated successfully',
            'assignment' => $assignment->load(['user', 'task']),
        ]);
    }

    /**
     * Delete task assignment
     */
    public function deleteAssignment($id)
    {
        $assignment = Task_assigment::findOrFail($id);
        $assignment->delete();

        return response()->json([
            'message' => 'Assignment deleted successfully',
        ]);
    }

    /**
     * Admin dashboard view
     */
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome to Admin Dashboard',
            'stats' => [
                'total_users' => User::count(),
                'total_tasks' => Task::count(),
                'pending_tasks' => Task::where('status', 'pending')->count(),
                'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'total_assignments' => Task_assigment::count(),
            ],
        ]);
    }
}
