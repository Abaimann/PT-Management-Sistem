<?php

namespace App\Models\HRIS;

use App\Models\Attendance\AttendanceRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'code',
        'name',
        'start_time',
        'end_time',
        'late_tolerance_minutes',
        'is_overnight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
            'late_tolerance_minutes' => 'integer',
        ];
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'shift_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'shift_id');
    }
}
