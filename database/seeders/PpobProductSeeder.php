<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\PpobProduct;
use Illuminate\Database\Seeder;

class PpobProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ppobAccount = Account::where('type', 'ppob_provider')->first()
            ?? Account::where('is_default', true)->first();

        $accountId = $ppobAccount?->id;

        $products = [
            // PULSA REGULER TELKOMSEL
            [
                'category' => 'pulsa',
                'provider' => 'Telkomsel',
                'code' => 'TSEL5',
                'name' => 'Telkomsel 5.000',
                'cost_price' => 5350,
                'selling_price' => 7000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Telkomsel',
                'code' => 'TSEL10',
                'name' => 'Telkomsel 10.000',
                'cost_price' => 10250,
                'selling_price' => 12500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Telkomsel',
                'code' => 'TSEL20',
                'name' => 'Telkomsel 20.000',
                'cost_price' => 20100,
                'selling_price' => 22500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Telkomsel',
                'code' => 'TSEL50',
                'name' => 'Telkomsel 50.000',
                'cost_price' => 49750,
                'selling_price' => 52500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Telkomsel',
                'code' => 'TSEL100',
                'name' => 'Telkomsel 100.000',
                'cost_price' => 97800,
                'selling_price' => 102500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],

            // PULSA INDOSAT
            [
                'category' => 'pulsa',
                'provider' => 'Indosat',
                'code' => 'ISAT10',
                'name' => 'Indosat 10.000',
                'cost_price' => 10150,
                'selling_price' => 12500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Indosat',
                'code' => 'ISAT25',
                'name' => 'Indosat 25.000',
                'cost_price' => 24800,
                'selling_price' => 27500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'Indosat',
                'code' => 'ISAT50',
                'name' => 'Indosat 50.000',
                'cost_price' => 49500,
                'selling_price' => 52500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],

            // PULSA XL & AXIS
            [
                'category' => 'pulsa',
                'provider' => 'XL Axiata',
                'code' => 'XL10',
                'name' => 'XL 10.000',
                'cost_price' => 10150,
                'selling_price' => 12500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'pulsa',
                'provider' => 'XL Axiata',
                'code' => 'XL25',
                'name' => 'XL 25.000',
                'cost_price' => 24750,
                'selling_price' => 27500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],

            // TOKEN LISTRIK PLN
            [
                'category' => 'token_pln',
                'provider' => 'PLN Prepaid',
                'code' => 'PLN20',
                'name' => 'Token PLN 20.000',
                'cost_price' => 20100,
                'selling_price' => 23000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'token_pln',
                'provider' => 'PLN Prepaid',
                'code' => 'PLN50',
                'name' => 'Token PLN 50.000',
                'cost_price' => 50100,
                'selling_price' => 53000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'token_pln',
                'provider' => 'PLN Prepaid',
                'code' => 'PLN100',
                'name' => 'Token PLN 100.000',
                'cost_price' => 100100,
                'selling_price' => 103000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],

            // TOP UP E-WALLET (DANA, GOPAY, OVO, SHOPEEPAY)
            [
                'category' => 'ewallet',
                'provider' => 'DANA',
                'code' => 'DANA20',
                'name' => 'Saldo DANA 20.000',
                'cost_price' => 20300,
                'selling_price' => 22500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'ewallet',
                'provider' => 'DANA',
                'code' => 'DANA50',
                'name' => 'Saldo DANA 50.000',
                'cost_price' => 50300,
                'selling_price' => 52500,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'ewallet',
                'provider' => 'DANA',
                'code' => 'DANA100',
                'name' => 'Saldo DANA 100.000',
                'cost_price' => 100300,
                'selling_price' => 103000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'ewallet',
                'provider' => 'GoPay',
                'code' => 'GOPAY50',
                'name' => 'Saldo GoPay 50.000',
                'cost_price' => 50500,
                'selling_price' => 53000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
            [
                'category' => 'ewallet',
                'provider' => 'ShopeePay',
                'code' => 'SPAY50',
                'name' => 'Saldo ShopeePay 50.000',
                'cost_price' => 50500,
                'selling_price' => 53000,
                'default_account_id' => $accountId,
                'is_active' => true,
            ],
        ];

        foreach ($products as $prod) {
            PpobProduct::updateOrCreate(
                ['code' => $prod['code']],
                $prod
            );
        }
    }
}
