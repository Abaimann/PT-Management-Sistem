<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\HRIS\Employee;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcurementController extends Controller
{
    /**
     * Master database rekanan vendor (/procurement/vendors).
     */
    public function vendors(Request $request): View
    {
        $query = Vendor::withCount('purchaseOrders');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('code', 'ilike', "%{$search}%")
                    ->orWhere('contact_person', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $vendors = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('procurement.vendors.index', compact('vendors'));
    }

    /**
     * Tambah rekanan vendor baru.
     */
    public function storeVendor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:vendors,code'],
            'name' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Vendor::create($validated);

        return redirect()->route('procurement.vendors.index')
            ->with('success', "Vendor rekanan '{$validated['name']}' berhasil didaftarkan.");
    }

    /**
     * Daftar Purchase Request (PR) dari departemen (/procurement/requests).
     */
    public function requests(Request $request): View
    {
        $query = PurchaseRequest::with(['department', 'requester.user', 'items.item']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'ilike', "%{$search}%")
                    ->orWhere('reason', 'ilike', "%{$search}%");
            });
        }

        $purchaseRequests = $query->latest('request_date')->latest('id')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('procurement.requests.index', compact('purchaseRequests', 'departments'));
    }

    /**
     * Form pengajuan PR + items.
     */
    public function createRequest(): View
    {
        $departments = Department::orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('name')->get();

        return view('procurement.requests.create', compact('departments', 'items'));
    }

    /**
     * Simpan pengajuan PR + items.
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'required_date' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['nullable', 'exists:items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.estimated_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = $this->resolveCurrentEmployee();

        DB::transaction(function () use ($validated, $employee) {
            $yearMonth = now()->format('Ym');
            $count = PurchaseRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;
            $prNumber = sprintf('PR-%s-%04d', $yearMonth, $count);

            $totalAmount = 0;
            foreach ($validated['items'] as $itemData) {
                $totalAmount += ((float) $itemData['quantity'] * (float) $itemData['estimated_price']);
            }

            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'department_id' => $validated['department_id'],
                'requester_id' => $employee->id,
                'request_date' => now()->toDateString(),
                'required_date' => $validated['required_date'] ?? null,
                'total_amount' => $totalAmount,
                'reason' => $validated['reason'],
                'status' => 'SUBMITTED',
            ]);

            foreach ($validated['items'] as $itemData) {
                $subtotal = (float) $itemData['quantity'] * (float) $itemData['estimated_price'];
                PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => strtoupper($itemData['unit'] ?? 'PCS'),
                    'estimated_price' => $itemData['estimated_price'],
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('procurement.requests.index')
            ->with('success', 'Purchase Request (PR) berhasil diajukan dan menunggu persetujuan.');
    }

    /**
     * Otorisasi PR menjadi status siap diterbitkan ke PO.
     */
    public function approveRequest(PurchaseRequest $pr): RedirectResponse
    {
        if (! in_array($pr->status, ['DRAFT', 'SUBMITTED'])) {
            return redirect()->route('procurement.requests.index')
                ->with('warning', "PR #{$pr->pr_number} tidak dapat diotorisasi karena status saat ini: {$pr->status}.");
        }

        $pr->update(['status' => 'APPROVED']);

        return redirect()->route('procurement.requests.index')
            ->with('success', "Purchase Request #{$pr->pr_number} berhasil disetujui (APPROVED) & siap dikonversi ke PO.");
    }

    /**
     * Daftar Purchase Order (PO) ke vendor (/procurement/orders).
     */
    public function orders(Request $request): View
    {
        $query = PurchaseOrder::with(['vendor', 'purchaseRequest', 'items', 'goodsReceipts']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($vendorId = $request->input('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                    ->orWhereHas('vendor', function ($vq) use ($search) {
                        $vq->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $purchaseOrders = $query->latest('po_date')->latest('id')->paginate(15)->withQueryString();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('procurement.orders.index', compact('purchaseOrders', 'vendors'));
    }

    /**
     * Konversi PR ke PO atau penerbitan PO baru ke vendor.
     */
    public function createOrder(Request $request): View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('name')->get();

        $selectedPr = null;
        if ($prId = $request->input('pr_id')) {
            $selectedPr = PurchaseRequest::with('items')->find($prId);
        }

        $approvedPrs = PurchaseRequest::where('status', 'APPROVED')
            ->with('department')
            ->latest()
            ->get();

        return view('procurement.orders.create', compact('vendors', 'items', 'selectedPr', 'approvedPrs'));
    }

    /**
     * Simpan penerbitan PO baru ke vendor.
     */
    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purchase_request_id' => ['nullable', 'exists:purchase_requests,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'po_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['nullable', 'exists:items,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $po = DB::transaction(function () use ($validated) {
            $yearMonth = now()->format('Ym');
            $count = PurchaseOrder::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;
            $poNumber = sprintf('PO-%s-%04d', $yearMonth, $count);

            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $subtotal += ((float) $itemData['quantity'] * (float) $itemData['unit_price']);
            }

            $taxRate = isset($validated['tax_percent']) ? ((float) $validated['tax_percent'] / 100) : 0.11;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $validated['purchase_request_id'] ?? null,
                'vendor_id' => $validated['vendor_id'],
                'po_date' => $validated['po_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'SENT',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $itemSubtotal = (float) $itemData['quantity'] * (float) $itemData['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => strtoupper($itemData['unit'] ?? 'PCS'),
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemSubtotal,
                    'received_quantity' => 0,
                ]);
            }

            if (! empty($validated['purchase_request_id'])) {
                PurchaseRequest::where('id', $validated['purchase_request_id'])
                    ->update(['status' => 'CONVERTED_TO_PO']);
            }

            return $po;
        });

        return redirect()->route('procurement.orders.index')
            ->with('success', "Purchase Order #{$po->po_number} berhasil diterbitkan ke vendor.");
    }

    /**
     * Daftar penerimaan barang (Goods Receipts).
     */
    public function receipts(Request $request): View
    {
        $query = GoodsReceipt::with([
            'purchaseOrder.vendor',
            'warehouse',
            'receivedBy.user',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('gr_number', 'ilike', "%{$search}%")
                    ->orWhere('delivery_order_number', 'ilike', "%{$search}%")
                    ->orWhereHas('purchaseOrder', function ($poq) use ($search) {
                        $poq->where('po_number', 'ilike', "%{$search}%");
                    });
            });
        }

        $receipts = $query->latest('receipt_date')->latest('id')->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $openOrders = PurchaseOrder::whereIn('status', ['SENT', 'PARTIAL_RECEIVED'])
            ->with(['vendor', 'items'])
            ->orderBy('po_number')
            ->get();

        return view('procurement.receipts.index', compact('receipts', 'warehouses', 'openOrders'));
    }

    /**
     * Catat penerimaan fisik barang di gudang (Three-Way Matching validasi quantity vs PO).
     */
    public function storeReceipt(Request $request, PurchaseOrder $po): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'receipt_date' => ['required', 'date'],
            'delivery_order_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
        ]);

        $employee = $this->resolveCurrentEmployee();

        DB::transaction(function () use ($validated, $po, $employee) {
            $yearMonth = now()->format('Ym');
            $count = GoodsReceipt::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count() + 1;
            $grNumber = sprintf('GR-%s-%04d', $yearMonth, $count);

            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $po->id,
                'warehouse_id' => $validated['warehouse_id'],
                'received_by' => $employee->id,
                'receipt_date' => $validated['receipt_date'],
                'delivery_order_number' => $validated['delivery_order_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'CONFIRMED',
            ]);

            $totalAcceptedInThisGr = 0;

            foreach ($validated['items'] as $receiptItem) {
                $qty = (float) $receiptItem['received_qty'];
                if ($qty <= 0) {
                    continue;
                }

                $poItem = PurchaseOrderItem::where('id', $receiptItem['po_item_id'])
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyReceived = (float) $poItem->received_quantity;
                $orderedQty = (float) $poItem->quantity;

                // Three-way matching check: received_quantity + new_qty <= ordered_quantity
                if (($alreadyReceived + $qty) > ($orderedQty + 0.0001)) {
                    $remaining = max(0, $orderedQty - $alreadyReceived);
                    throw ValidationException::withMessages([
                        'items' => "Kuantitas penerimaan untuk '{$poItem->item_name}' ({$qty}) melebihi sisa pesanan PO ({$remaining}).",
                    ]);
                }

                // Update PO Item received quantity
                $poItem->update([
                    'received_quantity' => $alreadyReceived + $qty,
                ]);

                $totalAcceptedInThisGr += $qty;

                // If this PO item is linked to an inventory item, update inventory stock balance and log movement
                if ($poItem->item_id) {
                    $stockBalance = StockBalance::firstOrCreate(
                        [
                            'warehouse_id' => $validated['warehouse_id'],
                            'item_id' => $poItem->item_id,
                        ],
                        ['quantity' => 0]
                    );

                    $stockBalance->increment('quantity', $qty);
                    $stockBalance->refresh();

                    StockMovement::create([
                        'item_id' => $poItem->item_id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'movement_type' => 'IN',
                        'quantity' => $qty,
                        'balance_after' => $stockBalance->quantity,
                        'reference_type' => GoodsReceipt::class,
                        'reference_id' => $gr->id,
                        'notes' => "Penerimaan PO #{$po->po_number} (SJ: {$validated['delivery_order_number']})",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            if ($totalAcceptedInThisGr <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Minimal salah satu item harus memiliki jumlah terima lebih dari 0.',
                ]);
            }

            // Recalculate PO overall status
            $po->load('items');
            $allComplete = true;
            $anyReceived = false;

            foreach ($po->items as $item) {
                if ((float) $item->received_quantity < (float) $item->quantity) {
                    $allComplete = false;
                }
                if ((float) $item->received_quantity > 0) {
                    $anyReceived = true;
                }
            }

            if ($allComplete) {
                $po->update(['status' => 'RECEIVED']);
            } elseif ($anyReceived) {
                $po->update(['status' => 'PARTIAL_RECEIVED']);
            }
        });

        return redirect()->route('procurement.receipts.index')
            ->with('success', "Penerimaan barang PO #{$po->po_number} berhasil dicatat dengan validasi 3-Way Matching dan stok otomatis ter-update.");
    }

    /**
     * Helper to resolve the authenticated user's employee record.
     */
    protected function resolveCurrentEmployee(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            $dept = Department::firstOrCreate(['code' => 'PROC'], ['name' => 'Procurement & Purchasing']);
            $pos = Position::firstOrCreate(
                ['name' => 'Procurement Specialist', 'department_id' => $dept->id],
                ['grade_level' => 'Staff', 'is_active' => true]
            );

            $employee = Employee::firstOrCreate(
                ['user_id' => auth()->id()],
                [
                    'nik' => 'EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT),
                    'full_name' => auth()->user()->name,
                    'gender' => 'MALE',
                    'birth_date' => '1990-01-01',
                    'department_id' => $dept->id,
                    'position_id' => $pos->id,
                    'employment_status' => 'PERMANENT',
                    'join_date' => now(),
                    'is_active' => true,
                ]
            );
        }

        return $employee;
    }
}
