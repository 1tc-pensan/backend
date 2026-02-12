<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 tasks with different statuses and priorities
        
        // 3 pending tasks with different priorities
        Task::factory()->pending()->lowPriority()->create();
        Task::factory()->pending()->mediumPriority()->create();
        Task::factory()->pending()->highPriority()->create();
        
        // 4 in progress tasks with different priorities
        Task::factory()->inProgress()->lowPriority()->create();
        Task::factory()->inProgress()->mediumPriority()->create();
        Task::factory()->inProgress()->mediumPriority()->create();
        Task::factory()->inProgress()->highPriority()->create();
        
        // 3 completed tasks with different priorities
        Task::factory()->completed()->lowPriority()->create();
        Task::factory()->completed()->mediumPriority()->create();
        Task::factory()->completed()->highPriority()->create();
    }
}
