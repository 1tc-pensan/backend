<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can view their profile
     */
    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'name' => 'Test User',
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test unauthenticated user cannot view profile
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /**
     * Test admin can view all users
     */
    public function test_admin_can_view_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    '*' => ['id', 'name', 'email', 'is_admin'],
                ],
            ]);
    }

    /**
     * Test non-admin cannot view all users
     */
    public function test_non_admin_cannot_view_all_users(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden. Admin access required.']);
    }

    /**
     * Test admin can create a new user
     */
    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Jelszo12',
            'department' => 'IT',
            'is_admin' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
                'user' => [
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    /**
     * Test non-admin cannot create user
     */
    public function test_non_admin_cannot_create_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('user-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Jelszo12',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test admin can update user
     */
    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name']);
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully',
                'user' => [
                    'name' => 'Updated Name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test admin can delete user
     */
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test admin cannot delete themselves
     */
    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/admin/users/{$admin->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'You cannot delete your own account']);
    }

    /**
     * Test unauthenticated user cannot access admin endpoints
     */
    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }
}
