@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20 shadow-2xs">
                    <i data-lucide="pie-chart" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-slate-100">Laporan & Analitik Konsinyasi</h2>
                    <p class="text-xs text-slate-400">Ringkasan performa penjualan barang titipan, mutasi stok, dan pembagian hasil supplier</p>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <a 
                    href="{{ route('consignments.reports.export-pdf', request()->query()) }}" 
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-100 font-bold text-xs border border-rose-200 dark:border-rose-500/20 transition shadow-xs"
                >
                    <i data-lucide="file-down" class="w-4 h-4"></i>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        <div class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>

        <!-- Filter Form -->
        <form action="{{ route('consignments.reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Supplier Filter (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Filter Supplier Penitip
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Start Date (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Dari Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none">
            </div>

            <!-- End Date (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none">
            </div>

            <!-- Reset (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-end">
                @if(request()->hasAny(['supplier_id', 'start_date', 'end_date']))
                    <a href="{{ route('consignments.reports.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- 4 KPI Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Omzet Terjual -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">Total Omzet Konsinyasi</span>
            <span class="text-xl font-black text-slate-900 dark:text-slate-100 font-mono-num">
                Rp {{ number_format($totalGrossSales, 0, ',', '.') }}
            </span>
            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1 font-semibold">
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-purple-500"></i>
                <span>{{ number_format($totalSoldQty, 0) }} Pcs Terjual</span>
            </div>
        </div>

        <!-- Hak Supplier -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">Hak Setor Supplier (HPP)</span>
            <span class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono-num">
                Rp {{ number_format($totalSupplierPayable, 0, ',', '.') }}
            </span>
            <div class="text-[11px] text-slate-400 mt-1 font-semibold">
                Porsi nilai milik penitip barang
            </div>
        </div>

        <!-- Laba Komisi Toko -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">Keuntungan Komisi Toko</span>
            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono-num">
                Rp {{ number_format($totalStoreCommission, 0, ',', '.') }}
            </span>
            <div class="text-[11px] text-slate-400 mt-1 font-semibold">
                Margin laba bersih ritel toko
            </div>
        </div>

        <!-- Sisa Tagihan Unsettled / Unpaid -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
            <span class="text-[11px] font-bold text-slate-400 block mb-1">Hutang Belum Dilunasi</span>
            <span class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono-num">
                Rp {{ number_format($totalUnpaidSettlement, 0, ',', '.') }}
            </span>
            <div class="text-[11px] text-slate-400 mt-1 font-semibold">
                Faktur settlement siap bayar
            </div>
        </div>
    </div>

    <!-- Breakdown Per Supplier Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-gradient-to-r from-purple-700 to-indigo-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="users" class="w-4 h-4"></i>
                <h3 class="font-black text-xs tracking-tight uppercase">Rekap Penjualan per Supplier Penitip</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-purple-800 text-white/95 uppercase font-bold text-[10px]">
                        <th class="py-2.5 px-5">Nama Supplier Penitip</th>
                        <th class="py-2.5 px-5 text-center">Qty Terjual</th>
                        <th class="py-2.5 px-5 text-right">Total Omzet POS</th>
                        <th class="py-2.5 px-5 text-right">Hak Supplier</th>
                        <th class="py-2.5 px-5 text-right">Laba Komisi Toko</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($supplierBreakdown as $sb)
                        <tr class="hover:bg-purple-50/20">
                            <td class="py-3 px-5 font-bold text-slate-800 dark:text-slate-200">
                                {{ $sb->consignmentSupplier?->name ?? 'Supplier Tidak Terdaftar' }}
                            </td>
                            <td class="py-3 px-5 text-center font-bold font-mono">
                                {{ number_format($sb->total_qty, 0) }} Pcs
                            </td>
                            <td class="py-3 px-5 text-right font-mono">
                                Rp {{ number_format($sb->total_gross, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-rose-600 dark:text-rose-400 font-mono">
                                Rp {{ number_format($sb->total_payable, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                Rp {{ number_format(max(0, $sb->total_gross - $sb->total_payable), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Tidak ada transaksi penjualan konsinyasi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Current Stock of Consignment Products Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="boxes" class="w-4 h-4 text-purple-400"></i>
                <h3 class="font-black text-xs tracking-tight uppercase">Sisa Stok Fisik Produk Konsinyasi di Toko</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white/95 uppercase font-bold text-[10px]">
                        <th class="py-2.5 px-5">Kode & Nama Produk</th>
                        <th class="py-2.5 px-5">Supplier Penitip</th>
                        <th class="py-2.5 px-5 text-right">Harga Setor (HPP)</th>
                        <th class="py-2.5 px-5 text-right">Harga Jual</th>
                        <th class="py-2.5 px-5 text-center">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($consignmentProducts as $cp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $cp->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $cp->code }}</div>
                            </td>
                            <td class="py-3 px-5 text-slate-600 dark:text-slate-300">
                                {{ $cp->consignmentSupplier?->name ?? '-' }}
                            </td>
                            <td class="py-3 px-5 text-right font-mono">
                                Rp {{ number_format($cp->consignment_rate > 0 ? $cp->consignment_rate : $cp->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-right font-mono font-bold">
                                Rp {{ number_format($cp->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $cp->total_stock <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-purple-50 text-purple-700' }}">
                                    {{ number_format($cp->total_stock, 0) }} {{ $cp->baseUnit?->name ?? 'Pcs' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Belum ada data produk bertipe konsinyasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
