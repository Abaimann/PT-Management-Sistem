<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $table = 'maintenance_records';

    protected $fillable = [
        'asset_id',
        'maintenance_type',
        'schedule_date',
        'completion_date',
        'cost',
        'vendor_name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'schedule_date' => 'date',
            'completion_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
