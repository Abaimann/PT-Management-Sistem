<?php

namespace Tests\Feature;

use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalLine;
use App\Models\Finance\Reimbursement;
use App\Models\HRIS\Employee;
use App\Services\Finance\AccountingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class FinanceAndAccountingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $financeUser;
    protected User $employeeUser;
    protected Employee $employee;
    protected ChartOfAccount $cashAccount;
    protected ChartOfAccount $expenseAccount;
    protected ChartOfAccount $revenueAccount;
    protected AccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->accountingService = app(AccountingService::class);

        // Ensure roles exist
        $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $financeRole = Role::firstOrCreate(['code' => 'finance_officer'], ['name' => 'Finance Officer']);
        $employeeRole = Role::firstOrCreate(['code' => 'employee'], ['name' => 'Karyawan']);

        // Finance user
        $this->financeUser = User::create([
            'name' => 'Finance Officer Test',
            'email' => 'fin_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->financeUser->roles()->sync([$financeRole->id]);

        // Regular employee user
        $this->employeeUser = User::create([
            'name' => 'Employee Test',
            'email' => 'emp_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->employeeUser->roles()->sync([$employeeRole->id]);

        // Department & Position
        $dept = Department::firstOrCreate(
            ['code' => 'FIN-TEST'],
            ['name' => 'Finance Testing', 'is_active' => true]
        );
        $pos = Position::firstOrCreate(
            ['department_id' => $dept->id, 'name' => 'Accounting Staff'],
            ['grade_level' => 'Staff', 'is_active' => true]
        );

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'nik' => 'EMP-'.rand(10000, 99999),
            'full_name' => 'Budi Santoso Staff',
            'gender' => 'MALE',
            'birth_date' => '1995-05-15',
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2023-01-01',
            'is_active' => true,
        ]);

        // Standard accounts setup
        $this->cashAccount = ChartOfAccount::firstOrCreate(
            ['account_code' => '1101'],
            [
                'account_name' => 'Kas Operasional',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
            ]
        );

        $this->expenseAccount = ChartOfAccount::firstOrCreate(
            ['account_code' => '5104'],
            [
                'account_name' => 'Beban Reimbursement & Klaim Pengeluaran',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
            ]
        );

        $this->revenueAccount = ChartOfAccount::firstOrCreate(
            ['account_code' => '4101'],
            [
                'account_name' => 'Pendapatan Kontrak Software ERP',
                'classification' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
            ]
        );
    }

    public function test_can_access_finance_dashboard(): void
    {
        $response = $this->actingAs($this->financeUser)->get(route('finance.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Keuangan &amp; Akuntansi', false);
        $response->assertSee('Total Kas &amp; Bank', false);
    }

    public function test_can_view_chart_of_accounts(): void
    {
        $response = $this->actingAs($this->financeUser)->get(route('finance.accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Chart of Accounts');
        $response->assertSee('1101');
        $response->assertSee('Kas Operasional');
    }

    public function test_can_store_new_chart_of_account(): void
    {
        $newCode = '5199';
        $response = $this->actingAs($this->financeUser)->post(route('finance.accounts.store'), [
            'account_code' => $newCode,
            'account_name' => 'Beban Riset dan Eksperimen',
            'classification' => 'EXPENSE',
            'normal_balance' => 'DEBIT',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('finance.accounts.index'));
        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => $newCode,
            'account_name' => 'Beban Riset dan Eksperimen',
        ]);
    }

    public function test_can_view_journals_index_and_create_page(): void
    {
        $indexResponse = $this->actingAs($this->financeUser)->get(route('finance.journals.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Jurnal Transaksi Umum');

        $createResponse = $this->actingAs($this->financeUser)->get(route('finance.journals.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Buat Ayat Jurnal Umum');
    }

    public function test_can_create_and_post_balanced_journal(): void
    {
        $amount = 1500000.00;

        $response = $this->actingAs($this->financeUser)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Pembayaran Jasa Konsultasi Pajak',
            'action' => 'post',
            'lines' => [
                [
                    'chart_of_account_id' => $this->expenseAccount->id,
                    'description' => 'Debet Beban',
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'description' => 'Kredit Kas',
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ],
        ]);

        $response->assertRedirect(route('finance.journals.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Pembayaran Jasa Konsultasi Pajak',
            'status' => 'POSTED',
        ]);

        $journal = JournalEntry::where('description', 'Pembayaran Jasa Konsultasi Pajak')->first();
        $this->assertNotNull($journal);
        $this->assertCount(2, $journal->lines);
        $this->assertEquals($amount, (float) $journal->lines->sum('debit'));
        $this->assertEquals($amount, (float) $journal->lines->sum('credit'));
        $this->assertTrue($journal->isBalanced());
    }

    public function test_unbalanced_journal_posting_is_rejected(): void
    {
        // Total Debit 500.000 vs Total Credit 400.000
        $response = $this->actingAs($this->financeUser)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Transaksi Tidak Seimbang',
            'action' => 'post',
            'lines' => [
                [
                    'chart_of_account_id' => $this->expenseAccount->id,
                    'debit' => 500000,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => 400000,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('journal_entries', [
            'description' => 'Transaksi Tidak Seimbang',
            'status' => 'POSTED',
        ]);
    }

    public function test_journal_with_negative_values_is_rejected(): void
    {
        $response = $this->actingAs($this->financeUser)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Transaksi Nilai Negatif',
            'action' => 'post',
            'lines' => [
                [
                    'chart_of_account_id' => $this->expenseAccount->id,
                    'debit' => -200000,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => -200000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['lines.0.debit', 'lines.1.credit']);
    }

    public function test_draft_journal_can_be_saved_and_posted_later(): void
    {
        $amount = 750000.00;

        // 1. Simpan sebagai DRAFT
        $response = $this->actingAs($this->financeUser)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Draft Pembelian Perlengkapan Kantor',
            'action' => 'draft',
            'lines' => [
                [
                    'chart_of_account_id' => $this->expenseAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'chart_of_account_id' => $this->cashAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ],
        ]);

        $response->assertRedirect(route('finance.journals.index'));

        $journal = JournalEntry::where('description', 'Draft Pembelian Perlengkapan Kantor')->first();
        $this->assertNotNull($journal);
        $this->assertEquals('DRAFT', $journal->status);

        // 2. Post Draft Jurnal
        $postResponse = $this->actingAs($this->financeUser)->post(route('finance.journals.post', $journal));
        $postResponse->assertRedirect(route('finance.journals.index'));
        $postResponse->assertSessionHas('success');

        $journal->refresh();
        $this->assertEquals('POSTED', $journal->status);
        $this->assertNotNull($journal->posted_at);
    }

    public function test_general_ledger_calculates_running_balance_correctly(): void
    {
        // Buat 2 jurnal beruntun pada akun kas
        // Jurnal 1: Penerimaan pendapatan 2.000.000 (Kas Debet 2.000.000, Pendapatan Kredit 2.000.000)
        $this->accountingService->createBalancedJournal(
            description: 'Pendapatan Invoice 01',
            lines: [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 2000000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 2000000],
            ],
            status: 'POSTED',
            date: now()->subDays(2)->toDateString()
        );

        // Jurnal 2: Pengeluaran biaya 500.000 (Beban Debet 500.000, Kas Kredit 500.000)
        $this->accountingService->createBalancedJournal(
            description: 'Beban Operasional 01',
            lines: [
                ['chart_of_account_id' => $this->expenseAccount->id, 'debit' => 500000, 'credit' => 0],
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 0, 'credit' => 500000],
            ],
            status: 'POSTED',
            date: now()->subDay()->toDateString()
        );

        $response = $this->actingAs($this->financeUser)->get(route('finance.reports.general_ledger', [
            'account_id' => $this->cashAccount->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kas Operasional');
        $response->assertSee('Pendapatan Invoice 01');
        $response->assertSee('Beban Operasional 01');

        $ledger = $this->accountingService->getGeneralLedger(
            $this->cashAccount->id,
            now()->subDays(5)->toDateString(),
            now()->toDateString()
        );

        $this->assertEquals(2000000.00, $ledger['total_debit']);
        $this->assertEquals(500000.00, $ledger['total_credit']);
        $this->assertEquals(1500000.00, $ledger['closing_balance']);
    }

    public function test_trial_balance_calculates_and_reports_balanced_totals(): void
    {
        // Masukkan transaksi seimbang
        $this->accountingService->createBalancedJournal(
            description: 'Penerimaan Modal / Kas Masuk',
            lines: [
                ['chart_of_account_id' => $this->cashAccount->id, 'debit' => 10000000, 'credit' => 0],
                ['chart_of_account_id' => $this->revenueAccount->id, 'debit' => 0, 'credit' => 10000000],
            ],
            status: 'POSTED'
        );

        $response = $this->actingAs($this->financeUser)->get(route('finance.reports.trial_balance'));

        $response->assertStatus(200);
        $response->assertSee('Neraca Saldo Sempurna &amp; Berimbang', false);

        $tb = $this->accountingService->getTrialBalance();
        $this->assertTrue($tb['is_balanced']);
        $this->assertEquals(0, $tb['difference']);
    }

    public function test_employee_can_submit_reimbursement_with_receipt(): void
    {
        $fakeFile = UploadedFile::fake()->create('nota_bensin.pdf', 250, 'application/pdf');

        $response = $this->actingAs($this->employeeUser)->post(route('reimbursements.store'), [
            'title' => 'Bensin Kunjungan Klien Cikarang',
            'amount' => 175000,
            'description' => 'Isi BBM Pertamax mobil operasional',
            'receipt' => $fakeFile,
        ]);

        $response->assertRedirect(route('reimbursements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reimbursements', [
            'employee_id' => $this->employee->id,
            'title' => 'Bensin Kunjungan Klien Cikarang',
            'amount' => 175000,
            'status' => 'PENDING',
        ]);

        $reimbursement = Reimbursement::where('title', 'Bensin Kunjungan Klien Cikarang')->first();
        $this->assertNotNull($reimbursement->receipt_path);
        Storage::disk('local')->assertExists($reimbursement->receipt_path);
    }

    public function test_finance_can_approve_reimbursement_and_auto_generates_balanced_journal(): void
    {
        // 1. Buat klaim reimbursement pending
        $reimbursement = Reimbursement::create([
            'employee_id' => $this->employee->id,
            'claim_number' => 'RMB-TEST-0001',
            'title' => 'Konsumsi Lembur Deploy ERP',
            'amount' => 250000.00,
            'description' => 'Makan malam tim developer',
            'receipt_path' => 'finance/receipts/sample.pdf',
            'status' => 'PENDING',
        ]);

        // 2. Finance approve dan cairkan kas
        $response = $this->actingAs($this->financeUser)->post(route('reimbursements.approve', $reimbursement), [
            'cash_account_id' => $this->cashAccount->id,
            'expense_account_id' => $this->expenseAccount->id,
        ]);

        $response->assertRedirect(route('reimbursements.index'));
        $response->assertSessionHas('success');

        $reimbursement->refresh();
        $this->assertEquals('PAID', $reimbursement->status);
        $this->assertNotNull($reimbursement->paid_at);
        $this->assertEquals($this->financeUser->id, $reimbursement->approver_id);

        // 3. Pastikan auto-generated journal entry terbentuk dengan status POSTED dan seimbang
        $journal = JournalEntry::where('reference_type', 'Reimbursement')
            ->where('reference_id', $reimbursement->id)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals('POSTED', $journal->status);
        $this->assertTrue($journal->isBalanced());
        $this->assertEquals(250000.00, (float) $journal->lines->sum('debit'));
        $this->assertEquals(250000.00, (float) $journal->lines->sum('credit'));

        // Cek baris debet pada akun beban dan kredit pada akun kas
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->expenseAccount->id,
            'debit' => 250000.00,
            'credit' => 0.00,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $journal->id,
            'chart_of_account_id' => $this->cashAccount->id,
            'debit' => 0.00,
            'credit' => 250000.00,
        ]);
    }

    public function test_finance_can_reject_reimbursement_with_notes(): void
    {
        $reimbursement = Reimbursement::create([
            'employee_id' => $this->employee->id,
            'claim_number' => 'RMB-TEST-0002',
            'title' => 'Klaim Tidak Sah',
            'amount' => 500000.00,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->financeUser)->post(route('reimbursements.reject', $reimbursement), [
            'notes' => 'Kuitansi tidak valid dan di luar ketentuan perusahaan.',
        ]);

        $response->assertRedirect(route('reimbursements.index'));
        $response->assertSessionHas('success');

        $reimbursement->refresh();
        $this->assertEquals('REJECTED', $reimbursement->status);
        $this->assertEquals('Kuitansi tidak valid dan di luar ketentuan perusahaan.', $reimbursement->notes);
        $this->assertEquals($this->financeUser->id, $reimbursement->approver_id);
    }

    public function test_authorized_user_can_view_receipt_file(): void
    {
        $path = 'finance/receipts/valid_receipt.jpg';
        Storage::disk('local')->put($path, 'sample image content');

        $reimbursement = Reimbursement::create([
            'employee_id' => $this->employee->id,
            'claim_number' => 'RMB-TEST-0003',
            'title' => 'Nota Pembelian Printer Ink',
            'amount' => 300000.00,
            'receipt_path' => $path,
            'status' => 'PENDING',
        ]);

        // Pemilik klaim dapat melihat kuitansi
        $ownerResponse = $this->actingAs($this->employeeUser)->get(route('reimbursements.receipt', $reimbursement));
        $ownerResponse->assertStatus(200);

        // Finance officer dapat melihat kuitansi
        $financeResponse = $this->actingAs($this->financeUser)->get(route('reimbursements.receipt', $reimbursement));
        $financeResponse->assertStatus(200);
    }
}
