@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100 shrink-0">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
                <h4 class="text-xl font-black text-slate-900">{{ number_format($totalSalesCount, 0, ',', '.') }} Faktur</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shrink-0">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Omzet Penjualan</p>
                <h4 class="text-xl font-black text-emerald-600">Rp {{ number_format($totalGrandTotal, 0, ',', '.') }}</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Potongan Diskon</p>
                <h4 class="text-xl font-black text-amber-600">Rp {{ number_format($totalDiscountAmount, 0, ',', '.') }}</h4>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shrink-0">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kas Diterima</p>
                <h4 class="text-xl font-black text-blue-600">Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 dark:border-slate-800 dark:bg-slate-900 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Pencarian No. Faktur / Pelanggan (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari Transaksi
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Faktur / Pelanggan..." class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-0 focus:outline-none">
                </div>
            </div>

            <!-- Dari Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Dari Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Sampai Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Sampai Tanggal
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                </div>
            </div>

            <!-- Gudang / Cabang (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Gudang / Cabang
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="warehouse_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Cabang / Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Kasir / User + Action Buttons (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Kasir
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="user_id" onchange="this.form.submit()" class="select2-filter w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Semua Kasir</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="p-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold transition flex items-center justify-center shadow-xs shadow-brand-500/20 shrink-0 cursor-pointer" title="Terapkan Filter">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                </button>

                @if(request()->hasAny(['search', 'start_date', 'end_date', 'warehouse_id', 'user_id', 'payment_method', 'payment_status', 'status']))
                    <a href="{{ route('sales.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Sales Table Card (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Daftar Riwayat Penjualan</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $sales->total() }} Transaksi
                </span>
            </div>
            <div class="flex items-center gap-2">
                @if(request('start_date') || request('end_date'))
                    <span class="text-xs text-white/80 font-medium hidden md:inline mr-1">Periode: {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') : '-' }} s/d {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') : '-' }}</span>
                @endif
                <a href="{{ route('reports.sales.export-pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs border border-white/20 shadow-xs transition" title="Preview & Cetak Laporan Penjualan (PDF)">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-white"></i>
                    <span>Cetak PDF</span>
                </a>
                <a href="{{ route('reports.sales.export-excel', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-brand-600 hover:bg-brand-50 font-bold text-xs shadow-xs transition" title="Export Laporan Penjualan ke Excel (.xlsx)">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">No. Faktur & Waktu</th>
                        <th class="py-3 px-4 border-b border-white/10">Kasir & Pelanggan</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Metode Bayar</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Total Tagihan</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/70 transition {{ $sale->status === 'void' ? 'bg-rose-50/30 opacity-70' : '' }}">
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-900 font-mono flex items-center gap-1.5">
                                    <span>{{ $sale->invoice_number }}</span>
                                    @if($sale->status === 'void')
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-rose-100 text-rose-700 border border-rose-200">VOID</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ \Carbon\Carbon::parse($sale->sale_date)->translatedFormat('d M Y, H:i') }}
                                </div>
                            </td>

                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800">
                                    {{ $sale->customer ? $sale->customer->name : 'Umum (Walk-in)' }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Kasir: {{ $sale->user ? $sale->user->name : '-' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-5">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $sale->warehouse ? $sale->warehouse->name : 'Utama' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase
                                    {{ $sale->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $sale->payment_method === 'qris' ? 'bg-brand-50 text-brand-700 border border-brand-200' : '' }}
                                    {{ $sale->payment_method === 'transfer' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $sale->payment_method === 'credit' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                ">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                @if($sale->status === 'void')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Dibatalkan (Void)</span>
                                @elseif($sale->payment_status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Lunas</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Sebagian</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Belum Bayar</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-5 text-right font-mono">
                                <div class="font-black text-slate-900 {{ $sale->status === 'void' ? 'line-through text-slate-400' : '' }}">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </div>
                                @if($sale->discount_amount > 0)
                                    <div class="text-[10px] text-emerald-600 font-semibold">
                                        Disc: -Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- View Details Modal Button -->
                                    <button 
                                        type="button" 
                                        onclick="viewSaleDetail({{ json_encode($sale) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" 
                                        title="Rincian Transaksi"
                                    >
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Reprint Receipt Button -->
                                    <button 
                                        type="button" 
                                        onclick="reprintReceipt({{ json_encode($sale) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition cursor-pointer" 
                                        title="Cetak Ulang Struk Kasir"
                                    >
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Void Sale Button -->
                                    @if($sale->status !== 'void')
                                        @can('sales.void')
                                        <button 
                                            type="button" 
                                            onclick="openVoidModal({{ $sale->id }}, '{{ $sale->invoice_number }}')"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" 
                                            title="Batalkan / Void Transaksi"
                                        >
                                            <i data-lucide="ban" class="w-4 h-4"></i>
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="receipt" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-semibold text-slate-600">Tidak ada riwayat transaksi penjualan</p>
                                    <p class="text-xs text-slate-400">Transaksi yang diproses di kasir POS akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($sales->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL DETAIL TRANSAKSI -->
<div id="saleDetailModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-2xl w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-900 tracking-tight" id="detail_invoice_title">Rincian Transaksi</h3>
                    <p class="text-[11px] text-slate-400" id="detail_sale_date">-</p>
                </div>
            </div>
            <button onclick="closeModal('saleDetailModal')" type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 flex-1 text-xs">
            <!-- Meta Info Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                <div>
                    <span class="text-[10px] text-slate-400 block">Kasir</span>
                    <span class="font-bold text-slate-800" id="detail_cashier">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block">Pelanggan</span>
                    <span class="font-bold text-slate-800" id="detail_customer">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block">Gudang/Cabang</span>
                    <span class="font-bold text-slate-800" id="detail_warehouse">-</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 block">Metode Bayar</span>
                    <span class="font-bold text-slate-800 uppercase" id="detail_payment_method">-</span>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-400 uppercase text-[9px] font-bold">
                        <tr>
                            <th class="py-2.5 px-3">Produk</th>
                            <th class="py-2.5 px-3 text-center">Qty</th>
                            <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail_items_tbody" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Rendered via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Summary Breakdown -->
            <div class="space-y-1.5 pt-2 border-t border-slate-100 text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-bold font-mono" id="detail_subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span class="font-bold font-mono" id="detail_discount">- Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-900 font-bold text-sm pt-1 border-t border-slate-200">
                    <span>Grand Total</span>
                    <span class="font-mono text-brand-600" id="detail_grand_total">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-500 text-[11px]">
                    <span>Nominal Dibayar</span>
                    <span class="font-mono" id="detail_paid_amount">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-500 text-[11px]">
                    <span>Kembalian</span>
                    <span class="font-mono" id="detail_change_amount">Rp 0</span>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-2 shrink-0">
            <button type="button" onclick="closeModal('saleDetailModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/70 transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL REPRINT STRUK THERMAL -->
<div id="receiptModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-sm w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h4 class="font-bold text-xs text-slate-800">Cetak Struk Transaksi</h4>
            <button onclick="closeModal('receiptModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <div class="p-6 bg-slate-100 flex justify-center">
            <div id="thermal_receipt_paper" class="w-64 bg-white p-4 shadow-md font-mono text-[11px] text-slate-800 space-y-2 border border-slate-200">
                <!-- Struk HTML -->
            </div>
        </div>
        <div class="p-4 border-t border-slate-100 flex gap-2">
            <button onclick="closeModal('receiptModal')" class="flex-1 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Tutup</button>
            <button onclick="window.print()" class="flex-1 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm shadow-brand-500/25">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL VOID SALE -->
<div id="voidSaleModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white border border-slate-200/90 rounded-2xl max-w-md w-full shadow-2xl transition-all my-auto overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50/60">
            <div class="flex items-center gap-2.5 text-rose-700">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                <h4 class="font-bold text-sm">Konfirmasi Pembatalan (VOID)</h4>
            </div>
            <button onclick="closeModal('voidSaleModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <form id="voidSaleForm" onsubmit="handleVoidSale(event)" class="p-6 space-y-4 text-xs">
            <input type="hidden" id="void_sale_id">
            <p class="text-slate-600 leading-relaxed">
                Anda akan membatalkan faktur <b id="void_invoice_label" class="text-slate-900"></b>. Stok produk akan dikembalikan secara otomatis ke gudang.
            </p>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alasan Pembatalan / Void <span class="text-rose-500">*</span></label>
                <textarea id="void_reason" required placeholder="Contoh: Pelanggan salah pesan barang, salah input pembayaran kasir" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-0 focus:outline-none focus:border-rose-500" rows="3"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('voidSaleModal')" class="px-4 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-sm shadow-rose-600/25">Batalkan Faktur</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('hidden');
            el.classList.add('flex');
            lucide.createIcons();
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function viewSaleDetail(sale) {
        document.getElementById('detail_invoice_title').innerText = `Faktur ${sale.invoice_number}`;
        document.getElementById('detail_sale_date').innerText = new Date(sale.sale_date).toLocaleString('id-ID');
        document.getElementById('detail_cashier').innerText = sale.user ? sale.user.name : '-';
        document.getElementById('detail_customer').innerText = sale.customer ? sale.customer.name : 'Umum (Walk-in)';
        document.getElementById('detail_warehouse').innerText = sale.warehouse ? sale.warehouse.name : 'Utama';
        document.getElementById('detail_payment_method').innerText = sale.payment_method;

        const tbody = document.getElementById('detail_items_tbody');
        tbody.innerHTML = '';
        (sale.items || []).forEach(it => {
            const isFree = parseFloat(it.unit_price) === 0;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2.5 px-3">
                    <div class="font-bold text-slate-800">${it.product ? it.product.name : 'Produk'} ${isFree ? '<b class="text-emerald-600">[GRATIS]</b>' : ''}</div>
                    ${it.discount_amount > 0 ? `<div class="text-[10px] text-emerald-600">Diskon Item: -Rp ${parseInt(it.discount_amount).toLocaleString('id-ID')}</div>` : ''}
                </td>
                <td class="py-2.5 px-3 text-center font-bold font-mono">${it.quantity}</td>
                <td class="py-2.5 px-3 text-right font-mono">Rp ${parseInt(it.unit_price).toLocaleString('id-ID')}</td>
                <td class="py-2.5 px-3 text-right font-bold font-mono">Rp ${parseInt(it.subtotal).toLocaleString('id-ID')}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('detail_subtotal').innerText = `Rp ${parseInt(sale.subtotal).toLocaleString('id-ID')}`;
        document.getElementById('detail_discount').innerText = `- Rp ${parseInt(sale.discount_amount).toLocaleString('id-ID')}`;
        document.getElementById('detail_grand_total').innerText = `Rp ${parseInt(sale.grand_total).toLocaleString('id-ID')}`;
        document.getElementById('detail_paid_amount').innerText = `Rp ${parseInt(sale.paid_amount).toLocaleString('id-ID')}`;
        document.getElementById('detail_change_amount').innerText = `Rp ${parseInt(sale.change_amount).toLocaleString('id-ID')}`;

        openModal('saleDetailModal');
    }

    function reprintReceipt(sale) {
        const paper = document.getElementById('thermal_receipt_paper');
        let itemsHtml = '';
        (sale.items || []).forEach(it => {
            const isFree = parseFloat(it.unit_price) === 0;
            itemsHtml += `
                <div class="flex justify-between">
                    <span>${it.product ? it.product.name : 'Item'} ${isFree ? '<b>[GRATIS]</b>' : ''}</span>
                </div>
                <div class="flex justify-between text-slate-500 text-[10px]">
                    <span>${it.quantity} x ${parseInt(it.unit_price).toLocaleString('id-ID')} ${it.discount_amount > 0 ? `(Disc: -${parseInt(it.discount_amount).toLocaleString('id-ID')})` : ''}</span>
                    <span class="font-bold text-slate-800">Rp ${parseInt(it.subtotal).toLocaleString('id-ID')}</span>
                </div>
            `;
        });

        paper.innerHTML = `
            <div class="text-center space-y-0.5 pb-2 border-b border-dashed border-slate-300">
                <h4 class="font-black text-xs uppercase tracking-wider">POS RETAIL PRO</h4>
                <p class="text-[10px] text-slate-500">${sale.warehouse ? sale.warehouse.name : 'Cabang Utama'}</p>
            </div>
            <div class="text-[10px] space-y-0.5 py-1 border-b border-dashed border-slate-300">
                <div class="flex justify-between"><span>No. Faktur</span><span class="font-bold">${sale.invoice_number}</span></div>
                <div class="flex justify-between"><span>Kasir</span><span>${sale.user ? sale.user.name : '-'}</span></div>
                <div class="flex justify-between"><span>Pelanggan</span><span>${sale.customer ? sale.customer.name : 'Umum'}</span></div>
                <div class="flex justify-between"><span>Waktu</span><span>${new Date(sale.sale_date).toLocaleString('id-ID')}</span></div>
            </div>
            <div class="space-y-1.5 py-2 border-b border-dashed border-slate-300">
                ${itemsHtml}
            </div>
            <div class="space-y-1 pt-1 text-[10px]">
                <div class="flex justify-between"><span>Subtotal</span><span>Rp ${parseInt(sale.subtotal).toLocaleString('id-ID')}</span></div>
                ${sale.discount_amount > 0 ? `<div class="flex justify-between text-emerald-600"><span>Diskon</span><span>- Rp ${parseInt(sale.discount_amount).toLocaleString('id-ID')}</span></div>` : ''}
                <div class="flex justify-between text-xs font-black pt-1 border-t border-slate-200"><span>TOTAL</span><span>Rp ${parseInt(sale.grand_total).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Bayar (${sale.payment_method.toUpperCase()})</span><span>Rp ${parseInt(sale.paid_amount).toLocaleString('id-ID')}</span></div>
                <div class="flex justify-between"><span>Kembalian</span><span>Rp ${parseInt(sale.change_amount).toLocaleString('id-ID')}</span></div>
            </div>
            <div class="text-center text-[9px] text-slate-400 pt-3 border-t border-dashed border-slate-300">
                <p>Terima kasih atas kunjungan Anda!</p>
            </div>
        `;

        openModal('receiptModal');
    }

    function openVoidModal(saleId, invoiceNumber) {
        document.getElementById('void_sale_id').value = saleId;
        document.getElementById('void_invoice_label').innerText = invoiceNumber;
        document.getElementById('void_reason').value = '';
        openModal('voidSaleModal');
    }

    async function handleVoidSale(e) {
        e.preventDefault();
        const saleId = document.getElementById('void_sale_id').value;
        const reason = document.getElementById('void_reason').value;

        try {
            const res = await fetch(`/pos/void/${saleId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason })
            });

            const data = await res.json();
            if (data.status === 'success') {
                closeModal('voidSaleModal');
                Swal.fire({
                    icon: 'success',
                    title: 'Faktur Berhasil di-VOID',
                    text: data.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal VOID', text: data.message });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Kesalahan', text: err.message });
        }
    }
</script>
@endsection
