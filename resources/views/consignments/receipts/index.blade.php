@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-100 dark:border-purple-500/20 shadow-2xs">
                    <i data-lucide="package-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900 dark:text-slate-100">Penerimaan Konsinyasi (Titip Jual)</h2>
                    <p class="text-xs text-slate-400">Pencatatan barang titipan supplier/mitra UMKM masuk toko tanpa menambah hutang dagang</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateReceiptModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-purple-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Terima Barang Titipan</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>

        <!-- Row 2: Filter Bar -->
        <form action="{{ route('consignments.receipts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 dark:border-slate-700 hover:border-slate-300 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-500/20 bg-white dark:bg-slate-900 transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                    Cari No. Penerimaan / Supplier
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: CNR-2026 atau Nama Mitra..." 
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

            <!-- Warehouse Filter + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white dark:bg-slate-900 px-1.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Gudang
                    </label>
                    <div class="flex items-center gap-2">
                        <i data-lucide="warehouse" class="w-4 h-4 text-slate-400 shrink-0"></i>
                        <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-0 focus:outline-none cursor-pointer">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('consignments.receipts.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
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
                <i data-lucide="package-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Penerimaan Konsinyasi</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $receipts->total() }} Dokumen
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-800 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Bukti Terima</th>
                        <th class="py-3 px-5 border-b border-white/10">Tanggal Terima</th>
                        <th class="py-3 px-5 border-b border-white/10">Supplier Penitip</th>
                        <th class="py-3 px-5 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Total Item</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Estimasi Nilai</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($receipts as $receipt)
                        <tr class="hover:bg-purple-50/30 dark:hover:bg-slate-800/50 transition">
                            <td class="py-3.5 px-5">
                                <span class="font-bold text-purple-600 dark:text-purple-400 font-mono">{{ $receipt->receipt_number }}</span>
                            </td>
                            <td class="py-3.5 px-5 text-slate-600 dark:text-slate-300 font-medium">
                                {{ $receipt->receipt_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $receipt->supplier?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-5 text-slate-600 dark:text-slate-300">
                                {{ $receipt->warehouse?->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-5 text-center font-bold text-slate-700 dark:text-slate-300">
                                {{ $receipt->total_items }} SKU ({{ number_format($receipt->total_quantity, 0) }} Pcs)
                            </td>
                            <td class="py-3.5 px-5 text-right font-black text-slate-900 dark:text-slate-100 font-mono">
                                Rp {{ number_format($receipt->total_estimated_value, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/20">
                                    Diterima
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('consignments.receipts.print', $receipt) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition" title="Cetak Surat Penerimaan">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600 dark:text-slate-300">Belum Ada Penerimaan Konsinyasi</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "+ Terima Barang Titipan" untuk mencatat barang konsinyasi masuk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('modals')
@include('consignments.receipts._create_modal')
@endpush
