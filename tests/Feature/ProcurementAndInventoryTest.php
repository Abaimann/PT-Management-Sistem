<?php

namespace Tests\Feature;

use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetAssignment;
use App\Models\Inventory\FacilityRequest;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\Warehouse;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\PurchaseRequestItem;
use App\Models\Procurement\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProcurementAndInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $officerUser;

    protected Employee $employee;

    protected Department $department;

    protected Position $position;

    protected Warehouse $warehouse;

    protected Item $item;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $procRole = Role::firstOrCreate(['code' => 'procurement_officer'], ['name' => 'Procurement Officer']);
        $invRole = Role::firstOrCreate(['code' => 'inventory_ga'], ['name' => 'Inventory & GA']);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Admin Procurement '.uniqid(),
            'email' => 'admin_proc_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->adminUser->roles()->sync([$superAdminRole->id]);

        // Officer User
        $this->officerUser = User::create([
            'name' => 'Officer Test '.uniqid(),
            'email' => 'officer_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->officerUser->roles()->sync([$procRole->id, $invRole->id]);

        // Department & Position
        $this->department = Department::firstOrCreate(
            ['code' => 'PROC-TEST'],
            ['name' => 'Procurement Department', 'cost_center' => 'CC-PROC', 'is_active' => true]
        );

        $this->position = Position::firstOrCreate(
            ['name' => 'Procurement Officer Test', 'department_id' => $this->department->id],
            ['grade_level' => 'Senior', 'is_active' => true]
        );

        // Employee
        $this->employee = Employee::create([
            'user_id' => $this->officerUser->id,
            'nik' => 'EMP-'.uniqid(),
            'full_name' => 'Officer Procurement Test',
            'gender' => 'MALE',
            'birth_date' => '1992-05-15',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2023-01-01',
            'is_active' => true,
        ]);

        // Warehouse
        $this->warehouse = Warehouse::create([
            'code' => 'WH-TEST-'.uniqid(),
            'name' => 'Gudang Pusat Logistik Test',
            'location' => 'Lantai 1 Sayap Barat',
            'pic_employee_id' => $this->employee->id,
            'is_active' => true,
        ]);

        // Item
        $this->item = Item::create([
            'sku' => 'SKU-TEST-'.uniqid(),
            'barcode' => 'BAR-'.uniqid(),
            'name' => 'Kertas A4 80gr Rim',
            'category' => 'ATK',
            'unit' => 'RIM',
            'minimum_stock' => 10,
            'is_active' => true,
        ]);

        // Vendor
        $this->vendor = Vendor::create([
            'code' => 'VND-TEST-'.uniqid(),
            'name' => 'PT Mitra Supplier Nusantara',
            'npwp' => '01.234.567.8-901.000',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'sales@mitrasupplier.test',
            'address' => 'Kawasan Industri Pulo Gadung, Jakarta',
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'is_active' => true,
        ]);
    }

    public function test_can_view_vendors_index_and_create_vendor(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('procurement.vendors.index'));
        $response->assertStatus(200);
        $response->assertSee($this->vendor->name);

        $vendorCode = 'VND-NEW-'.uniqid();
        $createResponse = $this->actingAs($this->adminUser)->post(route('procurement.vendors.store'), [
            'code' => $vendorCode,
            'name' => 'PT Graha Stationary Utama',
            'npwp' => '32.111.222.3-444.000',
            'contact_person' => 'Siti Rahma',
            'phone' => '08987654321',
            'email' => 'info@grahastationary.test',
            'address' => 'Jl. Sudirman Kav 25',
            'bank_name' => 'Mandiri',
            'bank_account' => '9876543210',
            'is_active' => 1,
        ]);

        $createResponse->assertRedirect(route('procurement.vendors.index'));
        $this->assertDatabaseHas('vendors', [
            'code' => $vendorCode,
            'name' => 'PT Graha Stationary Utama',
        ]);
    }

    public function test_can_create_purchase_request_and_approve(): void
    {
        $response = $this->actingAs($this->officerUser)->get(route('procurement.requests.create'));
        $response->assertStatus(200);

        $postData = [
            'department_id' => $this->department->id,
            'required_date' => now()->addDays(5)->toDateString(),
            'reason' => 'Kebutuhan ATK administrasi bulanan',
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'quantity' => 20,
                    'unit' => 'RIM',
                    'estimated_price' => 50000,
                    'notes' => 'Untuk cetak laporan akhir tahun',
                ],
            ],
        ];

        $storeResponse = $this->actingAs($this->officerUser)->post(route('procurement.requests.store'), $postData);
        $storeResponse->assertRedirect(route('procurement.requests.index'));

        $pr = PurchaseRequest::where('department_id', $this->department->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($pr);
        $this->assertEquals('SUBMITTED', $pr->status);
        $this->assertEquals(1000000.00, (float) $pr->total_amount);
        $this->assertDatabaseHas('purchase_request_items', [
            'purchase_request_id' => $pr->id,
            'item_id' => $this->item->id,
            'quantity' => 20,
        ]);

        // Approve PR
        $approveResponse = $this->actingAs($this->adminUser)->post(route('procurement.requests.approve', $pr));
        $approveResponse->assertRedirect(route('procurement.requests.index'));

        $pr->refresh();
        $this->assertEquals('APPROVED', $pr->status);
    }

    public function test_can_create_purchase_order_from_approved_pr(): void
    {
        // Setup an approved PR
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-'.uniqid(),
            'department_id' => $this->department->id,
            'requester_id' => $this->employee->id,
            'request_date' => now()->toDateString(),
            'total_amount' => 1000000,
            'reason' => 'Pengadaan kertas kantor',
            'status' => 'APPROVED',
        ]);

        PurchaseRequestItem::create([
            'purchase_request_id' => $pr->id,
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'quantity' => 20,
            'unit' => 'RIM',
            'estimated_price' => 50000,
            'subtotal' => 1000000,
        ]);

        // Access create order with PR prefill
        $viewResponse = $this->actingAs($this->adminUser)->get(route('procurement.orders.create', ['pr_id' => $pr->id]));
        $viewResponse->assertStatus(200);

        // Store PO
        $poData = [
            'purchase_request_id' => $pr->id,
            'vendor_id' => $this->vendor->id,
            'po_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Term of payment: 30 hari',
            'tax_percent' => 11,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'item_name' => $this->item->name,
                    'quantity' => 20,
                    'unit' => 'RIM',
                    'unit_price' => 50000,
                ],
            ],
        ];

        $storeResponse = $this->actingAs($this->adminUser)->post(route('procurement.orders.store'), $poData);
        $storeResponse->assertRedirect(route('procurement.orders.index'));

        $po = PurchaseOrder::where('purchase_request_id', $pr->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals('SENT', $po->status);
        $this->assertEquals(1000000.00, (float) $po->subtotal);
        $this->assertEquals(110000.00, (float) $po->tax_amount);
        $this->assertEquals(1110000.00, (float) $po->total_amount);

        // Linked PR status converted
        $pr->refresh();
        $this->assertEquals('CONVERTED_TO_PO', $pr->status);
    }

    public function test_goods_receipt_validates_three_way_matching_and_updates_inventory_stock(): void
    {
        // Create PO
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-'.uniqid(),
            'vendor_id' => $this->vendor->id,
            'po_date' => now()->toDateString(),
            'subtotal' => 1000000,
            'tax_amount' => 110000,
            'total_amount' => 1110000,
            'status' => 'SENT',
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'quantity' => 20,
            'unit' => 'RIM',
            'unit_price' => 50000,
            'subtotal' => 1000000,
            'received_quantity' => 0,
        ]);

        // Attempt to receive MORE than ordered quantity (Three-Way Matching violation)
        $invalidReceipt = [
            'warehouse_id' => $this->warehouse->id,
            'receipt_date' => now()->toDateString(),
            'delivery_order_number' => 'SJ-OVERFLOW-001',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'received_qty' => 25, // ordered is only 20
                ],
            ],
        ];

        $invalidResponse = $this->actingAs($this->officerUser)
            ->post(route('procurement.receipts.store', $po), $invalidReceipt);
        $invalidResponse->assertSessionHasErrors('items');

        // Now receive PARTIAL quantity (10 of 20)
        $validReceiptPartial = [
            'warehouse_id' => $this->warehouse->id,
            'receipt_date' => now()->toDateString(),
            'delivery_order_number' => 'SJ-PARTIAL-001',
            'notes' => 'Pengiriman termin pertama 10 rim',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'received_qty' => 10,
                ],
            ],
        ];

        $partialResponse = $this->actingAs($this->officerUser)
            ->post(route('procurement.receipts.store', $po), $validReceiptPartial);
        $partialResponse->assertRedirect(route('procurement.receipts.index'));

        $po->refresh();
        $poItem->refresh();
        $this->assertEquals('PARTIAL_RECEIVED', $po->status);
        $this->assertEquals(10.0, (float) $poItem->received_quantity);

        // Verify stock balance in warehouse updated automatically
        $balance = StockBalance::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->item->id)
            ->first();
        $this->assertNotNull($balance);
        $this->assertEquals(10.0, (float) $balance->quantity);

        // Verify StockMovement logged
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'IN',
            'quantity' => 10.0,
            'balance_after' => 10.0,
        ]);

        // Second receipt: receive remaining 10 rim (total 20/20)
        $validReceiptFinal = [
            'warehouse_id' => $this->warehouse->id,
            'receipt_date' => now()->toDateString(),
            'delivery_order_number' => 'SJ-FINAL-002',
            'notes' => 'Pengiriman sisa 10 rim',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'received_qty' => 10,
                ],
            ],
        ];

        $finalResponse = $this->actingAs($this->officerUser)
            ->post(route('procurement.receipts.store', $po), $validReceiptFinal);
        $finalResponse->assertRedirect(route('procurement.receipts.index'));

        $po->refresh();
        $poItem->refresh();
        $this->assertEquals('RECEIVED', $po->status);
        $this->assertEquals(20.0, (float) $poItem->received_quantity);

        $balance->refresh();
        $this->assertEquals(20.0, (float) $balance->quantity);
    }

    public function test_inventory_stock_movement_prevents_negative_balance(): void
    {
        // Initialize stock balance to 5
        StockBalance::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'item_id' => $this->item->id],
            ['quantity' => 5]
        );

        // Attempt OUT with 10 (exceeds balance of 5)
        $invalidOut = [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'OUT',
            'quantity' => 10,
            'notes' => 'Pengambilan melebih stok',
        ];

        $failResponse = $this->actingAs($this->officerUser)
            ->post(route('inventory.movements.store'), $invalidOut);
        $failResponse->assertSessionHasErrors('quantity');

        // Valid OUT of 3 (balance becomes 2)
        $validOut = [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'OUT',
            'quantity' => 3,
            'notes' => 'Pengambilan wajar',
        ];

        $successResponse = $this->actingAs($this->officerUser)
            ->post(route('inventory.movements.store'), $validOut);
        $successResponse->assertRedirect(route('inventory.movements.index'));

        $balance = StockBalance::where('warehouse_id', $this->warehouse->id)
            ->where('item_id', $this->item->id)
            ->first();
        $this->assertEquals(2.0, (float) $balance->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => 'OUT',
            'quantity' => 3.0,
            'balance_after' => 2.0,
        ]);
    }

    public function test_asset_registration_assignment_bast_and_return_clearance(): void
    {
        $assetCode = 'AST-LAP-'.uniqid();
        $assetData = [
            'asset_code' => $assetCode,
            'barcode' => 'BAR-'.uniqid(),
            'serial_number' => 'SN-'.uniqid(),
            'name' => 'Dell Latitude 7420 i7 16GB',
            'category' => 'Laptop',
            'acquisition_date' => '2024-01-10',
            'acquisition_cost' => 18500000,
            'book_value' => 15000000,
            'condition' => 'GOOD',
            'location' => 'IT Stockroom',
        ];

        // Store Asset
        $storeAssetResponse = $this->actingAs($this->adminUser)
            ->post(route('inventory.assets.store'), $assetData);
        $storeAssetResponse->assertRedirect(route('inventory.assets.index'));

        $asset = Asset::where('asset_code', $assetCode)->first();
        $this->assertNotNull($asset);
        $this->assertEquals('AVAILABLE', $asset->status);

        // Assign Asset (BAST)
        $assignData = [
            'employee_id' => $this->employee->id,
            'assignment_date' => now()->toDateString(),
            'condition_on_assignment' => 'GOOD',
            'notes' => 'Kelengkapan charger type-c dan tas laptop',
        ];

        $assignResponse = $this->actingAs($this->adminUser)
            ->post(route('inventory.assets.assign', $asset), $assignData);
        $assignResponse->assertRedirect(route('inventory.assets.index'));

        $asset->refresh();
        $this->assertEquals('ASSIGNED', $asset->status);

        $assignment = AssetAssignment::where('asset_id', $asset->id)
            ->where('employee_id', $this->employee->id)
            ->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('ACTIVE', $assignment->status);
        $this->assertFalse($assignment->clearance_status);
        $this->assertNotNull($assignment->handover_document_path);

        // Return Asset with Clearance
        $returnData = [
            'return_date' => now()->toDateString(),
            'condition_on_return' => 'GOOD',
            'notes' => 'Fisik mulus, fungsi charger normal',
        ];

        $returnResponse = $this->actingAs($this->adminUser)
            ->post(route('inventory.assignments.return', $assignment), $returnData);
        $returnResponse->assertRedirect(route('inventory.assets.index'));

        $assignment->refresh();
        $asset->refresh();

        $this->assertEquals('RETURNED', $assignment->status);
        $this->assertTrue($assignment->clearance_status);
        $this->assertEquals('AVAILABLE', $asset->status);
    }

    public function test_general_affairs_facility_request_and_status_update(): void
    {
        $response = $this->actingAs($this->officerUser)->get(route('ga.requests.index'));
        $response->assertStatus(200);

        // Submit request
        $reqData = [
            'request_type' => 'FACILITY_REPAIR',
            'title' => 'AC Ruang Meeting Lt. 2 Tidak Dingin',
            'description' => 'Suhu AC tetap 28C meski diset 18C, terdengar bunyi hembusan lemah.',
            'quantity' => 1,
            'priority' => 'HIGH',
            'department_id' => $this->department->id,
        ];

        $storeResponse = $this->actingAs($this->officerUser)->post(route('ga.requests.store'), $reqData);
        $storeResponse->assertRedirect(route('ga.requests.index'));

        $facilityRequest = FacilityRequest::where('title', $reqData['title'])->first();
        $this->assertNotNull($facilityRequest);
        $this->assertEquals('PENDING', $facilityRequest->status);
        $this->assertEquals($this->employee->id, $facilityRequest->employee_id);

        // Privileged GA updates status to RESOLVED
        $updateResponse = $this->actingAs($this->adminUser)
            ->patch(route('ga.requests.status', $facilityRequest), [
                'status' => 'RESOLVED',
                'resolution_notes' => 'Teknisi PT Sejuk Abadi telah melakukan isi ulang freon R32.',
            ]);

        $updateResponse->assertRedirect(route('ga.requests.index'));

        $facilityRequest->refresh();
        $this->assertEquals('RESOLVED', $facilityRequest->status);
        $this->assertNotNull($facilityRequest->resolved_at);
        $this->assertStringContainsString('Teknisi PT Sejuk Abadi', $facilityRequest->description);
    }
}
