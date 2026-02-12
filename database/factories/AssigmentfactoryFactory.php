<?php

namespace Database\Factories;

use App\Models\Task_assigment;
use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task_assigment>
 */
class AssigmentfactoryFactory extends Factory
{
    protected $model = Task_assigment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'assigned_at' => fake()->dateTimeBetween('-10 days', 'now'),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the task assignment is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $assignedAt = $attributes['assigned_at'] ?? now()->subDays(5);
            return [
                'completed_at' => fake()->dateTimeBetween($assignedAt, 'now'),
            ];
        });
    }
}
