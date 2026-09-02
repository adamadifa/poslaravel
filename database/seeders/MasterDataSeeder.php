<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\TieredPrice;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Satuan (Units)
        $pcs = Unit::firstOrCreate(['name' => 'Pcs'], ['short_name' => 'pcs', 'is_active' => true]);
        $pak = Unit::firstOrCreate(['name' => 'Pak'], ['short_name' => 'pak', 'is_active' => true]);
        $renceng = Unit::firstOrCreate(['name' => 'Renceng'], ['short_name' => 'rcg', 'is_active' => true]);
        $dus = Unit::firstOrCreate(['name' => 'Dus'], ['short_name' => 'dus', 'is_active' => true]);
        $karton = Unit::firstOrCreate(['name' => 'Karton'], ['short_name' => 'krt', 'is_active' => true]);
        $botol = Unit::firstOrCreate(['name' => 'Botol'], ['short_name' => 'btl', 'is_active' => true]);
        $liter = Unit::firstOrCreate(['name' => 'Liter'], ['short_name' => 'ltr', 'is_active' => true]);
        $kg = Unit::firstOrCreate(['name' => 'Kilogram'], ['short_name' => 'kg', 'is_active' => true]);

        // 2. Kategori Produk
        $makanan = Category::firstOrCreate(['name' => 'Makanan & Snack'], ['slug' => 'makanan-snack', 'is_active' => true]);
        $mieInstant = Category::firstOrCreate(['name' => 'Mie Instant', 'parent_id' => $makanan->id], ['slug' => 'mie-instant', 'is_active' => true]);
        $snackRingan = Category::firstOrCreate(['name' => 'Snack Ringan', 'parent_id' => $makanan->id], ['slug' => 'snack-ringan', 'is_active' => true]);

        $minuman = Category::firstOrCreate(['name' => 'Minuman'], ['slug' => 'minuman', 'is_active' => true]);
        $airMineral = Category::firstOrCreate(['name' => 'Air Mineral', 'parent_id' => $minuman->id], ['slug' => 'air-mineral', 'is_active' => true]);
        $susu = Category::firstOrCreate(['name' => 'Susu & Olahan', 'parent_id' => $minuman->id], ['slug' => 'susu-olahan', 'is_active' => true]);

        $sembako = Category::firstOrCreate(['name' => 'Sembako & Bumbu'], ['slug' => 'sembako-bumbu', 'is_active' => true]);

        // 3. Customer Groups
        $groupUmum = CustomerGroup::firstOrCreate(['name' => 'Umum (Retail)'], ['discount_percent' => 0, 'description' => 'Pelanggan langsung umum']);
        $groupMember = CustomerGroup::firstOrCreate(['name' => 'Member'], ['discount_percent' => 2.00, 'description' => 'Pelanggan terdaftar']);
        $groupReseller = CustomerGroup::firstOrCreate(['name' => 'Reseller'], ['discount_percent' => 5.00, 'description' => 'Mitra reseller']);
        $groupGrosir = CustomerGroup::firstOrCreate(['name' => 'Grosir'], ['discount_percent' => 8.00, 'description' => 'Pembeli partai besar']);

        // 4. Customers
        Customer::firstOrCreate(
            ['code' => 'CUST-001'],
            [
                'name' => 'Pelanggan Umum',
                'customer_group_id' => $groupUmum->id,
                'phone' => '-',
                'is_active' => true,
            ]
        );

        Customer::firstOrCreate(
            ['code' => 'CUST-002'],
            [
                'name' => 'Toko Berkah (Budi)',
                'customer_group_id' => $groupGrosir->id,
                'phone' => '081234567890',
                'credit_limit' => 5000000,
                'is_active' => true,
            ]
        );

        // 5. Suppliers
        $supIndofood = Supplier::firstOrCreate(
            ['code' => 'SUP-001'],
            [
                'name' => 'PT Indofood CBP Sukses Makmur',
                'contact_person' => 'Hendra',
                'phone' => '021-5551234',
                'payment_term_days' => 30,
                'is_active' => true,
            ]
        );

        $supDanone = Supplier::firstOrCreate(
            ['code' => 'SUP-002'],
            [
                'name' => 'PT Tirta Investama (Danone Aqua)',
                'contact_person' => 'Sari',
                'phone' => '021-5555678',
                'payment_term_days' => 14,
                'is_active' => true,
            ]
        );

        // 6. Warehouses (Multi-Cabang)
        $whJakarta = Warehouse::firstOrCreate(
            ['code' => 'WH-JKT'],
            [
                'name' => 'Cabang Utama Jakarta',
                'address' => 'Jl. Sudirman No. 45, Jakarta Pusat',
                'phone' => '021-123456',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $whSurabaya = Warehouse::firstOrCreate(
            ['code' => 'WH-SBY'],
            [
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Pemuda No. 12, Surabaya',
                'phone' => '031-654321',
                'is_default' => false,
                'is_active' => true,
            ]
        );

        // 7. Master Produk Contoh Lengkap (Indomie Goreng)
        $indomie = Product::firstOrCreate(
            ['code' => 'PRD-0001'],
            [
                'category_id' => $mieInstant->id,
                'base_unit_id' => $pcs->id,
                'barcode' => '8992770001',
                'name' => 'Indomie Goreng Spesial 85g',
                'slug' => 'indomie-goreng-spesial-85g',
                'brand' => 'Indofood',
                'purchase_price' => 2800,
                'selling_price' => 3500,
                'min_stock' => 50,
                'is_active' => true,
            ]
        );

        // Multi-Barcode Indomie (Barcode Pcs & Barcode Karton)
        ProductBarcode::firstOrCreate(['barcode' => '8992770001'], ['product_id' => $indomie->id, 'unit_id' => $pcs->id, 'is_primary' => true]);
        ProductBarcode::firstOrCreate(['barcode' => '8992770040'], ['product_id' => $indomie->id, 'unit_id' => $karton->id, 'is_primary' => false]);

        // Konversi Satuan Indomie (1 Karton = 40 Pcs, 1 Pak = 5 Pcs)
        UnitConversion::firstOrCreate(['product_id' => $indomie->id, 'from_unit_id' => $karton->id, 'to_unit_id' => $pcs->id], ['conversion_value' => 40]);
        UnitConversion::firstOrCreate(['product_id' => $indomie->id, 'from_unit_id' => $pak->id, 'to_unit_id' => $pcs->id], ['conversion_value' => 5]);

        // Daftar Harga per Satuan Indomie
        PriceList::firstOrCreate(['product_id' => $indomie->id, 'unit_id' => $pcs->id], ['purchase_price' => 2800, 'selling_price' => 3500]);
        PriceList::firstOrCreate(['product_id' => $indomie->id, 'unit_id' => $pak->id], ['purchase_price' => 13500, 'selling_price' => 16500]);
        PriceList::firstOrCreate(['product_id' => $indomie->id, 'unit_id' => $karton->id], ['purchase_price' => 105000, 'selling_price' => 125000]);

        // Stok Indomie di Cabang Utama
        ProductStock::firstOrCreate(['product_id' => $indomie->id, 'warehouse_id' => $whJakarta->id], ['quantity' => 240]);

        // 8. Master Produk Contoh 2: Aqua 600ml (Tiered Pricing)
        $aqua = Product::firstOrCreate(
            ['code' => 'PRD-0002'],
            [
                'category_id' => $airMineral->id,
                'base_unit_id' => $botol->id,
                'barcode' => '8886008101',
                'name' => 'Aqua Air Mineral 600ml',
                'slug' => 'aqua-air-mineral-600ml',
                'brand' => 'Danone',
                'purchase_price' => 2200,
                'selling_price' => 3000,
                'min_stock' => 24,
                'is_active' => true,
            ]
        );

        // Harga Berjenjang Aqua (Beli 24+ Botol dapat harga Rp 2.500)
        TieredPrice::firstOrCreate(
            ['product_id' => $aqua->id, 'unit_id' => $botol->id, 'min_qty' => 1, 'max_qty' => 23],
            ['price' => 3000, 'is_active' => true]
        );
        TieredPrice::firstOrCreate(
            ['product_id' => $aqua->id, 'unit_id' => $botol->id, 'min_qty' => 24, 'max_qty' => null],
            ['price' => 2500, 'is_active' => true]
        );

        // Stok Aqua di Cabang Utama
        ProductStock::firstOrCreate(['product_id' => $aqua->id, 'warehouse_id' => $whJakarta->id], ['quantity' => 120]);
    }
}
