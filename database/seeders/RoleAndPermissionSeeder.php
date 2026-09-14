<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar Permissions Lengkap
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data & Resep (BOM)
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'units.view', 'units.create', 'units.edit', 'units.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'recipes.view', 'recipes.manage',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',

            // F&B (Resto, Kafe, Meja, KDS, Modifiers)
            'tables.view', 'tables.create', 'tables.edit', 'tables.delete', 'tables.reservations',
            'modifiers.view', 'modifiers.manage',
            'kitchen.view',

            // Jasa & Layanan (Services, Booking, Antrian, Staff)
            'service_queue.view',
            'service_bookings.view', 'service_bookings.manage',
            'service_staff.view', 'service_staff.manage',

            // Harga & Diskon
            'pricing.view', 'pricing.manage',
            'discounts.view', 'discounts.manage',

            // Inventory & Stok
            'stocks.view', 'stocks.opname', 'stocks.transfer', 'stocks.adjust',

            // Pembelian (Purchasing)
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.receive', 'purchases.return',

            // Penjualan (POS / Sales)
            'sales.pos', 'sales.view', 'sales.hold', 'sales.void', 'sales.return',
            'shifts.manage',

            // Keuangan (Finance)
            'finance.accounts', 'finance.payable', 'finance.receivable', 'finance.cashflow', 'finance.transfer',

            // Laporan
            'reports.sales', 'reports.purchases', 'reports.inventory', 'reports.finance', 'reports.shifts',

            // User & Pengaturan
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.manage',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat Roles Default
        // Super Admin (Semua Hak Akses)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Owner (Dashboard, Laporan, Keuangan, Master Data, FNB, Service)
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions([
            'dashboard.view',
            'categories.view', 'units.view', 'products.view', 'recipes.view', 'suppliers.view', 'customers.view', 'warehouses.view',
            'tables.view', 'tables.reservations', 'modifiers.view', 'kitchen.view',
            'service_queue.view', 'service_bookings.view', 'service_staff.view',
            'pricing.view', 'discounts.view', 'stocks.view', 'purchases.view', 'sales.view',
            'finance.accounts', 'finance.payable', 'finance.receivable', 'finance.cashflow', 'finance.transfer',
            'reports.sales', 'reports.purchases', 'reports.inventory', 'reports.finance', 'reports.shifts',
            'audit.view',
        ]);

        // Manager (Operasional, Stok, Kasir, Pembelian, Diskon, FNB, Service)
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'dashboard.view',
            'categories.view', 'categories.create', 'categories.edit',
            'units.view', 'units.create', 'units.edit',
            'products.view', 'products.create', 'products.edit',
            'recipes.view', 'recipes.manage',
            'tables.view', 'tables.create', 'tables.edit', 'tables.delete', 'tables.reservations',
            'modifiers.view', 'modifiers.manage',
            'kitchen.view',
            'service_queue.view', 'service_bookings.view', 'service_bookings.manage', 'service_staff.view', 'service_staff.manage',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'warehouses.view',
            'pricing.view', 'pricing.manage',
            'discounts.view', 'discounts.manage',
            'stocks.view', 'stocks.opname', 'stocks.transfer', 'stocks.adjust',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.receive', 'purchases.return',
            'sales.pos', 'sales.view', 'sales.hold', 'sales.void', 'sales.return',
            'shifts.manage',
            'reports.sales', 'reports.purchases', 'reports.inventory',
        ]);

        // Cashier (Fokus Penjualan POS, Shift, Customer)
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'sales.pos', 'sales.view', 'sales.hold', 'sales.return',
            'customers.view', 'customers.create',
            'shifts.manage',
            'products.view',
        ]);

        // Warehouse Staff (Gudang, Penerimaan, Opname, Transfer)
        $warehouseStaff = Role::firstOrCreate(['name' => 'warehouse_staff']);
        $warehouseStaff->givePermissionTo([
            'products.view',
            'stocks.view', 'stocks.opname', 'stocks.transfer', 'stocks.adjust',
            'purchases.view', 'purchases.receive', 'purchases.return',
            'reports.inventory',
        ]);

        // Accountant (Keuangan, Hutang, Piutang, Laporan Keuangan)
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->givePermissionTo([
            'dashboard.view',
            'finance.accounts', 'finance.payable', 'finance.receivable', 'finance.cashflow', 'finance.transfer',
            'purchases.view', 'sales.view',
            'reports.sales', 'reports.purchases', 'reports.finance',
        ]);

        // 3. Buat User Admin Default
        $admin = User::firstOrCreate(
            ['email' => 'admin@pospro.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('super_admin');

        // Buat User Kasir Default
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@pospro.com'],
            [
                'name' => 'Nanda (Kasir)',
                'password' => Hash::make('password'),
            ]
        );
        $kasir->assignRole('cashier');
    }
}
