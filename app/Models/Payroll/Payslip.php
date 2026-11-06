<?php

namespace App\Models\Payroll;

use App\Models\HRIS\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    protected $table = 'payslips';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'payslip_number',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'payslip_id');
    }

    public function earnings(): Collection
    {
        return $this->items->where('type', 'EARNING');
    }

    public function deductions(): Collection
    {
        return $this->items->where('type', 'DEDUCTION');
    }
}
