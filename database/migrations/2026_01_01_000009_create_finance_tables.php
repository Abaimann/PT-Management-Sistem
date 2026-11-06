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
        // 1. Chart of Accounts Table
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 30)->unique();
            $table->string('account_name');
            $table->string('classification', 30)->index(); // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('normal_balance', 10); // DEBIT, CREDIT
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Journal Entries Table
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('date')->index();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description');
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, POSTED, VOID
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        // 3. Journal Lines Table
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->timestamps();

            $table->index(['journal_entry_id', 'chart_of_account_id']);
        });

        // 4. Budgets Table
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('chart_of_account_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->integer('fiscal_year');
            $table->decimal('allocated_amount', 16, 2)->default(0);
            $table->decimal('spent_amount', 16, 2)->default(0);
            $table->decimal('remaining_amount', 16, 2)->default(0);
            $table->string('status', 20)->default('DRAFT')->index(); // DRAFT, APPROVED, EXHAUSTED
            $table->timestamps();

            $table->index(['department_id', 'fiscal_year']);
        });

        // 5. Reimbursements Table
        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('claim_number', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('receipt_path')->nullable();
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED, PAID
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursements');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};
