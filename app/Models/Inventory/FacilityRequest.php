<?php

namespace App\Models\Inventory;

use App\Models\Core\Department;
use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityRequest extends Model
{
    use HasFactory;

    protected $table = 'facility_requests';

    protected $fillable = [
        'employee_id',
        'department_id',
        'request_type',
        'title',
        'description',
        'quantity',
        'priority',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
