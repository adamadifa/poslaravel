<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Daftar Permissions Lengkap
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'units.view', 'units.create', 'units.edit', 'units.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',

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
        $superAdmin->givePermissionTo(Permission::all());

        // Owner (Dashboard, Laporan, Keuangan, Master Data)
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo([
            'dashboard.view',
            'categories.view', 'units.view', 'products.view', 'suppliers.view', 'customers.view', 'warehouses.view',
            'pricing.view', 'discounts.view', 'stocks.view', 'purchases.view', 'sales.view',
            'finance.accounts', 'finance.payable', 'finance.receivable', 'finance.cashflow', 'finance.transfer',
            'reports.sales', 'reports.purchases', 'reports.inventory', 'reports.finance', 'reports.shifts',
            'audit.view',
        ]);

        // Manager (Operasional, Stok, Kasir, Pembelian, Diskon)
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'dashboard.view',
            'categories.view', 'categories.create', 'categories.edit',
            'units.view', 'units.create', 'units.edit',
            'products.view', 'products.create', 'products.edit',
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
