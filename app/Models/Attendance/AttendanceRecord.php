<?php

namespace App\Models\Attendance;

use App\Models\HRIS\Employee;
use App\Models\HRIS\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_selfie',
        'clock_out_selfie',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_distance_meters',
        'clock_out_distance_meters',
        'status',
        'late_minutes',
        'is_locked',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_locked' => 'boolean',
            'late_minutes' => 'integer',
            'clock_in_latitude' => 'float',
            'clock_in_longitude' => 'float',
            'clock_out_latitude' => 'float',
            'clock_out_longitude' => 'float',
            'clock_in_distance_meters' => 'float',
            'clock_out_distance_meters' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class, 'attendance_record_id');
    }
}
