<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Task_assigment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Taskassigmentseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();

        // Assign each task to 1-3 random users
        foreach ($tasks as $task) {
            $numAssignments = rand(1, 3);
            $assignedUsers = $users->random(min($numAssignments, $users->count()));
            
            foreach ($assignedUsers as $user) {
                $assignment = Task_assigment::create([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'assigned_at' => now()->subDays(rand(1, 10)),
                ]);

                // Mark some assignments as completed based on task status
                if ($task->status === 'completed' || ($task->status === 'in_progress' && rand(0, 1))) {
                    $assignment->completed_at = now()->subDays(rand(0, 5));
                    $assignment->save();
                }
            }
        }
    }
}
