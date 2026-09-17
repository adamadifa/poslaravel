<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class FinancialAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kas Laci Utama Kasir
        Account::firstOrCreate(
            ['account_code' => 'KAS-01'],
            [
                'name' => 'Kas Laci Kasir Utama',
                'type' => 'cash',
                'opening_balance' => 1000000,
                'current_balance' => 1000000,
                'alert_minimum_balance' => 200000,
                'is_default' => true,
                'is_active' => true,
                'description' => 'Uang kas fisik di laci kasir untuk uang kembalian dan transaksi kasir.',
            ]
        );

        // 2. Rekening Bank Operasional BCA
        Account::firstOrCreate(
            ['account_code' => 'BNK-BCA-01'],
            [
                'name' => 'BCA Rekening Operasional',
                'type' => 'bank',
                'bank_name' => 'BCA',
                'account_number' => '8210982341',
                'account_holder' => 'Toko Retail Pro',
                'opening_balance' => 15000000,
                'current_balance' => 15000000,
                'alert_minimum_balance' => 2000000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Rekening bank operasional utama penerimaan transfer / QRIS toko.',
            ]
        );

        // 3. Rekening Khusus Agen BRILink / EDC Agen
        Account::firstOrCreate(
            ['account_code' => 'AGN-BRI-01'],
            [
                'name' => 'EDC Agen BRILink Toko',
                'type' => 'bank_agent',
                'bank_name' => 'Bank BRI',
                'account_number' => '012901002345501',
                'account_holder' => 'Agen BRILink Toko Pro',
                'opening_balance' => 8500000,
                'current_balance' => 8500000,
                'alert_minimum_balance' => 1000000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Rekening terhubung mesin EDC BRILink untuk melayani transfer dan tarik tunai perbankan nasabah.',
            ]
        );

        // 4. Saldo Deposit PPOB (Digiflazz / Mitra)
        Account::firstOrCreate(
            ['account_code' => 'PPOB-DIGI-01'],
            [
                'name' => 'Deposit Server PPOB Digiflazz',
                'type' => 'ppob_provider',
                'bank_name' => 'Digiflazz PPOB',
                'account_number' => 'DIGI-PRO-892',
                'account_holder' => 'RetailPro PPOB',
                'opening_balance' => 2500000,
                'current_balance' => 2500000,
                'alert_minimum_balance' => 300000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Deposit server pulsa, paket data internet, dan token listrik PLN.',
            ]
        );
    }
}
