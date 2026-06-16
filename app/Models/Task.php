<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => TaskStatus::class,
            'priority'     => TaskPriority::class,
            'due_date'     => 'date',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->isDirty('status')) {
                if ($task->status === TaskStatus::Done) {
                    $task->completed_at ??= Carbon::now();
                } else {
                    $task->completed_at = null;
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
