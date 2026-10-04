<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ConsignmentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConsignmentSeeder extends Seeder
{
    /**
     * Run the database seeds for Consignment (Titip Jual).
     */
    public function run(): void
    {
        $admin = User::first();
        if ($admin) {
            auth()->login($admin);
        }

        $warehouse = Warehouse::first() ?? Warehouse::create([
            'code' => 'GUD-01',
            'name' => 'Toko & Gudang Utama',
            'is_default' => true,
            'is_active' => true,
        ]);

        $pcs = Unit::where('short_name', 'pcs')->first() ?? Unit::firstOrCreate(['name' => 'Pcs'], ['short_name' => 'pcs', 'is_active' => true]);
        $bungkus = Unit::firstOrCreate(['name' => 'Bungkus'], ['short_name' => 'bks', 'is_active' => true]);

        // 1. Kategori Khusus Titipan / Konsinyasi
        $catTitipan = Category::firstOrCreate(
            ['slug' => 'titipan-konsinyasi'],
            ['name' => 'Titipan Konsinyasi & UMKM', 'is_active' => true]
        );

        // 2. Supplier Penitip Barang
        $supplier1 = Supplier::firstOrCreate(
            ['code' => 'SUP-KSG-001'],
            [
                'name' => 'Dapur Bu Dewi (Kue Basah & Jajanan)',
                'contact_person' => 'Ibu Dewi Sartika',
                'phone' => '081234567890',
                'email' => 'dapur.budewi@gmail.com',
                'address' => 'Jl. Anggrek No. 15, RT 02/04',
                'is_active' => true,
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['code' => 'SUP-KSG-002'],
            [
                'name' => 'Keripik Mbak Sri (Oleh-oleh Tradisional)',
                'contact_person' => 'Mbak Sri Wahyuni',
                'phone' => '082198765432',
                'email' => 'keripik.mbaksri@gmail.com',
                'address' => 'Jl. Merpati Blok C4 No. 12',
                'is_active' => true,
            ]
        );

        // 3. Produk Konsinyasi Supplier 1
        $pLemper = Product::firstOrCreate(
            ['code' => 'KSG-LMP-01'],
            [
                'name' => 'Lemper Ayam Bakar Spesial (Bu Dewi)',
                'slug' => Str::slug('Lemper Ayam Bakar Spesial Bu Dewi'),
                'category_id' => $catTitipan->id,
                'base_unit_id' => $pcs->id,
                'product_type' => 'product',
                'purchase_price' => 3200,
                'selling_price' => 4000,
                'min_stock' => 5,
                'is_active' => true,
                'is_consignment' => true,
                'consignment_supplier_id' => $supplier1->id,
                'consignment_type' => 'fixed_cost',
                'consignment_rate' => 3200,
            ]
        );

        $pRisol = Product::firstOrCreate(
            ['code' => 'KSG-RSL-01'],
            [
                'name' => 'Risol Mayo Smoked Beef & Keju (Bu Dewi)',
                'slug' => Str::slug('Risol Mayo Smoked Beef Keju Bu Dewi'),
                'category_id' => $catTitipan->id,
                'base_unit_id' => $pcs->id,
                'product_type' => 'product',
                'purchase_price' => 4000,
                'selling_price' => 5000,
                'min_stock' => 5,
                'is_active' => true,
                'is_consignment' => true,
                'consignment_supplier_id' => $supplier1->id,
                'consignment_type' => 'percentage_commission',
                'consignment_rate' => 20, // 20% komisi toko
            ]
        );

        // 4. Produk Konsinyasi Supplier 2
        $pSingkong = Product::firstOrCreate(
            ['code' => 'KSG-SKG-01'],
            [
                'name' => 'Keripik Singkong Balado 200g (Mbak Sri)',
                'slug' => Str::slug('Keripik Singkong Balado 200g Mbak Sri'),
                'category_id' => $catTitipan->id,
                'base_unit_id' => $bungkus->id,
                'product_type' => 'product',
                'purchase_price' => 14000,
                'selling_price' => 18000,
                'min_stock' => 3,
                'is_active' => true,
                'is_consignment' => true,
                'consignment_supplier_id' => $supplier2->id,
                'consignment_type' => 'fixed_cost',
                'consignment_rate' => 14000,
            ]
        );

        $pPisang = Product::firstOrCreate(
            ['code' => 'KSG-PSG-01'],
            [
                'name' => 'Keripik Pisang Coklat Lumer 150g (Mbak Sri)',
                'slug' => Str::slug('Keripik Pisang Coklat Lumer 150g Mbak Sri'),
                'category_id' => $catTitipan->id,
                'base_unit_id' => $bungkus->id,
                'product_type' => 'product',
                'purchase_price' => 16000,
                'selling_price' => 20000,
                'min_stock' => 3,
                'is_active' => true,
                'is_consignment' => true,
                'consignment_supplier_id' => $supplier2->id,
                'consignment_type' => 'percentage_commission',
                'consignment_rate' => 20,
            ]
        );

        // 5. Buat Penerimaan Barang Titipan Perdana (Consignment Receipt)
        $consignmentService = app(ConsignmentService::class);

        // Penerimaan dari Supplier 1
        $consignmentService->processConsignmentReceipt([
            'supplier_id' => $supplier1->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->toDateString(),
            'notes' => 'Penerimaan kue basah titip jual perdana',
            'items' => [
                [
                    'product_id' => $pLemper->id,
                    'unit_id' => $pcs->id,
                    'quantity' => 25,
                    'consignment_cost' => 3200,
                    'notes' => 'Titipan lemper hangat pagi hari',
                ],
                [
                    'product_id' => $pRisol->id,
                    'unit_id' => $pcs->id,
                    'quantity' => 30,
                    'consignment_cost' => 4000,
                    'notes' => 'Titipan risol mayo',
                ],
            ],
        ]);

        // Penerimaan dari Supplier 2
        $consignmentService->processConsignmentReceipt([
            'supplier_id' => $supplier2->id,
            'warehouse_id' => $warehouse->id,
            'receipt_date' => now()->subDay()->toDateString(),
            'notes' => 'Penerimaan oleh-oleh keripik kemasan',
            'items' => [
                [
                    'product_id' => $pSingkong->id,
                    'unit_id' => $bungkus->id,
                    'quantity' => 20,
                    'consignment_cost' => 14000,
                    'notes' => 'Kemasan standing pouch 200g',
                ],
                [
                    'product_id' => $pPisang->id,
                    'unit_id' => $bungkus->id,
                    'quantity' => 15,
                    'consignment_cost' => 16000,
                    'notes' => 'Kemasan foil 150g coklat lumer',
                ],
            ],
        ]);
    }
}
