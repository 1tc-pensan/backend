<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\Task_assigment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can view their tasks
     */
    public function test_authenticated_user_can_view_their_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Task_assigment::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'assigned_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    '*' => ['id', 'title', 'status'],
                ],
            ]);
    }

    /**
     * Test admin can view all tasks
     */
    public function test_admin_can_view_all_tasks(): void
    {
        $admin = User::factory()->admin()->create();
        Task::factory()->count(3)->create();

        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tasks' => [
                    '*' => ['id', 'title', 'description', 'priority', 'status'],
                ],
            ]);
    }

    /**
     * Test non-admin cannot view all tasks
     */
    public function test_non_admin_cannot_view_all_tasks(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/tasks');

        $response->assertStatus(403);
    }

    /**
     * Test admin can create task
     */
    public function test_admin_can_create_task(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'priority' => 'high',
            'due_date' => '2026-02-20',
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Task created successfully',
                'task' => [
                    'title' => 'New Task',
                    'priority' => 'high',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
        ]);
    }

    /**
     * Test non-admin cannot create task
     */
    public function test_non_admin_cannot_create_task(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/tasks', [
            'title' => 'New Task',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can update task
     */
    public function test_admin_can_update_task(): void
    {
        $admin = User::factory()->admin()->create();
        $task = Task::factory()->create(['title' => 'Old Title']);
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task updated successfully',
                'task' => [
                    'title' => 'Updated Title',
                    'status' => 'in_progress',
                ],
            ]);
    }

    /**
     * Test admin can delete task
     */
    public function test_admin_can_delete_task(): void
    {
        $admin = User::factory()->admin()->create();
        $task = Task::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    /**
     * Test user can update their own task status
     */
    public function test_user_can_update_their_task_status(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();
        Task_assigment::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'assigned_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/tasks/{$task->id}/status", [
            'completed_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task status updated successfully']);

        $this->assertDatabaseHas('task_assigments', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
    }

    /**
     * Test user cannot update status of unassigned task
     */
    public function test_user_cannot_update_status_of_unassigned_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/tasks/{$task->id}/status", [
            'completed_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(404);
    }
}
