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
        // 1. Warehouses Table
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('location')->nullable();
            $table->foreignId('pic_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Items Master Table
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('name')->index();
            $table->string('category', 50)->nullable()->index();
            $table->string('unit', 20)->default('PCS');
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Stock Balances Table
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'item_id']);
        });

        // 4. Stock Movements Table
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('movement_type', 20)->index(); // IN, OUT, ADJUSTMENT, TRANSFER
            $table->decimal('quantity', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        // 5. Assets Table
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 50)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('name');
            $table->string('category', 50)->nullable()->index();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 16, 2)->default(0);
            $table->decimal('book_value', 16, 2)->default(0);
            $table->string('condition', 30)->default('GOOD'); // GOOD, SLIGHTLY_DAMAGED, BROKEN, DISPOSED
            $table->string('location')->nullable();
            $table->string('status', 30)->default('AVAILABLE')->index(); // AVAILABLE, ASSIGNED, IN_MAINTENANCE, DISPOSED
            $table->timestamps();
        });

        // 6. Asset Assignments Table (BAST)
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('assignment_date');
            $table->date('return_date')->nullable();
            $table->string('condition_on_assignment')->nullable();
            $table->string('condition_on_return')->nullable();
            $table->string('status', 20)->default('ACTIVE')->index(); // ACTIVE, RETURNED, LOST
            $table->string('handover_document_path')->nullable();
            $table->boolean('clearance_status')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // 7. Maintenance Records Table
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('maintenance_type', 30); // PREVENTIVE, CORRECTIVE, SERVICING
            $table->date('schedule_date');
            $table->date('completion_date')->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->string('vendor_name')->nullable();
            $table->text('description');
            $table->string('status', 30)->default('SCHEDULED')->index(); // SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED
            $table->timestamps();
        });

        // 8. Facility Requests Table
        Schema::create('facility_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('request_type', 30); // STATIONERY, EQUIPMENT, FACILITY_REPAIR, VEHICLE
            $table->string('title');
            $table->text('description');
            $table->integer('quantity')->default(1);
            $table->string('priority', 20)->default('MEDIUM');
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, APPROVED, IN_PROGRESS, RESOLVED, REJECTED
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_requests');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('items');
        Schema::dropIfExists('warehouses');
    }
};
