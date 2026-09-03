<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'account_code' => 'ACC-1001',
                'name' => 'Kas Kasir Utama',
                'type' => 'cash',
                'account_number' => null,
                'bank_name' => null,
                'opening_balance' => 1000000,
                'current_balance' => 1000000,
                'is_default' => true,
                'is_active' => true,
                'description' => 'Kas laci utama untuk transaksi operasional POS kasir',
            ],
            [
                'account_code' => 'ACC-1002',
                'name' => 'Kas Operasional Kantor',
                'type' => 'cash',
                'account_number' => null,
                'bank_name' => null,
                'opening_balance' => 2500000,
                'current_balance' => 2500000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Kas kecil untuk biaya operasional harian & belanja rutin',
            ],
            [
                'account_code' => 'ACC-2001',
                'name' => 'Bank BCA - Rekening Utama',
                'type' => 'bank',
                'account_number' => '8291029381',
                'bank_name' => 'BCA',
                'opening_balance' => 25000000,
                'current_balance' => 25000000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Rekening penerimaan transfer dan pembayaran supplier',
            ],
            [
                'account_code' => 'ACC-2002',
                'name' => 'Bank Mandiri - Bisnis',
                'type' => 'bank',
                'account_number' => '1320098472918',
                'bank_name' => 'Mandiri',
                'opening_balance' => 15000000,
                'current_balance' => 15000000,
                'is_default' => false,
                'is_active' => true,
                'description' => 'Rekening cadangan operasional dan payroll',
            ],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(
                ['account_code' => $acc['account_code']],
                $acc
            );
        }
    }
}
