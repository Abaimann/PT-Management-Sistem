<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Salary Components Table
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('type', 20)->index(); // EARNING, DEDUCTION
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_fixed')->default(true);
            $table->decimal('default_amount', 14, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Employee Salary Components Table
        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['employee_id', 'salary_component_id']);
        });

        // 3. Payroll Periods Table
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('cutoff_date');
            $table->date('payment_date');
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, CALCULATED, APPROVED, LOCKED_CLOSED
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 4. Payroll Runs Table
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->string('run_number', 50)->unique();
            $table->date('run_date')->index();
            $table->integer('total_employees')->default(0);
            $table->decimal('total_gross_pay', 16, 2)->default(0);
            $table->decimal('total_deductions', 16, 2)->default(0);
            $table->decimal('total_net_pay', 16, 2)->default(0);
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, PROCESSING, COMPLETED, FAILED
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Payslips Table
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('payslip_number', 50)->unique();
            $table->decimal('gross_salary', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('net_salary', 14, 2)->default(0);
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, PUBLISHED, PAID
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });

        // 6. Payslip Items Table
        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('payslips')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->restrictOnDelete();
            $table->string('name');
            $table->string('type', 20); // EARNING, DEDUCTION
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_components');
    }
};
