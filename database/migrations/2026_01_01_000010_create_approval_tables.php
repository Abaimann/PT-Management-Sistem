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
        // 1. Approval Requests Table
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 100)->index();
            $table->unsignedBigInteger('document_id')->index();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED
            $table->integer('total_steps')->default(1);
            $table->integer('current_step')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });

        // 2. Approval Steps Table
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->integer('step_order')->default(1);
            $table->string('decision', 20)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED
            $table->text('notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_requests');
    }
};
