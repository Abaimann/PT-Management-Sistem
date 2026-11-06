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
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->foreignId('closed_by_id')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            $table->date('cutoff_start_date')->nullable()->after('end_date');
            $table->date('cutoff_end_date')->nullable()->after('cutoff_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['closed_by_id']);
            $table->dropColumn(['closed_by_id', 'cutoff_start_date', 'cutoff_end_date']);
        });
    }
};
