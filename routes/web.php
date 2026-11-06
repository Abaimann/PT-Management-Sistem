<?php

use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Core\DepartmentController;
use App\Http\Controllers\Core\PositionController;
use App\Http\Controllers\Core\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Executive\ExecutiveDashboardController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Finance\ReimbursementController;
use App\Http\Controllers\GeneralAffairs\GeneralAffairsController;
use App\Http\Controllers\HRIS\EmployeeController;
use App\Http\Controllers\HRIS\LeaveController;
use App\Http\Controllers\Internship\InternshipController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Operations\TaskController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Procurement\ProcurementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PT Management Sistem ERP
|--------------------------------------------------------------------------
*/

// Root URL Redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Static Blueprint & Documentation Viewers
Route::get('/index.html', function () {
    return response()->file(base_path('index.html'));
});
Route::get('/update-rancangan.html', function () {
    return response()->file(base_path('update-rancangan.html'));
});
Route::get('/docs/{file}', function ($file) {
    $path = base_path('docs/' . basename($file));
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/plain; charset=UTF-8'
        ]);
    }
    abort(404);
})->where('file', '.*');

// Authentication Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Authenticated Routes (Protected by Auth & Active Guard)
Route::middleware(['auth', 'active'])->group(function () {
    // Session Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Central Executive & Operational Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Executive KPI Dashboard (Direksi & Super Admin)
    Route::get('/executive', [ExecutiveDashboardController::class, 'index'])
        ->middleware('role:super_admin|director')
        ->name('executive.dashboard');
    Route::redirect('/executive/dashboard', '/executive');

    // Profile & Account Security
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // Core Master Data: Departments & Positions
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);

    // HRIS: Employees Directory, Profiling & Management
    Route::resource('employees', EmployeeController::class);

    // Smart Attendance & Geofencing
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/scan', [AttendanceController::class, 'create'])->name('create');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('checkin');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('checkout');
        Route::get('/corrections', [AttendanceController::class, 'corrections'])->name('corrections');
        Route::post('/corrections', [AttendanceController::class, 'storeCorrection'])->name('corrections.store');
        Route::post('/corrections/{correction}/approve', [AttendanceController::class, 'approveCorrection'])->name('corrections.approve');
    });

    // Leaves Management
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::post('/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('reject');
    });

    // Operations / Work Management & Tasks
    Route::resource('tasks', TaskController::class);
    Route::redirect('/operations/tasks', '/tasks');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('tasks/{task}/evidence', [TaskController::class, 'submitEvidence'])->name('tasks.evidence');
    Route::post('tasks/{task}/review', [TaskController::class, 'review'])->name('tasks.review');
    Route::get('tasks/{task}/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');

    // Internship Management
    Route::prefix('internship')->name('internship.')->group(function () {
        Route::get('/', [InternshipController::class, 'index'])->name('index');
        Route::get('/create', [InternshipController::class, 'create'])->name('create');
        Route::post('/', [InternshipController::class, 'store'])->name('store');
        Route::get('/logbooks', [InternshipController::class, 'logbooks'])->name('logbooks');
        Route::post('/logbooks', [InternshipController::class, 'storeLogbook'])->name('logbooks.store');
        Route::patch('/logbooks/{logbook}/verify', [InternshipController::class, 'verifyLogbook'])->name('logbooks.verify');
        Route::get('/{intern}', [InternshipController::class, 'show'])->name('show');
        Route::post('/{intern}/evaluate', [InternshipController::class, 'evaluate'])->name('evaluate');
    });

    // Payroll Management
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');

        // Periods
        Route::prefix('periods')->name('periods.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/create', [PayrollController::class, 'createPeriod'])->name('create');
            Route::post('/', [PayrollController::class, 'storePeriod'])->name('store');
            Route::get('/{period}', [PayrollController::class, 'show'])->name('show');
            Route::post('/{period}/calculate', [PayrollController::class, 'calculate'])->name('calculate');
            Route::post('/{period}/approve', [PayrollController::class, 'approve'])->name('approve');
            Route::post('/{period}/close', [PayrollController::class, 'close'])->name('close');
        });

        // Payslips
        Route::get('/payslips/{payslip}', [PayrollController::class, 'payslip'])->name('payslips.show');
        Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('payslips.my');
    });

    // Finance & Double-Entry Accounting
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/accounts', [FinanceController::class, 'accounts'])->name('accounts.index');
        Route::post('/accounts', [FinanceController::class, 'storeAccount'])->name('accounts.store');
        Route::get('/journals', [FinanceController::class, 'journals'])->name('journals.index');
        Route::get('/journals/create', [FinanceController::class, 'createJournal'])->name('journals.create');
        Route::post('/journals', [FinanceController::class, 'storeJournal'])->name('journals.store');
        Route::post('/journals/{journal}/post', [FinanceController::class, 'postJournal'])->name('journals.post');
        Route::get('/reports/general-ledger', [FinanceController::class, 'generalLedger'])->name('reports.general_ledger');
        Route::get('/reports/trial-balance', [FinanceController::class, 'trialBalance'])->name('reports.trial_balance');
    });

    // Reimbursement Claims Management
    Route::prefix('reimbursements')->name('reimbursements.')->group(function () {
        Route::get('/', [ReimbursementController::class, 'index'])->name('index');
        Route::get('/create', [ReimbursementController::class, 'create'])->name('create');
        Route::post('/', [ReimbursementController::class, 'store'])->name('store');
        Route::post('/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('approve');
        Route::post('/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reject');
        Route::get('/{reimbursement}/receipt', [ReimbursementController::class, 'receipt'])->name('receipt');
    });

    // Procurement & Purchasing
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/vendors', [ProcurementController::class, 'vendors'])->name('vendors.index');
        Route::post('/vendors', [ProcurementController::class, 'storeVendor'])->name('vendors.store');

        Route::get('/requests', [ProcurementController::class, 'requests'])->name('requests.index');
        Route::get('/requests/create', [ProcurementController::class, 'createRequest'])->name('requests.create');
        Route::post('/requests', [ProcurementController::class, 'storeRequest'])->name('requests.store');
        Route::post('/requests/{pr}/approve', [ProcurementController::class, 'approveRequest'])->name('requests.approve');

        Route::get('/orders', [ProcurementController::class, 'orders'])->name('orders.index');
        Route::get('/orders/create', [ProcurementController::class, 'createOrder'])->name('orders.create');
        Route::post('/orders', [ProcurementController::class, 'storeOrder'])->name('orders.store');

        Route::get('/receipts', [ProcurementController::class, 'receipts'])->name('receipts.index');
        Route::post('/orders/{po}/receipts', [ProcurementController::class, 'storeReceipt'])->name('receipts.store');
    });

    // Inventory & Fixed Asset Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/items', [InventoryController::class, 'items'])->name('items.index');
        Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');

        Route::get('/warehouses', [InventoryController::class, 'warehouses'])->name('warehouses.index');
        Route::post('/warehouses', [InventoryController::class, 'storeWarehouse'])->name('warehouses.store');

        Route::get('/movements', [InventoryController::class, 'movements'])->name('movements.index');
        Route::post('/movements', [InventoryController::class, 'storeMovement'])->name('movements.store');

        Route::get('/assets', [InventoryController::class, 'assets'])->name('assets.index');
        Route::post('/assets', [InventoryController::class, 'storeAsset'])->name('assets.store');
        Route::post('/assets/{asset}/assign', [InventoryController::class, 'assignAsset'])->name('assets.assign');
        Route::post('/assignments/{assignment}/return', [InventoryController::class, 'returnAsset'])->name('assignments.return');
    });

    // General Affairs (GA) & Facility Requests
    Route::prefix('ga')->name('ga.')->group(function () {
        Route::get('/requests', [GeneralAffairsController::class, 'requests'])->name('requests.index');
        Route::post('/requests', [GeneralAffairsController::class, 'storeRequest'])->name('requests.store');
        Route::patch('/requests/{facilityRequest}/status', [GeneralAffairsController::class, 'updateStatus'])->name('requests.status');
    });
});
