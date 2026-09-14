<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Grouping map of permissions per module for rich UI display.
     */
    public static function getPermissionModules(): array
    {
        return [
            'Dashboard' => [
                'icon' => 'layout-grid',
                'color' => 'brand',
                'permissions' => [
                    'dashboard.view' => 'Lihat Dashboard & Ringkasan',
                ],
            ],
            'Master Data' => [
                'icon' => 'database',
                'color' => 'blue',
                'permissions' => [
                    'products.view' => 'Lihat Produk',
                    'products.create' => 'Tambah Produk',
                    'products.edit' => 'Edit Produk',
                    'products.delete' => 'Hapus Produk',
                    'categories.view' => 'Lihat Kategori',
                    'categories.create' => 'Tambah Kategori',
                    'categories.edit' => 'Edit Kategori',
                    'categories.delete' => 'Hapus Kategori',
                    'units.view' => 'Lihat Satuan',
                    'units.create' => 'Tambah Satuan',
                    'units.edit' => 'Edit Satuan',
                    'units.delete' => 'Hapus Satuan',
                    'suppliers.view' => 'Lihat Pemasok',
                    'suppliers.create' => 'Tambah Pemasok',
                    'suppliers.edit' => 'Edit Pemasok',
                    'suppliers.delete' => 'Hapus Pemasok',
                    'customers.view' => 'Lihat Pelanggan',
                    'customers.create' => 'Tambah Pelanggan',
                    'customers.edit' => 'Edit Pelanggan',
                    'customers.delete' => 'Hapus Pelanggan',
                    'warehouses.view' => 'Lihat Gudang/Cabang',
                    'warehouses.create' => 'Tambah Gudang',
                    'warehouses.edit' => 'Edit Gudang',
                    'warehouses.delete' => 'Hapus Gudang',
                ],
            ],
            'Harga & Diskon' => [
                'icon' => 'tags',
                'color' => 'amber',
                'permissions' => [
                    'pricing.view' => 'Lihat Daftar Harga',
                    'pricing.manage' => 'Kelola Harga & Grosir',
                    'discounts.view' => 'Lihat Diskon & Promo',
                    'discounts.manage' => 'Kelola Program Diskon',
                ],
            ],
            'Inventory & Stok' => [
                'icon' => 'boxes',
                'color' => 'emerald',
                'permissions' => [
                    'stocks.view' => 'Lihat Kartu Stok & Alert',
                    'stocks.opname' => 'Kelola Stock Opname',
                    'stocks.transfer' => 'Kelola Transfer Antar Gudang',
                    'stocks.adjust' => 'Kelola Penyesuaian Stok',
                ],
            ],
            'Pembelian (Procurement)' => [
                'icon' => 'truck',
                'color' => 'indigo',
                'permissions' => [
                    'purchases.view' => 'Lihat Pesanan Pembelian',
                    'purchases.create' => 'Buat Purchase Order (PO)',
                    'purchases.edit' => 'Edit Pesanan Pembelian',
                    'purchases.receive' => 'Penerimaan Barang (GRN)',
                    'purchases.return' => 'Retur Pembelian Barang',
                ],
            ],
            'Kasir & Penjualan POS' => [
                'icon' => 'shopping-cart',
                'color' => 'rose',
                'permissions' => [
                    'sales.pos' => 'Akses Kasir POS',
                    'sales.view' => 'Lihat Riwayat Penjualan',
                    'sales.hold' => 'Fitur Tahan Transaksi (Hold)',
                    'sales.void' => 'Batalkan Penjualan (Void)',
                    'sales.return' => 'Retur Penjualan Kasir',
                    'shifts.manage' => 'Kelola Shift & Buka/Tutup Kasir',
                ],
            ],
            'Keuangan & Kas/Bank' => [
                'icon' => 'wallet',
                'color' => 'cyan',
                'permissions' => [
                    'finance.accounts' => 'Lihat Akun Kas & Bank',
                    'finance.payable' => 'Kelola Hutang Pembelian',
                    'finance.receivable' => 'Kelola Piutang Penjualan',
                    'finance.cashflow' => 'Lihat Arus Kas & Biaya',
                    'finance.transfer' => 'Transfer Antar Rekening',
                ],
            ],
            'Laporan & Analitik' => [
                'icon' => 'bar-chart-3',
                'color' => 'violet',
                'permissions' => [
                    'reports.sales' => 'Laporan Penjualan',
                    'reports.purchases' => 'Laporan Pembelian',
                    'reports.inventory' => 'Laporan Stok & Mutasi',
                    'reports.finance' => 'Laporan Keuangan & Laba Rugi',
                    'reports.shifts' => 'Laporan Shift Kasir',
                ],
            ],
            'Resto & Kuliner (F&B)' => [
                'icon' => 'utensils',
                'color' => 'amber',
                'permissions' => [
                    'tables.view' => 'Lihat Daftar & Denah Meja',
                    'tables.manage' => 'Kelola Meja & Status',
                    'tables.reservations' => 'Kelola Reservasi Meja',
                    'modifiers.manage' => 'Kelola Modifiers & Topping',
                    'kitchen.view' => 'Akses Layar Dapur (KDS)',
                ],
            ],
            'Layanan & Jasa (Service)' => [
                'icon' => 'scissors',
                'color' => 'emerald',
                'permissions' => [
                    'service_staff.manage' => 'Kelola Penugasan & Komisi Staff',
                    'service_bookings.manage' => 'Kelola Booking & Janji Temu',
                    'service_queue.view' => 'Akses Monitoring Antrian Layanan',
                ],
            ],
            'Pengaturan & Staf' => [
                'icon' => 'settings',
                'color' => 'slate',
                'permissions' => [
                    'users.view' => 'Lihat Daftar Pengguna',
                    'users.create' => 'Tambah Pengguna Baru',
                    'users.edit' => 'Edit Data Pengguna',
                    'users.delete' => 'Hapus Pengguna',
                    'roles.manage' => 'Kelola Peran & Hak Akses',
                    'settings.manage' => 'Kelola Pengaturan Toko',
                    'audit.view' => 'Lihat Log Audit Trail',
                ],
            ],
        ];
    }

    /**
     * Display listing of roles and their permissions.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $roles = Role::withCount('users')
            ->with('permissions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByRaw("CASE WHEN name = 'super_admin' THEN 1 WHEN name = 'owner' THEN 2 WHEN name = 'manager' THEN 3 WHEN name = 'cashier' THEN 4 ELSE 5 END")
            ->orderBy('name')
            ->get();

        $modules = self::getPermissionModules();
        $allPermissionsCount = Permission::count();

        return view('roles.index', [
            'title' => 'Hak Akses & Peran',
            'headerTitle' => 'Manajemen Peran & Hak Akses Menu',
            'roles' => $roles,
            'modules' => $modules,
            'allPermissionsCount' => $allPermissionsCount,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-\s]+$/', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.required' => 'Nama peran/role wajib diisi.',
            'name.unique' => 'Nama peran sudah digunakan, pilih nama lain.',
        ]);

        $role = Role::create([
            'name' => strtolower(trim(str_replace(' ', '_', $validated['name']))),
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Peran '{$role->name}' berhasil dibuat dengan hak akses terpilih.");
    }

    /**
     * Update the specified role and its permissions.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\-\s]+$/', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        // If role is super_admin, preserve its name and grant all permissions
        if ($role->name === 'super_admin') {
            $role->syncPermissions(Permission::all());
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return redirect()->route('roles.index')->with('success', 'Peran Super Administrator selalu memiliki hak akses penuh.');
        }

        $role->update([
            'name' => strtolower(trim(str_replace(' ', '_', $validated['name']))),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Hak akses peran '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if ($role->name === 'super_admin') {
            return back()->with('error', 'Peran Super Administrator dilindungi dan tidak dapat dihapus.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', "Peran '{$role->name}' masih digunakan oleh {$role->users()->count()} pengguna. Silakan alihkan peran pengguna terlebih dahulu.");
        }

        $roleName = $role->name;
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', "Peran '{$roleName}' berhasil dihapus.");
    }
}
