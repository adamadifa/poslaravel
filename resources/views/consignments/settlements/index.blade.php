@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards (3 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Belum Dibayar -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-rose-100 dark:border-rose-900/30 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-100 dark:border-rose-500/20 shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 block">Kewajiban Belum Dibayar</span>
                <span class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono-num">
                    Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Sudah Dilunasi -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-emerald-100 dark:border-emerald-900/30 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-500/20 shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 block">Total Sudah Dilunasi</span>
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono-num">
                    Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Keuntungan Komisi Toko -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-purple-100 dark:border-purple-900/30 shadow-2xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20 shrink-0">
                <i data-lucide="badge-percent" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 block">Laba Komisi Toko</span>
                <span class="text-xl font-black text-purple-600 dark:text-purple-400 font-mono-num">
                    Rp {{ number_format($totalCommissionEarned, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Action & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20 shadow-2xs">
                    <i data-lucide="hand-coins" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-slate-100">Settlement & Bagi Hasil Konsinyasi</h2>
                    <p class="text-xs text-slate-400">Rekonsiliasi barang titipan terjual, perhitungan tagihan supplier, dan pembayaran kas/bank</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateSettlementModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-purple-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="calculator" class="w-4 h-4"></i>
                <span>Buat Settlement Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>

        <!-- Row 2: Filter Bar -->
        <form action="{{ route('consignments.settlements.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search Input (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 dark:border-slate-700 hover:border-slate-300 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari No. Settlement / Supplier
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: CNS-2026 atau Nama..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Supplier Filter (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Supplier Penitip
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Pembayaran Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Status Bayar
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <select name="payment_status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Sudah Lunas</option>
                    </select>
                </div>
            </div>

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-end">
                @if(request()->hasAny(['search', 'supplier_id', 'payment_status', 'start_date', 'end_date']))
                    <a href="{{ route('consignments.settlements.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Header -->
        <div class="px-6 pt-5 pb-3 bg-gradient-to-r from-purple-700 to-indigo-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Riwayat Settlement Konsinyasi</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $settlements->total() }} Faktur
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-800 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Settlement</th>
                        <th class="py-3 px-5 border-b border-white/10">Periode Penjualan</th>
                        <th class="py-3 px-5 border-b border-white/10">Supplier Penitip</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Omzet POS</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Hak Supplier</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Laba Komisi Toko</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status Bayar</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($settlements as $settlement)
                        <tr class="hover:bg-purple-50/30 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3.5 px-5">
                                <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $settlement->settlement_number }}</span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-600 dark:text-slate-300 font-medium">
                                {{ $settlement->start_date->format('d/m/y') }} s.d. {{ $settlement->end_date->format('d/m/y') }}
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $settlement->supplier?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-5 text-right font-semibold text-slate-700 dark:text-slate-300 font-mono">
                                Rp {{ number_format($settlement->total_gross_sales, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-black text-rose-600 dark:text-rose-400 font-mono">
                                Rp {{ number_format($settlement->total_supplier_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                Rp {{ number_format($settlement->total_store_commission, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                @if($settlement->payment_status === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20">
                                        Lunas
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/20">
                                        Belum Dibayar
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($settlement->payment_status === 'unpaid')
                                        <button 
                                            type="button" 
                                            onclick="openPaySettlementModal({{ $settlement->id }}, '{{ $settlement->settlement_number }}', '{{ addslashes($settlement->supplier?->name) }}', {{ $settlement->total_supplier_amount }})" 
                                            class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-xs flex items-center gap-1"
                                            title="Bayar ke Supplier"
                                        >
                                            <i data-lucide="wallet" class="w-3 h-3"></i>
                                            <span>Bayar</span>
                                        </button>
                                    @endif

                                    <a href="{{ route('consignments.settlements.print', $settlement) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition" title="Cetak Nota Settlement">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>

                                    <a href="{{ route('consignments.settlements.export-pdf', $settlement) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Download PDF">
                                        <i data-lucide="file-down" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="receipt-text" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600 dark:text-slate-300">Belum Ada Settlement Konsinyasi</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Buat Settlement Baru" untuk menarik data penjualan barang titipan dan menghitung bagi hasil.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($settlements->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $settlements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
@include('consignments.settlements._create_wizard_modal')
@include('consignments.settlements._pay_modal')
@endpush
