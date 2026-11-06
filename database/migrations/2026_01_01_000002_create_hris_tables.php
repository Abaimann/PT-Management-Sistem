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
        // 1. Employees Table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nik', 30)->unique();
            $table->string('full_name')->index();
            $table->string('gender', 10);
            $table->string('birth_place')->nullable();
            $table->date('birth_date');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employment_status', 30)->default('PERMANENT')->index(); // PERMANENT, CONTRACT, INTERN, PROBATION
            $table->date('join_date');
            $table->date('resign_date')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['department_id', 'position_id']);
        });

        // 2. Employee Contracts
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->string('contract_type', 30); // PKWT, PKWTT, INTERNSHIP
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('document_path')->nullable();
            $table->string('status', 20)->default('ACTIVE')->index(); // ACTIVE, EXPIRED, TERMINATED
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Shifts Table
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->boolean('is_overnight')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 4. Work Schedules Table
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->date('schedule_date')->index();
            $table->boolean('is_day_off')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'schedule_date']);
        });

        // 5. Leave Types Table
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->integer('default_quota')->default(12);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 6. Leave Balances Table
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('total_quota')->default(12);
            $table->integer('used_quota')->default(0);
            $table->integer('remaining_quota')->default(12);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        // 7. Leave Requests Table
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->default(1);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED, CANCELLED
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('employee_contracts');
        Schema::dropIfExists('employees');
    }
};
