<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashierShift;
use App\Models\CashierShiftExpense;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Warehouse $warehouse;

    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->cashier = User::where('email', 'admin@pospro.com')->first();

        $this->warehouse = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Cabang Pusat',
            'is_active' => true,
        ]);

        $this->account = Account::create([
            'account_code' => 'KAS-01',
            'name' => 'Kas Laci POS',
            'type' => 'cash',
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_cashier_can_open_shift(): void
    {
        $response = $this->actingAs($this->cashier)
            ->postJson(route('shifts.open'), [
                'warehouse_id' => $this->warehouse->id,
                'starting_cash' => 200000,
                'notes' => 'Shift Pagi',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.starting_cash', '200000.00')
            ->assertJsonPath('data.expected_cash', '200000.00');

        $this->assertDatabaseHas('cashier_shifts', [
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'starting_cash' => 200000,
            'status' => 'open',
        ]);
    }

    public function test_cashier_can_record_shift_expense(): void
    {
        $shift = CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'starting_cash' => 200000,
            'expected_cash' => 200000,
            'total_sales' => 0,
            'total_expenses' => 0,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->cashier)
            ->postJson(route('shifts.expenses.add', $shift), [
                'amount' => 25000,
                'category' => 'Operasional Toko',
                'notes' => 'Beli kantong plastik & air galon',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_expenses', '25000.00')
            ->assertJsonPath('data.expected_cash', '175000.00');

        $this->assertDatabaseHas('cashier_shift_expenses', [
            'cashier_shift_id' => $shift->id,
            'amount' => 25000,
            'category' => 'Operasional Toko',
            'notes' => 'Beli kantong plastik & air galon',
        ]);

        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'total_expenses' => 25000,
            'expected_cash' => 175000,
        ]);
    }

    public function test_cashier_can_delete_shift_expense(): void
    {
        $shift = CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'starting_cash' => 200000,
            'expected_cash' => 150000,
            'total_sales' => 0,
            'total_expenses' => 50000,
            'status' => 'open',
        ]);

        $expense = CashierShiftExpense::create([
            'cashier_shift_id' => $shift->id,
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'amount' => 50000,
            'category' => 'Bensin & Transport',
            'notes' => 'Bensin motor kurir',
            'expense_date' => now(),
        ]);

        $response = $this->actingAs($this->cashier)
            ->deleteJson(route('shifts.expenses.delete', ['shift' => $shift->id, 'expense' => $expense->id]));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_expenses', '0.00')
            ->assertJsonPath('data.expected_cash', '200000.00');

        $this->assertDatabaseMissing('cashier_shift_expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_cashier_can_close_shift_with_expenses_and_selisih(): void
    {
        $shift = CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'starting_cash' => 200000,
            'expected_cash' => 275000, // 200k modal + 100k sales - 25k expenses = 275k
            'total_sales' => 100000,
            'total_expenses' => 25000,
            'status' => 'open',
        ]);

        // Kasir menghitung fisik laci kas dan menemukan Rp 275.000 (Pas)
        $response = $this->actingAs($this->cashier)
            ->postJson(route('shifts.close', $shift), [
                'closing_cash' => 275000,
                'notes' => 'Kas sesuai fisik',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.cash_difference', '0.00')
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'status' => 'closed',
            'closing_cash' => 275000,
            'cash_difference' => 0,
        ]);
    }

    public function test_cashier_shifts_report_displays_total_expenses(): void
    {
        $shift = CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'closed_at' => now(),
            'starting_cash' => 200000,
            'expected_cash' => 275000,
            'total_sales' => 100000,
            'total_expenses' => 25000,
            'closing_cash' => 275000,
            'cash_difference' => 0,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->cashier)
            ->get(route('reports.cashier-shifts'));

        $response->assertOk()
            ->assertSee('Total Biaya / Kas Keluar')
            ->assertSee('25.000');
    }

    public function test_cashier_shifts_excel_and_pdf_export(): void
    {
        CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'closed_at' => now(),
            'starting_cash' => 200000,
            'expected_cash' => 275000,
            'total_sales' => 100000,
            'total_expenses' => 25000,
            'closing_cash' => 275000,
            'cash_difference' => 0,
            'status' => 'closed',
        ]);

        $excelResponse = $this->actingAs($this->cashier)
            ->get(route('reports.cashier-shifts.export-excel'));
        $excelResponse->assertOk();

        $pdfResponse = $this->actingAs($this->cashier)
            ->get(route('reports.cashier-shifts.export-pdf'));
        $pdfResponse->assertOk();
    }
}
