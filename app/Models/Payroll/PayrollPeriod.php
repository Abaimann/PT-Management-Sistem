<?php

namespace App\Models\Payroll;

use App\Models\Core\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $table = 'payroll_periods';

    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'cutoff_start_date',
        'cutoff_end_date',
        'cutoff_date',
        'payment_date',
        'status',
        'closed_at',
        'closed_by_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'cutoff_start_date' => 'date',
            'cutoff_end_date' => 'date',
            'cutoff_date' => 'date',
            'payment_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class, 'payroll_period_id');
    }

    public function latestPayrollRun(): HasOne
    {
        return $this->hasOne(PayrollRun::class, 'payroll_period_id')->latestOfMany();
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    /**
     * Get effective cutoff start date.
     */
    public function getEffectiveCutoffStartDateAttribute(): Carbon
    {
        return $this->cutoff_start_date ?? $this->start_date;
    }

    /**
     * Get effective cutoff end date.
     */
    public function getEffectiveCutoffEndDateAttribute(): Carbon
    {
        return $this->cutoff_end_date ?? $this->cutoff_date ?? $this->end_date;
    }
}
