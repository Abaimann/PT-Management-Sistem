<?php

namespace App\Services\Payroll;

use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\Payroll\EmployeeSalaryComponent;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\PayslipItem;
use App\Models\Payroll\SalaryComponent;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayrollService
{
    /**
     * Ensure default salary components exist in master table.
     */
    public function ensureMasterSalaryComponents(): void
    {
        $components = [
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 5000000.00,
                'is_active' => true,
            ],
            [
                'code' => 'MEAL_ALLOWANCE',
                'name' => 'Tunjangan Uang Makan',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 800000.00,
                'is_active' => true,
            ],
            [
                'code' => 'TRANSPORT_ALLOWANCE',
                'name' => 'Tunjangan Transportasi',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 600000.00,
                'is_active' => true,
            ],
            [
                'code' => 'POSITION_ALLOWANCE',
                'name' => 'Tunjangan Jabatan',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 1500000.00,
                'is_active' => true,
            ],
            [
                'code' => 'ATTENDANCE_ALLOWANCE',
                'name' => 'Tunjangan Kehadiran',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => false,
                'default_amount' => 25000.00, // per valid day
                'is_active' => true,
            ],
            [
                'code' => 'OVERTIME_PAY',
                'name' => 'Uang Lembur',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => false,
                'default_amount' => 25000.00, // per hour
                'is_active' => true,
            ],
            [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Keterlambatan',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 1000.00, // per late minute
                'is_active' => true,
            ],
            [
                'code' => 'ABSENCE_DEDUCTION',
                'name' => 'Potongan Ketidakhadiran (Mangkir)',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 150000.00, // per absent day
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_DED',
                'name' => 'Potongan Pinjaman / Kasbon Karyawan',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_TK_DED',
                'name' => 'Potongan BPJS Ketenagakerjaan (JHT & JP)',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => true,
                'default_amount' => 150000.00,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_KES_DED',
                'name' => 'Potongan BPJS Kesehatan',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => true,
                'default_amount' => 100000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PPH21_DED',
                'name' => 'Potongan Pajak PPh 21 Terutang',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($components as $comp) {
            SalaryComponent::firstOrCreate(
                ['code' => $comp['code']],
                $comp
            );
        }
    }

    /**
     * Calculate draft payroll batch for a period.
     */
    public function calculatePeriod(PayrollPeriod $period, ?User $processedBy = null): PayrollRun
    {
        if ($period->status === 'CLOSED') {
            throw new DomainException('Periode payroll sudah berstatus CLOSED dan tidak dapat dikalkulasi ulang.');
        }

        $this->ensureMasterSalaryComponents();

        return DB::transaction(function () use ($period, $processedBy) {
            // Delete prior runs and payslips for this period if re-calculating
            $priorRuns = PayrollRun::where('payroll_period_id', $period->id)->get();
            foreach ($priorRuns as $priorRun) {
                $priorRun->payslips()->delete();
                $priorRun->delete();
            }

            $cutoffStart = Carbon::parse($period->effective_cutoff_start_date)->startOfDay();
            $cutoffEnd = Carbon::parse($period->effective_cutoff_end_date)->endOfDay();

            // Create Payroll Run
            $runNumber = 'PRUN-' . $period->code . '-' . date('YmdHis');
            $payrollRun = PayrollRun::create([
                'payroll_period_id' => $period->id,
                'run_number' => $runNumber,
                'run_date' => now()->toDateString(),
                'total_employees' => 0,
                'total_gross_pay' => 0,
                'total_deductions' => 0,
                'total_net_pay' => 0,
                'status' => 'PROCESSING',
                'processed_by' => $processedBy?->id,
            ]);

            $masterComponents = SalaryComponent::where('is_active', true)->get()->keyBy('code');

            $employees = Employee::with(['salaryComponents.salaryComponent', 'department', 'position'])
                ->where('is_active', true)
                ->get();

            $totalEmployees = 0;
            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;

            foreach ($employees as $employee) {
                // Fetch attendance records in cutoff range
                $attendanceRecords = AttendanceRecord::with('shift')
                    ->where('employee_id', $employee->id)
                    ->whereBetween('date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
                    ->get();

                $validPresentDays = $attendanceRecords->filter(function ($rec) {
                    return in_array($rec->status, ['ON_TIME', 'LATE', 'OUT_OF_RADIUS']) || $rec->clock_in !== null;
                })->count();

                $lateMinutes = (int) $attendanceRecords->sum('late_minutes');

                $absentDays = $attendanceRecords->filter(function ($rec) {
                    return $rec->status === 'ABSENT';
                })->count();

                // Calculate overtime hours
                $overtimeHours = 0.0;
                foreach ($attendanceRecords as $rec) {
                    if ($rec->clock_out && $rec->shift && $rec->shift->end_time) {
                        $shiftEnd = Carbon::parse($rec->date->toDateString() . ' ' . $rec->shift->end_time);
                        $clockOut = Carbon::parse($rec->date->toDateString() . ' ' . $rec->clock_out);
                        if ($clockOut->gt($shiftEnd)) {
                            $diffHours = $clockOut->diffInMinutes($shiftEnd) / 60;
                            if ($diffHours >= 0.5) {
                                $overtimeHours += round($diffHours, 1);
                            }
                        }
                    }
                }

                // Map employee specific components
                $empComponents = $employee->salaryComponents->where('is_active', true)->keyBy(function ($item) {
                    return $item->salaryComponent->code;
                });

                $itemsToCreate = [];

                // 1. Basic Salary
                $basicComponent = $masterComponents->get('BASIC_SALARY');
                $basicAmount = $empComponents->has('BASIC_SALARY')
                    ? (float) $empComponents->get('BASIC_SALARY')->amount
                    : (float) ($basicComponent?->default_amount ?? 5000000.00);

                if ($basicComponent) {
                    $itemsToCreate[] = [
                        'salary_component_id' => $basicComponent->id,
                        'name' => $basicComponent->name,
                        'type' => 'EARNING',
                        'amount' => $basicAmount,
                    ];
                }

                // 2. Fixed Allowances (Meal, Transport, Position)
                foreach (['MEAL_ALLOWANCE', 'TRANSPORT_ALLOWANCE', 'POSITION_ALLOWANCE'] as $code) {
                    $comp = $masterComponents->get($code);
                    if ($comp) {
                        $amount = $empComponents->has($code)
                            ? (float) $empComponents->get($code)->amount
                            : (float) $comp->default_amount;

                        if ($amount > 0) {
                            $itemsToCreate[] = [
                                'salary_component_id' => $comp->id,
                                'name' => $comp->name,
                                'type' => 'EARNING',
                                'amount' => $amount,
                            ];
                        }
                    }
                }

                // 3. Attendance Allowance (based on valid present days)
                $attComponent = $masterComponents->get('ATTENDANCE_ALLOWANCE');
                if ($attComponent && $validPresentDays > 0) {
                    $ratePerDay = $empComponents->has('ATTENDANCE_ALLOWANCE')
                        ? (float) $empComponents->get('ATTENDANCE_ALLOWANCE')->amount
                        : (float) $attComponent->default_amount;
                    $attAmount = round($ratePerDay * $validPresentDays, 2);

                    if ($attAmount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $attComponent->id,
                            'name' => $attComponent->name . " ({$validPresentDays} hari hadir)",
                            'type' => 'EARNING',
                            'amount' => $attAmount,
                        ];
                    }
                }

                // 4. Overtime Pay
                $otComponent = $masterComponents->get('OVERTIME_PAY');
                if ($otComponent) {
                    $otRatePerHour = $empComponents->has('OVERTIME_PAY')
                        ? (float) $empComponents->get('OVERTIME_PAY')->amount
                        : (float) $otComponent->default_amount;
                    $otAmount = round($overtimeHours * $otRatePerHour, 2);

                    if ($otAmount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $otComponent->id,
                            'name' => $otComponent->name . " ({$overtimeHours} jam)",
                            'type' => 'EARNING',
                            'amount' => $otAmount,
                        ];
                    }
                }

                // 5. Late Deduction
                $lateComponent = $masterComponents->get('LATE_DEDUCTION');
                if ($lateComponent && $lateMinutes > 0) {
                    $ratePerMinute = $empComponents->has('LATE_DEDUCTION')
                        ? (float) $empComponents->get('LATE_DEDUCTION')->amount
                        : (float) $lateComponent->default_amount;
                    $lateAmount = round($lateMinutes * $ratePerMinute, 2);

                    if ($lateAmount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $lateComponent->id,
                            'name' => $lateComponent->name . " ({$lateMinutes} menit)",
                            'type' => 'DEDUCTION',
                            'amount' => $lateAmount,
                        ];
                    }
                }

                // 6. Absence Deduction (unexcused absence)
                $absComponent = $masterComponents->get('ABSENCE_DEDUCTION');
                if ($absComponent && $absentDays > 0) {
                    $ratePerAbsent = $empComponents->has('ABSENCE_DEDUCTION')
                        ? (float) $empComponents->get('ABSENCE_DEDUCTION')->amount
                        : (float) $absComponent->default_amount;
                    $absAmount = round($absentDays * $ratePerAbsent, 2);

                    if ($absAmount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $absComponent->id,
                            'name' => $absComponent->name . " ({$absentDays} hari mangkir)",
                            'type' => 'DEDUCTION',
                            'amount' => $absAmount,
                        ];
                    }
                }

                // 7. Kasbon / Loan Deduction
                $loanComponent = $masterComponents->get('LOAN_DED');
                if ($loanComponent && $empComponents->has('LOAN_DED')) {
                    $loanAmount = (float) $empComponents->get('LOAN_DED')->amount;
                    if ($loanAmount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $loanComponent->id,
                            'name' => $loanComponent->name,
                            'type' => 'DEDUCTION',
                            'amount' => $loanAmount,
                        ];
                    }
                }

                // 8. Fixed BPJS & Tax Deductions
                foreach (['BPJS_TK_DED', 'BPJS_KES_DED', 'PPH21_DED'] as $code) {
                    $comp = $masterComponents->get($code);
                    if ($comp) {
                        $amount = $empComponents->has($code)
                            ? (float) $empComponents->get($code)->amount
                            : (float) $comp->default_amount;

                        if ($amount > 0) {
                            $itemsToCreate[] = [
                                'salary_component_id' => $comp->id,
                                'name' => $comp->name,
                                'type' => 'DEDUCTION',
                                'amount' => $amount,
                            ];
                        }
                    }
                }

                // Include any other custom components configured on employee
                foreach ($empComponents as $code => $empComp) {
                    if (in_array($code, [
                        'BASIC_SALARY', 'MEAL_ALLOWANCE', 'TRANSPORT_ALLOWANCE', 'POSITION_ALLOWANCE',
                        'ATTENDANCE_ALLOWANCE', 'OVERTIME_PAY', 'LATE_DEDUCTION', 'ABSENCE_DEDUCTION',
                        'LOAN_DED', 'BPJS_TK_DED', 'BPJS_KES_DED', 'PPH21_DED'
                    ])) {
                        continue;
                    }

                    $customComp = $empComp->salaryComponent;
                    if ($customComp && (float) $empComp->amount > 0) {
                        $itemsToCreate[] = [
                            'salary_component_id' => $customComp->id,
                            'name' => $customComp->name,
                            'type' => $customComp->type,
                            'amount' => (float) $empComp->amount,
                        ];
                    }
                }

                // Sum totals
                $empGross = 0;
                $empDeductions = 0;
                foreach ($itemsToCreate as $item) {
                    if ($item['type'] === 'EARNING') {
                        $empGross += $item['amount'];
                    } else {
                        $empDeductions += $item['amount'];
                    }
                }
                $empNet = max(0, $empGross - $empDeductions);

                // Create Payslip
                $payslipNumber = 'SLIP/' . $period->code . '/' . $employee->nik;
                $payslip = Payslip::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $employee->id,
                    'payslip_number' => $payslipNumber,
                    'gross_salary' => $empGross,
                    'total_deductions' => $empDeductions,
                    'net_salary' => $empNet,
                    'status' => 'DRAFT',
                ]);

                // Create Payslip Items
                foreach ($itemsToCreate as $itemData) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'salary_component_id' => $itemData['salary_component_id'],
                        'name' => $itemData['name'],
                        'type' => $itemData['type'],
                        'amount' => $itemData['amount'],
                    ]);
                }

                $totalEmployees++;
                $totalGross += $empGross;
                $totalDeductions += $empDeductions;
                $totalNet += $empNet;
            }

            // Update PayrollRun totals
            $payrollRun->update([
                'total_employees' => $totalEmployees,
                'total_gross_pay' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net_pay' => $totalNet,
                'status' => 'COMPLETED',
            ]);

            // Update Period Status
            $period->update([
                'status' => 'CALCULATED',
            ]);

            // Audit Log
            AuditLog::create([
                'actor_id' => $processedBy?->id,
                'event' => 'PAYROLL_CALCULATED',
                'auditable_type' => PayrollPeriod::class,
                'auditable_id' => $period->id,
                'old_values' => ['status' => 'DRAFT'],
                'new_values' => [
                    'status' => 'CALCULATED',
                    'run_id' => $payrollRun->id,
                    'total_employees' => $totalEmployees,
                    'total_net_pay' => $totalNet,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);

            return $payrollRun->fresh(['payslips.items', 'payslips.employee']);
        });
    }

    /**
     * Approve payroll period (management/director approval).
     */
    public function approvePeriod(PayrollPeriod $period, User $approver): PayrollPeriod
    {
        if ($period->status !== 'CALCULATED') {
            throw new DomainException("Periode payroll hanya dapat disetujui jika telah selesai dikalkulasi (status CALCULATED). Status saat ini: {$period->status}");
        }

        $oldStatus = $period->status;
        $period->update(['status' => 'APPROVED']);

        AuditLog::create([
            'actor_id' => $approver->id,
            'event' => 'PAYROLL_APPROVED',
            'auditable_type' => PayrollPeriod::class,
            'auditable_id' => $period->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'APPROVED'],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);

        return $period;
    }

    /**
     * Close payroll period.
     * Guard: Cannot close unless CALCULATED and APPROVED by management/director.
     * Note: No automated fund transfers or disbursements are initiated.
     * Locks all attendance records for the period and publishes payslips.
     */
    public function closePeriod(PayrollPeriod $period, User $closedBy): PayrollPeriod
    {
        if ($period->status !== 'APPROVED') {
            throw new DomainException("Periode payroll tidak dapat ditutup. Periode harus berstatus APPROVED oleh manajemen/direksi sebelum ditutup. Status saat ini: {$period->status}");
        }

        return DB::transaction(function () use ($period, $closedBy) {
            $cutoffStart = Carbon::parse($period->effective_cutoff_start_date)->startOfDay();
            $cutoffEnd = Carbon::parse($period->effective_cutoff_end_date)->endOfDay();

            // 1. Lock all related attendance records
            AttendanceRecord::whereBetween('date', [$cutoffStart->toDateString(), $cutoffEnd->toDateString()])
                ->update(['is_locked' => true]);

            // 2. Publish payslips from latest run
            $latestRun = $period->payrollRuns()->latest()->first();
            if ($latestRun) {
                $latestRun->payslips()->update(['status' => 'PUBLISHED']);
            }

            // 3. Update Period status and closed fields
            $oldStatus = $period->status;
            $period->update([
                'status' => 'CLOSED',
                'closed_at' => now(),
                'closed_by_id' => $closedBy->id,
            ]);

            // 4. Audit Log
            AuditLog::create([
                'actor_id' => $closedBy->id,
                'event' => 'PAYROLL_CLOSED',
                'auditable_type' => PayrollPeriod::class,
                'auditable_id' => $period->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => 'CLOSED',
                    'closed_at' => now()->toDateTimeString(),
                    'closed_by_id' => $closedBy->id,
                ],
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
            ]);

            return $period;
        });
    }
}
