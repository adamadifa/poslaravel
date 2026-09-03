@extends('layouts.admin')

@section('title', 'Stok Opname - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Audit Stok Opname</h2>
                    <p class="text-xs text-slate-400">Pencocokan stok fisik vs sistem, audit selisih, dan rekonsiliasi otomatis</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateOpnameModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Stok Opname Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('stock-opnames.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor Opname / Catatan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: SO-2026 atau audit bulanan..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Opname
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Outset Floating-label Warehouse Filter + Reset (Col 4) -->
            <div class="lg:col-span-4 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang Lokasi Audit
                    </label>
                    <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['search', 'status', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('stock-opnames.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- OPNAME TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Dokumen Stok Opname</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $opnames->total() }} Dokumen
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Opname</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 border-b border-white/10">Item Diaudit</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Selisih (Qty)</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 text-right w-36 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($opnames as $op)
                        @php
                            $totalDiffQty = $op->items->sum('difference_qty');
                            $hasDiff = $op->items->where('difference_qty', '!=', 0)->count();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Opname -->
                            <td class="py-3.5 px-5">
                                <div class="font-extrabold text-brand-600 font-mono tracking-tight">{{ $op->opname_number }}</div>
                                <div class="text-[10px] text-slate-400">Oleh: {{ $op->conductor?->name ?? 'Admin' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $op->opname_date->format('d/m/Y') }}
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $op->warehouse?->name ?? 'Gudang Utama' }}
                            </td>

                            <!-- Item Diaudit -->
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                <span class="font-bold text-slate-900">{{ $op->items->count() }}</span> Produk
                                @if($hasDiff > 0)
                                    <span class="text-[10px] text-amber-600 font-bold ml-1">({{ $hasDiff }} selisih)</span>
                                @endif
                            </td>

                            <!-- Total Selisih Qty -->
                            <td class="py-3.5 px-4 text-right font-black font-mono-num {{ $totalDiffQty > 0 ? 'text-emerald-600' : ($totalDiffQty < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                                {{ $totalDiffQty > 0 ? '+' : '' }}{{ number_format($totalDiffQty, 2) }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($op->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Draft
                                    </span>
                                @elseif($op->status === 'in_progress')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        In Progress
                                    </span>
                                @elseif($op->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui (Completed)
                                    </span>
                                @elseif($op->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Approve Button (If draft or in_progress) -->
                                    @if(in_array($op->status, ['draft', 'in_progress']))
                                        <form action="{{ route('stock-opnames.approve', $op->id) }}" method="POST" class="inline" id="approve_opname_{{ $op->id }}">
                                            @csrf
                                            <button type="button" onclick="confirmDelete('approve_opname_{{ $op->id }}', 'Setujui Opname {{ $op->opname_number }}?', 'Sistem akan otomatis menyesuaikan kuantiti stok fisik gudang dan memposting kartu mutasi inventaris!')" class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer" title="Setujui & Posting Penyesuaian Stok">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <button type="button" onclick="openEditOpnameModal({{ $op->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" title="Edit Data Opname">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                    @endif

                                    <!-- Cancel / Delete Button -->
                                    @if(in_array($op->status, ['draft', 'in_progress']))
                                        <form action="{{ route('stock-opnames.destroy', $op->id) }}" method="POST" class="inline" id="delete_opname_{{ $op->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_opname_{{ $op->id }}', '{{ $op->status === 'draft' ? 'Hapus Draft' : 'Batalkan Opname' }} {{ $op->opname_number }}?', 'Tindakan ini tidak dapat dibatalkan.')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="{{ $op->status === 'draft' ? 'Hapus Draft' : 'Batalkan' }}">
                                                <i data-lucide="{{ $op->status === 'draft' ? 'trash-2' : 'ban' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="clipboard-check" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Dokumen Stok Opname</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Buat Stok Opname Baru" untuk memulai audit fisik persediaan barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>

</div>

@push('modals')
    @include('stocks.opnames._create_modal')
@endpush

@endsection
