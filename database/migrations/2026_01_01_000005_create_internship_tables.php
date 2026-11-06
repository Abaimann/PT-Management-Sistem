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
        // 1. Interns Table
        Schema::create('interns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('mentor_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('intern_code', 30)->unique();
            $table->string('full_name');
            $table->string('institution');
            $table->string('major')->nullable();
            $table->string('phone', 30)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('ACTIVE')->index(); // ACTIVE, COMPLETED, TERMINATED
            $table->timestamps();
        });

        // 2. Internship Logbooks Table
        Schema::create('internship_logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->date('date')->index();
            $table->text('activity_description');
            $table->string('output_url')->nullable();
            $table->string('status', 20)->default('SUBMITTED')->index(); // SUBMITTED, VERIFIED, REJECTED
            $table->foreignId('verified_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['intern_id', 'date']);
        });

        // 3. Internship Evaluations Table
        Schema::create('internship_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('discipline_score', 5, 2)->default(0);
            $table->decimal('technical_score', 5, 2)->default(0);
            $table->decimal('teamwork_score', 5, 2)->default(0);
            $table->decimal('communication_score', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->text('recommendation')->nullable();
            $table->string('certificate_number', 50)->nullable();
            $table->date('evaluated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_evaluations');
        Schema::dropIfExists('internship_logbooks');
        Schema::dropIfExists('interns');
    }
};
