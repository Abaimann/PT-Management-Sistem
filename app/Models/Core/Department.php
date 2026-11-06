<?php

namespace App\Models\Core;

use App\Models\Finance\Budget;
use App\Models\HRIS\Employee;
use App\Models\Operations\WorkTarget;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'code',
        'name',
        'cost_center',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'department_id');
    }

    public function workTargets(): HasMany
    {
        return $this->hasMany(WorkTarget::class, 'department_id');
    }
}
