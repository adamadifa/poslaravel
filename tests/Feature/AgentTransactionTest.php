<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CashierShift;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Warehouse $warehouse;

    protected Account $cashAccount;

    protected Account $bankAgentAccount;

    protected Account $ppobAccount;

    protected CashierShift $activeShift;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->cashier = User::where('email', 'admin@pospro.com')->first();

        $this->warehouse = Warehouse::create([
            'code' => 'WH-TEST',
            'name' => 'Cabang Retail & Agen',
            'is_active' => true,
        ]);

        $this->cashAccount = Account::create([
            'account_code' => 'KAS-LACI',
            'name' => 'Kas Laci Kasir',
            'type' => 'cash',
            'opening_balance' => 500000,
            'current_balance' => 500000,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->bankAgentAccount = Account::create([
            'account_code' => 'EDC-BRI',
            'name' => 'Rekening Agen BRILink',
            'type' => 'bank_agent',
            'bank_name' => 'Bank BRI',
            'account_number' => '01234567890',
            'opening_balance' => 10000000,
            'current_balance' => 10000000,
            'is_active' => true,
        ]);

        $this->ppobAccount = Account::create([
            'account_code' => 'DEP-DIGI',
            'name' => 'Deposit PPOB Digiflazz',
            'type' => 'ppob_provider',
            'opening_balance' => 1000000,
            'current_balance' => 1000000,
            'is_active' => true,
        ]);

        // Open Cashier Shift with Modal Awal Rp 500.000
        $this->activeShift = CashierShift::create([
            'user_id' => $this->cashier->id,
            'warehouse_id' => $this->warehouse->id,
            'opened_at' => now(),
            'starting_cash' => 500000,
            'expected_cash' => 500000,
            'status' => 'open',
        ]);
    }

    public function test_can_fetch_agent_balances_summary(): void
    {
        $response = $this->actingAs($this->cashier)
            ->getJson(route('agent.balances'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_bank_agent_balance', 10000000)
            ->assertJsonPath('data.total_ppob_balance', 1000000);
    }

    public function test_bank_transfer_deducts_bank_balance_and_increases_cash_drawer(): void
    {
        // Pelanggan transfer Rp 500.000, bayar admin fee Rp 10.000, cost bank Rp 3.000
        // Total diterima kasir di laci = 500.000 + 10.000 = 510.000
        // Saldo rekening bank berkurang = 500.000 + 3.000 = 503.000
        // Laba bersih = 10.000 - 3.000 = 7.000
        $response = $this->actingAs($this->cashier)
            ->postJson(route('agent.transfer'), [
                'account_id' => $this->bankAgentAccount->id,
                'destination_target' => 'BCA 123456789',
                'destination_holder' => 'Bpk. Ahmad Santoso',
                'principal_amount' => 500000,
                'admin_fee' => 10000,
                'cost_price' => 3000,
                'reference_number' => 'REF-EDC-991',
                'notes' => 'Transfer Kilat',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
        $this->assertEquals(7000, (float) $response->json('data.net_profit'));

        // Assert bank balance deducted
        $this->bankAgentAccount->refresh();
        $this->assertEquals(10000000 - 503000, (float) $this->bankAgentAccount->current_balance);

        // Assert shift updated (cash in +510.000, profit +7.000, expected cash 500.000 + 510.000 = 1.010.000)
        $this->activeShift->refresh();
        $this->assertEquals(510000, (float) $this->activeShift->total_agent_cash_in);
        $this->assertEquals(7000, (float) $this->activeShift->total_agent_profit);
        $this->assertEquals(1010000, (float) $this->activeShift->expected_cash);

        // Assert account mutation created
        $this->assertDatabaseHas('account_mutations', [
            'account_id' => $this->bankAgentAccount->id,
            'mutation_type' => 'debit',
            'amount' => 503000,
        ]);
    }

    public function test_cash_withdrawal_deducts_cash_drawer_and_increases_bank_balance(): void
    {
        // Nasabah tarik tunai Rp 200.000, fee Rp 5.000 dipotong dari saldo yang digesek (sehingga masuk ke bank 205.000)
        // Kas fisik kasir berkurang Rp 200.000
        // Saldo rekening agen bertambah Rp 205.000
        // Laba bersih Rp 5.000
        $response = $this->actingAs($this->cashier)
            ->postJson(route('agent.withdraw'), [
                'account_id' => $this->bankAgentAccount->id,
                'principal_amount' => 200000,
                'admin_fee' => 5000,
                'payment_method' => 'deduct_balance',
                'destination_holder' => 'Ibu Siti Aminah',
                'reference_number' => 'WITHDRAW-002',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
        $this->assertEquals(5000, (float) $response->json('data.net_profit'));

        // Assert bank balance increased by 205.000
        $this->bankAgentAccount->refresh();
        $this->assertEquals(10000000 + 205000, (float) $this->bankAgentAccount->current_balance);

        // Assert cash drawer updated (expected cash = 500.000 - 200.000 = 300.000)
        $this->activeShift->refresh();
        $this->assertEquals(200000, (float) $this->activeShift->total_agent_cash_out);
        $this->assertEquals(5000, (float) $this->activeShift->total_agent_profit);
        $this->assertEquals(300000, (float) $this->activeShift->expected_cash);

        // Assert account mutation created
        $this->assertDatabaseHas('account_mutations', [
            'account_id' => $this->bankAgentAccount->id,
            'mutation_type' => 'credit',
            'amount' => 205000,
        ]);
    }

    public function test_cash_withdrawal_fails_if_drawer_cash_insufficient(): void
    {
        // Kas laci modal 500.000, coba tarik tunai 1.000.000
        $response = $this->actingAs($this->cashier)
            ->postJson(route('agent.withdraw'), [
                'account_id' => $this->bankAgentAccount->id,
                'principal_amount' => 1000000,
                'admin_fee' => 10000,
                'payment_method' => 'deduct_balance',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // Drawer cash unchanged
        $this->activeShift->refresh();
        $this->assertEquals(500000, (float) $this->activeShift->expected_cash);
    }

    public function test_ppob_transaction_deducts_provider_deposit_and_records_profit(): void
    {
        // Beli Token PLN 50.000 (Modal 50.500, Jual 53.000, Bayar Tunai)
        // Saldo deposit PPOB berkurang 50.500
        // Kas laci kasir bertambah 53.000
        // Laba bersih = 53.000 - 50.500 = 2.500
        $response = $this->actingAs($this->cashier)
            ->postJson(route('agent.ppob'), [
                'account_id' => $this->ppobAccount->id,
                'service_type' => 'token_pln',
                'destination_target' => '54321098765',
                'cost_price' => 50500,
                'selling_price' => 53000,
                'payment_method' => 'cash',
                'reference_number' => '1234-5678-9012-3456-7890',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
        $this->assertEquals(2500, (float) $response->json('data.net_profit'));

        // Assert deposit balance deducted
        $this->ppobAccount->refresh();
        $this->assertEquals(1000000 - 50500, (float) $this->ppobAccount->current_balance);

        // Assert drawer cash updated (500.000 + 53.000 = 553.000)
        $this->activeShift->refresh();
        $this->assertEquals(53000, (float) $this->activeShift->total_agent_cash_in);
        $this->assertEquals(2500, (float) $this->activeShift->total_agent_profit);
        $this->assertEquals(553000, (float) $this->activeShift->expected_cash);
    }

    public function test_ppob_fails_if_provider_balance_is_insufficient(): void
    {
        // Deposit hanya 1.000.000, transaksi modal 1.500.000
        $response = $this->actingAs($this->cashier)
            ->postJson(route('agent.ppob'), [
                'account_id' => $this->ppobAccount->id,
                'service_type' => 'pulsa',
                'destination_target' => '081234567890',
                'cost_price' => 1500000,
                'selling_price' => 1550000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->ppobAccount->refresh();
        $this->assertEquals(1000000, (float) $this->ppobAccount->current_balance);
    }

    public function test_close_shift_with_combined_retail_and_agent_transactions(): void
    {
        // 1. Catat transfer masuk: kas in +205.000, profit +5.000
        $resTrf = $this->actingAs($this->cashier)->postJson(route('agent.transfer'), [
            'account_id' => $this->bankAgentAccount->id,
            'destination_target' => 'BRI 998877',
            'principal_amount' => 200000,
            'admin_fee' => 5000,
        ]);
        $resTrf->assertOk();

        // 2. Catat tarik tunai: kas out -100.000, profit +3.000
        $resWdr = $this->actingAs($this->cashier)->postJson(route('agent.withdraw'), [
            'account_id' => $this->bankAgentAccount->id,
            'principal_amount' => 100000,
            'admin_fee' => 3000,
            'payment_method' => 'deduct_balance',
        ]);
        $resWdr->assertOk();

        // Expected Cash sekarang: 500.000 (awal) + 205.000 (transfer) - 100.000 (tarik) = 605.000
        $this->activeShift->refresh();
        $this->assertEquals(605000, (float) $this->activeShift->expected_cash);

        // 3. Kasir hitung fisik laci Rp 605.000 (pas 100%)
        $closeResponse = $this->actingAs($this->cashier)
            ->postJson(route('shifts.close', $this->activeShift->id), [
                'closing_cash' => 605000,
                'notes' => 'Shift selesai pas seimbang',
            ]);

        $closeResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.cash_difference', '0.00')
            ->assertJsonPath('data.total_agent_profit', '8000.00');

        $this->activeShift->refresh();
        $this->assertEquals('closed', $this->activeShift->status);
        $this->assertEquals(0, (float) $this->activeShift->cash_difference);
        $this->assertEquals(8000, (float) $this->activeShift->total_agent_profit);
    }
}
