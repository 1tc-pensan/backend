<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Task_assigment;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // ===== USER METHODS =====

    /**
     * Get current user's tasks
     */
    public function myTasks(Request $request)
    {
        $user = $request->user();
        
        $tasks = $user->tasks()
            ->withPivot('id', 'assigned_at', 'completed_at')
            ->with('assignments')
            ->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Update task status (user marks their assignment as completed)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'completed_at' => 'nullable|date',
        ]);

        // Find the task assignment for this user and task
        $assignment = Task_assigment::where('task_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $assignment->completed_at = $validated['completed_at'] ?? now();
        $assignment->save();

        return response()->json([
            'message' => 'Task status updated successfully',
            'assignment' => $assignment->load('task'),
        ]);
    }

    // ===== ADMIN METHODS =====

    /**
     * Display a listing of tasks (Admin)
     */
    public function index()
    {
        $tasks = Task::with(['assignments.user', 'users'])->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created task (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task = Task::create($validated);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified task (Admin)
     */
    public function show($id)
    {
        $task = Task::with(['assignments.user', 'users'])->findOrFail($id);

        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Update the specified task (Admin)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
        ]);

        $task = Task::findOrFail($id);
        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Remove the specified task - soft delete (Admin)
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}
