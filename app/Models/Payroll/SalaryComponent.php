<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'salary_components';

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_taxable',
        'is_fixed',
        'default_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_fixed' => 'boolean',
            'is_active' => 'boolean',
            'default_amount' => 'decimal:2',
        ];
    }

    public function employeeComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class, 'salary_component_id');
    }

    public function payslipItems(): HasMany
    {
        return $this->hasMany(PayslipItem::class, 'salary_component_id');
    }
}
