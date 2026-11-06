<?php

namespace App\Models\Operations;

use App\Models\Core\Department;
use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTarget extends Model
{
    use HasFactory;

    protected $table = 'work_targets';

    protected $fillable = [
        'department_id',
        'employee_id',
        'title',
        'description',
        'month',
        'year',
        'target_metric',
        'target_value',
        'achieved_value',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'target_value' => 'decimal:2',
            'achieved_value' => 'decimal:2',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'work_target_id');
    }
}
