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
        // 1. Work Targets Table
        Schema::create('work_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->string('target_metric')->nullable();
            $table->decimal('target_value', 14, 2)->default(0);
            $table->decimal('achieved_value', 14, 2)->default(0);
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, ACTIVE, COMPLETED, CANCELLED
            $table->timestamps();

            $table->index(['department_id', 'month', 'year']);
        });

        // 2. Tasks Table
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_target_id')->nullable()->constrained('work_targets')->nullOnDelete();
            $table->foreignId('assigned_by')->constrained('employees')->restrictOnDelete();
            $table->foreignId('assigned_to')->constrained('employees')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('MEDIUM')->index(); // LOW, MEDIUM, HIGH, URGENT
            $table->string('status', 30)->default('TODO')->index(); // TODO, IN_PROGRESS, WAITING_VERIFICATION, REVISION, APPROVED
            $table->dateTime('due_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
        });

        // 3. Task Attachments Table
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Task Reviews Table
        Schema::create('task_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback');
            $table->text('revision_notes')->nullable();
            $table->string('decision', 30); // APPROVED, REVISION_NEEDED
            $table->timestamp('reviewed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reviews');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('work_targets');
    }
};
