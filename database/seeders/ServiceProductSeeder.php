<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ServiceStaff;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ServiceProductSeeder extends Seeder
{
    /**
     * Run database seeds for service products, categories, staff, and commissions.
     */
    public function run(): void
    {
        // 1. Units (Satuan)
        $unitKg = Unit::firstOrCreate(['name' => 'Kilogram'], ['short_name' => 'kg', 'is_active' => true]);
        $unitPcs = Unit::firstOrCreate(['name' => 'Pcs'], ['short_name' => 'pcs', 'is_active' => true]);
        $unitSesi = Unit::firstOrCreate(['name' => 'Sesi'], ['short_name' => 'sesi', 'is_active' => true]);
        $unitPasang = Unit::firstOrCreate(['name' => 'Pasang'], ['short_name' => 'psg', 'is_active' => true]);
        $unitUnit = Unit::firstOrCreate(['name' => 'Unit'], ['short_name' => 'unit', 'is_active' => true]);
        $unitPaket = Unit::firstOrCreate(['name' => 'Paket'], ['short_name' => 'pkt', 'is_active' => true]);

        // 2. Kategori Layanan & Jasa (send_to_kitchen = false)
        // A. Jasa Laundry
        $catLaundry = Category::firstOrCreate(
            ['name' => 'Jasa Laundry & Cuci'],
            [
                'slug' => 'jasa-laundry-cuci',
                'description' => 'Layanan cuci pakaian kiloan, satuan, bedcover, dan cuci sepatu',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Parfum Akasia', 'Parfum Lavender', 'Parfum Ocean Fresh', 'Pisah Baju Putih', 'Ekstra Setrika', 'Lipat Saja', 'Gantung Hanger'],
            ]
        );

        $catLaundryKiloan = Category::firstOrCreate(
            ['name' => 'Laundry Kiloan', 'parent_id' => $catLaundry->id],
            [
                'slug' => 'laundry-kiloan',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Parfum Akasia', 'Parfum Lavender', 'Pisah Baju Putih', 'Lipat Saja', 'Setrika Rapi'],
            ]
        );

        $catLaundrySatuan = Category::firstOrCreate(
            ['name' => 'Laundry Satuan & Sepatu', 'parent_id' => $catLaundry->id],
            [
                'slug' => 'laundry-satuan-sepatu',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Deep Clean', 'Unyellowing', 'Bungkus Plastik Khusus', 'Gantung Hanger'],
            ]
        );

        // B. Barbershop & Salon
        $catSalon = Category::firstOrCreate(
            ['name' => 'Barbershop & Salon'],
            [
                'slug' => 'barbershop-salon',
                'description' => 'Layanan potong rambut pria/wanita, creambath, perawatan rambut & styling',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Fade Tipis', 'Fade Sedang', 'Keramas Dingin', 'Pijat Kepala', 'Shaving Rapih', 'Pomade Waterbased'],
            ]
        );

        // C. Cuci Kendaraan & Detailing
        $catVehicle = Category::firstOrCreate(
            ['name' => 'Cuci Mobil & Motor'],
            [
                'slug' => 'cuci-mobil-motor',
                'description' => 'Layanan cuci salju, vacum interior, semir ban, dan auto detailing',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Semir Ban Ekstra', 'Fogging Anti Bakteri', 'Vacum Karpet', 'Cuci Mesin', 'Wax Body'],
            ]
        );

        // D. Servis & Bengkel
        $catService = Category::firstOrCreate(
            ['name' => 'Servis & Perawatan'],
            [
                'slug' => 'servis-perawatan',
                'description' => 'Layanan tune up kendaraan, ganti oli, dan servis peralatan elektronik / AC',
                'send_to_kitchen' => false,
                'is_active' => true,
                'default_notes' => ['Ganti Oli Mesin', 'Cek Rem & Rantai', 'Tune Up', 'Pembersihan Filter', 'Tambah Freon'],
            ]
        );

        // 3. Staf Teknisi & Terapis (Users)
        $staffRudi = User::firstOrCreate(
            ['email' => 'rudi.barber@pospro.com'],
            [
                'name' => 'Rudi (Senior Barber / Teknisi)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $staffAgus = User::firstOrCreate(
            ['email' => 'agus.laundry@pospro.com'],
            [
                'name' => 'Agus (Operator Cuci & Laundry)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $staffSiti = User::firstOrCreate(
            ['email' => 'siti.salon@pospro.com'],
            [
                'name' => 'Siti (Terapis Salon / Setrika)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $staffDimas = User::firstOrCreate(
            ['email' => 'dimas.mekanik@pospro.com'],
            [
                'name' => 'Dimas (Detailer & Mekanik)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // 4. Daftar Produk Layanan (Services)
        $serviceProducts = [
            // LAUNDRY
            [
                'code' => 'SRV-LND-001',
                'name' => 'Laundry Kiloan Cuci Komplit (Reguler 2 Hari)',
                'category_id' => $catLaundryKiloan->id,
                'base_unit_id' => $unitKg->id,
                'selling_price' => 8000,
                'purchase_price' => 0,
                'duration_minutes' => 1440,
                'default_notes' => ['Parfum Akasia', 'Parfum Lavender', 'Parfum Ocean Fresh', 'Pisah Baju Putih', 'Lipat Rapi'],
                'staff_id' => $staffAgus->id,
                'commission_type' => 'fixed',
                'commission_value' => 1000,
            ],
            [
                'code' => 'SRV-LND-002',
                'name' => 'Laundry Kiloan Cuci Komplit (Express 6 Jam)',
                'category_id' => $catLaundryKiloan->id,
                'base_unit_id' => $unitKg->id,
                'selling_price' => 15000,
                'purchase_price' => 0,
                'duration_minutes' => 360,
                'default_notes' => ['Parfum Akasia', 'Parfum Lavender', 'Express Cepat', 'Pisah Pakaian'],
                'staff_id' => $staffAgus->id,
                'commission_type' => 'fixed',
                'commission_value' => 2500,
            ],
            [
                'code' => 'SRV-LND-003',
                'name' => 'Cuci Bedcover Besar (King / Queen Size)',
                'category_id' => $catLaundrySatuan->id,
                'base_unit_id' => $unitPcs->id,
                'selling_price' => 35000,
                'purchase_price' => 0,
                'duration_minutes' => 180,
                'default_notes' => ['Wangi Tahan Lama', 'Bungkus Plastik Khusus'],
                'staff_id' => $staffSiti->id,
                'commission_type' => 'percent',
                'commission_value' => 15,
            ],
            [
                'code' => 'SRV-LND-004',
                'name' => 'Cuci Sepatu Sneaker Deep Clean',
                'category_id' => $catLaundrySatuan->id,
                'base_unit_id' => $unitPasang->id,
                'selling_price' => 45000,
                'purchase_price' => 0,
                'duration_minutes' => 60,
                'default_notes' => ['Deep Clean', 'Unyellowing', 'Semprot Anti Bakteri'],
                'staff_id' => $staffAgus->id,
                'commission_type' => 'fixed',
                'commission_value' => 10000,
            ],

            // BARBERSHOP & SALON
            [
                'code' => 'SRV-BAR-001',
                'name' => 'Gentleman Haircut + Cuci Rambut + Pijat Bahu',
                'category_id' => $catSalon->id,
                'base_unit_id' => $unitSesi->id,
                'selling_price' => 50000,
                'purchase_price' => 0,
                'duration_minutes' => 45,
                'default_notes' => ['Fade Tipis', 'Fade Sedang', 'Keramas Dingin', 'Pijat Bahu & Leher', 'Pomade Wangi'],
                'staff_id' => $staffRudi->id,
                'commission_type' => 'percent',
                'commission_value' => 20,
            ],
            [
                'code' => 'SRV-BAR-002',
                'name' => 'Creambath Tradisional + Relaksasi Head Spa',
                'category_id' => $catSalon->id,
                'base_unit_id' => $unitSesi->id,
                'selling_price' => 85000,
                'purchase_price' => 0,
                'duration_minutes' => 60,
                'default_notes' => ['Aroma Lidah Buaya', 'Aroma Ginseng', 'Pijat Punggung', 'Handuk Hangat'],
                'staff_id' => $staffSiti->id,
                'commission_type' => 'percent',
                'commission_value' => 25,
            ],
            [
                'code' => 'SRV-BAR-003',
                'name' => 'Coloring & Bleaching Rambut Fashion',
                'category_id' => $catSalon->id,
                'base_unit_id' => $unitSesi->id,
                'selling_price' => 175000,
                'purchase_price' => 0,
                'duration_minutes' => 90,
                'default_notes' => ['Ash Grey', 'Dark Brown', 'Blonde', 'Bleach Level 8'],
                'staff_id' => $staffRudi->id,
                'commission_type' => 'percent',
                'commission_value' => 20,
            ],

            // CUCI KENDARAAN
            [
                'code' => 'SRV-VEH-001',
                'name' => 'Cuci Mobil Salju + Vacum Interior + Semir Ban',
                'category_id' => $catVehicle->id,
                'base_unit_id' => $unitUnit->id,
                'selling_price' => 50000,
                'purchase_price' => 0,
                'duration_minutes' => 40,
                'default_notes' => ['Semir Ban Mengkilap', 'Vacum Karpet Dasar', 'Pewangi Kopi', 'Lap Kering Microfiber'],
                'staff_id' => $staffDimas->id,
                'commission_type' => 'fixed',
                'commission_value' => 12000,
            ],
            [
                'code' => 'SRV-VEH-002',
                'name' => 'Cuci Motor Matic / Bebek Salju',
                'category_id' => $catVehicle->id,
                'base_unit_id' => $unitUnit->id,
                'selling_price' => 20000,
                'purchase_price' => 0,
                'duration_minutes' => 20,
                'default_notes' => ['Semir Ban', 'Bersihkan Rantai', 'Cuci Kolong'],
                'staff_id' => $staffDimas->id,
                'commission_type' => 'fixed',
                'commission_value' => 5000,
            ],
            [
                'code' => 'SRV-VEH-003',
                'name' => 'Fogging Anti Bakteri & Virus Kabin Mobil',
                'category_id' => $catVehicle->id,
                'base_unit_id' => $unitSesi->id,
                'selling_price' => 75000,
                'purchase_price' => 0,
                'duration_minutes' => 25,
                'default_notes' => ['Aroma Lemon', 'Aroma Peppermint', 'Sirkulasi AC 15 Mnt'],
                'staff_id' => $staffDimas->id,
                'commission_type' => 'percent',
                'commission_value' => 20,
            ],

            // SERVIS & ELEKTRONIK
            [
                'code' => 'SRV-TEK-001',
                'name' => 'Servis & Cuci AC Split (1/2 - 1 PK)',
                'category_id' => $catService->id,
                'base_unit_id' => $unitUnit->id,
                'selling_price' => 85000,
                'purchase_price' => 0,
                'duration_minutes' => 60,
                'default_notes' => ['Cuci Evaporator', 'Cek Tekanan Freon', 'Bersihkan Talang Air', 'Cek Ampere'],
                'staff_id' => $staffRudi->id,
                'commission_type' => 'fixed',
                'commission_value' => 25000,
            ],
            [
                'code' => 'SRV-TEK-002',
                'name' => 'Tune Up Ringan & Ganti Oli Motor Matic',
                'category_id' => $catService->id,
                'base_unit_id' => $unitPaket->id,
                'selling_price' => 65000,
                'purchase_price' => 0,
                'duration_minutes' => 45,
                'default_notes' => ['Ganti Oli Mesin', 'Bersihkan Throttle Body', 'Cek Kampas Rem', 'Stel Angin Ban'],
                'staff_id' => $staffDimas->id,
                'commission_type' => 'fixed',
                'commission_value' => 15000,
            ],
        ];

        foreach ($serviceProducts as $prodData) {
            $staffId = $prodData['staff_id'];
            $commType = $prodData['commission_type'];
            $commVal = $prodData['commission_value'];

            unset($prodData['staff_id'], $prodData['commission_type'], $prodData['commission_value']);

            $product = Product::updateOrCreate(
                ['code' => $prodData['code']],
                array_merge($prodData, [
                    'slug' => Str::slug($prodData['name']),
                    'product_type' => 'service',
                    'is_bookable' => true,
                    'require_staff_assignment' => true,
                    'send_to_kitchen' => false,
                    'is_active' => true,
                    'min_stock' => 0,
                    'max_stock' => 0,
                    'has_expiry' => false,
                ])
            );

            // Assign staff commission
            ServiceStaff::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'user_id' => $staffId,
                ],
                [
                    'commission_type' => $commType,
                    'commission_value' => $commVal,
                    'is_active' => true,
                ]
            );
        }
    }
}
