<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task_assigment;
use Illuminate\Http\Request;

class TaskAssignmentController extends Controller
{
    /**
     * Get all task assignments (Admin)
     */
    public function index()
    {
        $assignments = Task_assigment::with(['user', 'task'])->get();

        return response()->json([
            'assignments' => $assignments,
        ]);
    }

    /**
     * Create a new task assignment (Admin)
     */
    public function store(Request $request)
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
     * Show a specific task assignment (Admin)
     */
    public function show($id)
    {
        $assignment = Task_assigment::with(['user', 'task'])->findOrFail($id);

        return response()->json([
            'assignment' => $assignment,
        ]);
    }

    /**
     * Update a task assignment (Admin)
     */
    public function update(Request $request, $id)
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
     * Delete a task assignment (Admin)
     */
    public function destroy($id)
    {
        $assignment = Task_assigment::findOrFail($id);
        $assignment->delete();

        return response()->json([
            'message' => 'Assignment deleted successfully',
        ]);
    }

    /**
     * Get all assignments for a specific task (Admin)
     */
    public function byTask($taskId)
    {
        $assignments = Task_assigment::where('task_id', $taskId)
            ->with(['user', 'task'])
            ->get();

        return response()->json([
            'assignments' => $assignments,
        ]);
    }

    /**
     * Get all assignments for a specific user (Admin)
     */
    public function byUser($userId)
    {
        $assignments = Task_assigment::where('user_id', $userId)
            ->with(['user', 'task'])
            ->get();

        return response()->json([
            'assignments' => $assignments,
        ]);
    }
}
