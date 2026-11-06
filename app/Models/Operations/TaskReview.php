<?php

namespace App\Models\Operations;

use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReview extends Model
{
    use HasFactory;

    protected $table = 'task_reviews';

    protected $fillable = [
        'task_id',
        'reviewer_id',
        'score',
        'feedback',
        'revision_notes',
        'decision',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }
}
