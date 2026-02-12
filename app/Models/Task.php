<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(Task_assigment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_assigments')
                    ->withPivot('assigned_at', 'completed_at')
                    ->withTimestamps()
                    ->withTrashed();
    }
}
