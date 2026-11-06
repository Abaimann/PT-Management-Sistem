<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\HRIS\Employee;
use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetAssignment;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Master data barang, SKU, barcode, unit, stok minimum, stok saat ini (/inventory/items).
     */
    public function items(Request $request): View
    {
        $query = Item::with(['stockBalances.warehouse']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();

        // Calculate current stock & low stock indicator for each item
        $items->getCollection()->transform(function ($item) {
            $totalStock = (float) $item->stockBalances->sum('quantity');
            $item->current_stock = $totalStock;
            $item->is_low_stock = $totalStock <= (float) $item->minimum_stock;

            return $item;
        });

        if ($request->boolean('low_stock')) {
            $items->setCollection($items->getCollection()->filter(fn ($i) => $i->is_low_stock)->values());
        }

        $categories = Item::whereNotNull('category')->distinct()->pluck('category');
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.items.index', compact('items', 'categories', 'warehouses'));
    }

    /**
     * Tambah item barang baru.
     */
    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:items,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:items,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['unit'] = strtoupper($validated['unit']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Item::create($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', "Item barang '{$validated['name']}' ({$validated['sku']}) berhasil ditambahkan.");
    }

    /**
     * Master data gudang/lokasi (/inventory/warehouses).
     */
    public function warehouses(Request $request): View
    {
        $warehouses = Warehouse::with(['pic.user', 'stockBalances.item'])
            ->withCount('stockBalances')
            ->orderBy('name')
            ->paginate(15);

        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();

        return view('inventory.warehouses.index', compact('warehouses', 'employees'));
    }

    /**
     * Tambah lokasi gudang baru.
     */
    public function storeWarehouse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string'],
            'pic_employee_id' => ['nullable', 'exists:employees,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Warehouse::create($validated);

        return redirect()->route('inventory.warehouses.index')
            ->with('success', "Gudang '{$validated['name']}' berhasil ditambahkan ke sistem.");
    }

    /**
     * Kartu stok pergerakan barang (IN, OUT, ADJUSTMENT) dengan proteksi saldo stok minus (/inventory/movements).
     */
    public function movements(Request $request): View
    {
        $query = StockMovement::with(['item', 'warehouse', 'createdBy']);

        if ($itemId = $request->input('item_id')) {
            $query->where('item_id', $itemId);
        }

        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($type = $request->input('movement_type')) {
            $query->where('movement_type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'ilike', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('sku', 'ilike', "%{$search}%");
                    });
            });
        }

        $movements = $query->latest('id')->paginate(20)->withQueryString();
        $items = Item::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.movements.index', compact('movements', 'items', 'warehouses'));
    }

    /**
     * Catat mutasi stok baru menggunakan database transaction dengan proteksi saldo minus.
     */
    public function storeMovement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'movement_type' => ['required', 'in:IN,OUT,ADJUSTMENT'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $stockBalance = StockBalance::firstOrCreate(
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'item_id' => $validated['item_id'],
                ],
                ['quantity' => 0]
            );

            // Re-fetch with row lock to avoid race conditions
            $stockBalance = StockBalance::where('id', $stockBalance->id)->lockForUpdate()->first();
            $currentQty = (float) $stockBalance->quantity;
            $deltaQty = (float) $validated['quantity'];

            if ($validated['movement_type'] === 'IN') {
                $newQty = $currentQty + $deltaQty;
            } elseif ($validated['movement_type'] === 'OUT') {
                if ($currentQty < $deltaQty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok tidak mencukupi untuk pengeluaran barang! Saldo saat ini di gudang: {$currentQty}, diminta: {$deltaQty}.",
                    ]);
                }
                $newQty = $currentQty - $deltaQty;
            } else { // ADJUSTMENT
                $newQty = $deltaQty;
            }

            $stockBalance->update(['quantity' => $newQty]);

            StockMovement::create([
                'item_id' => $validated['item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'movement_type' => $validated['movement_type'],
                'quantity' => $deltaQty,
                'balance_after' => $newQty,
                'reference_type' => 'MANUAL_MUTATION',
                'notes' => $validated['notes'] ?? 'Mutasi stok manual',
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('inventory.movements.index')
            ->with('success', 'Mutasi pergerakan stok berhasil dicatat ke kartu inventaris.');
    }

    /**
     * Register aset tetap perusahaan, kode barcode, harga perolehan, nilai buku (/inventory/assets).
     */
    public function assets(Request $request): View
    {
        $query = Asset::with(['assignments.employee.user', 'maintenanceRecords']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('asset_code', 'ilike', "%{$search}%")
                    ->orWhere('barcode', 'ilike', "%{$search}%")
                    ->orWhere('serial_number', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($condition = $request->input('condition')) {
            $query->where('condition', $condition);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $assets = $query->latest('acquisition_date')->latest('id')->paginate(15)->withQueryString();
        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();
        $categories = Asset::whereNotNull('category')->distinct()->pluck('category');

        return view('inventory.assets.index', compact('assets', 'employees', 'categories'));
    }

    /**
     * Registrasi aset baru perusahaan.
     */
    public function storeAsset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asset_code' => ['required', 'string', 'max:50', 'unique:assets,asset_code'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:assets,barcode'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'book_value' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'in:GOOD,SLIGHTLY_DAMAGED,BROKEN,DISPOSED'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['status'] = 'AVAILABLE';

        Asset::create($validated);

        return redirect()->route('inventory.assets.index')
            ->with('success', "Aset tetap '{$validated['name']}' ({$validated['asset_code']}) berhasil didaftarkan.");
    }

    /**
     * BAST serah terima pinjaman aset ke karyawan (nomor BAST digital) dan clearance status saat dikembalikan.
     */
    public function assignAsset(Request $request, Asset $asset): RedirectResponse
    {
        if ($asset->status !== 'AVAILABLE') {
            return redirect()->route('inventory.assets.index')
                ->with('error', "Aset {$asset->asset_code} sedang tidak tersedia untuk dipinjamkan (Status: {$asset->status}).");
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'assignment_date' => ['required', 'date'],
            'condition_on_assignment' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $asset) {
            $bastNumber = sprintf('BAST-%s-%04d', now()->format('Ymd'), $asset->id);

            AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $validated['employee_id'],
                'assignment_date' => $validated['assignment_date'],
                'condition_on_assignment' => $validated['condition_on_assignment'] ?? $asset->condition,
                'status' => 'ACTIVE',
                'handover_document_path' => $bastNumber,
                'clearance_status' => false,
                'notes' => $validated['notes'] ?? null,
            ]);

            $asset->update(['status' => 'ASSIGNED']);
        });

        return redirect()->route('inventory.assets.index')
            ->with('success', "BAST serah terima aset {$asset->asset_code} berhasil diterbitkan.");
    }

    /**
     * Pengembalian aset & update clearance.
     */
    public function returnAsset(Request $request, AssetAssignment $assignment): RedirectResponse
    {
        if ($assignment->status !== 'ACTIVE') {
            return redirect()->route('inventory.assets.index')
                ->with('warning', "Penugasan BAST ini sudah berstatus: {$assignment->status}.");
        }

        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'condition_on_return' => ['required', 'in:GOOD,SLIGHTLY_DAMAGED,BROKEN,DISPOSED'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $assignment) {
            $assignment->update([
                'return_date' => $validated['return_date'],
                'condition_on_return' => $validated['condition_on_return'],
                'status' => 'RETURNED',
                'clearance_status' => true,
                'notes' => trim($assignment->notes . "\n[Pengembalian: " . ($validated['notes'] ?? 'Tanpa catatan khusus') . ']'),
            ]);

            $assignment->asset->update([
                'status' => $validated['condition_on_return'] === 'BROKEN' ? 'IN_MAINTENANCE' : 'AVAILABLE',
                'condition' => $validated['condition_on_return'],
            ]);
        });

        return redirect()->route('inventory.assets.index')
            ->with('success', "Aset {$assignment->asset->asset_code} telah dikembalikan dan clearance status berhasil diterbitkan.");
    }
}
