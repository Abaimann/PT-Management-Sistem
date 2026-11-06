<?php

namespace App\Models\Inventory;

use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_id',
        'employee_id',
        'assignment_date',
        'return_date',
        'condition_on_assignment',
        'condition_on_return',
        'status',
        'handover_document_path',
        'clearance_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assignment_date' => 'date',
            'return_date' => 'date',
            'clearance_status' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
