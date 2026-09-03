@extends('layouts.admin')

@section('title', 'Penyesuaian Stok - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Penyesuaian Stok (Stock Adjustment)</h2>
                    <p class="text-xs text-slate-400">Koreksi persediaan manual untuk barang rusak, kedaluwarsa, atau bonus</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateAdjustmentModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Penyesuaian Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('stock-adjustments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor Penyesuaian / Alasan
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: ADJ-2026 atau barang rusak..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Type Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Tipe Penyesuaian
                </label>
                <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="addition" {{ request('type') === 'addition' ? 'selected' : '' }}>Penambahan (+)</option>
                    <option value="reduction" {{ request('type') === 'reduction' ? 'selected' : '' }}>Pengurangan (-)</option>
                </select>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Dokumen
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Outset Floating-label Warehouse Filter + Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang
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

                @if(request()->hasAny(['search', 'type', 'status', 'warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('stock-adjustments.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- ADJUSTMENTS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="sliders-horizontal" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Penyesuaian Stok</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $adjustments->total() }} Dokumen
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Penyesuaian</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 border-b border-white/10">Tipe & Alasan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Qty & Nilai</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 text-right w-36 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($adjustments as $adj)
                        @php
                            $totalQty = $adj->items->sum('quantity');
                            $totalVal = $adj->items->sum('total_cost');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Penyesuaian -->
                            <td class="py-3.5 px-5">
                                <div class="font-extrabold text-brand-600 font-mono tracking-tight">{{ $adj->adjustment_number }}</div>
                                <div class="text-[10px] text-slate-400">Oleh: {{ $adj->creator?->name ?? 'Admin' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $adj->adjustment_date->format('d/m/Y') }}
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                {{ $adj->warehouse?->name ?? 'Gudang Utama' }}
                            </td>

                            <!-- Tipe & Alasan -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    @if($adj->type === 'addition')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="plus" class="w-3 h-3"></i> TAMBAH
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="minus" class="w-3 h-3"></i> KURANG
                                        </span>
                                    @endif
                                    <span class="font-bold text-slate-800">{{ $adj->reason }}</span>
                                </div>
                            </td>

                            <!-- Total Qty & Nilai -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-black font-mono-num {{ $adj->type === 'addition' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $adj->type === 'addition' ? '+' : '-' }}{{ number_format($totalQty, 2) }} unit
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    Rp {{ number_format($totalVal, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($adj->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Draft
                                    </span>
                                @elseif($adj->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3"></i> Approved
                                    </span>
                                @elseif($adj->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Approve Button (If draft) -->
                                    @if($adj->status === 'draft')
                                        <form action="{{ route('stock-adjustments.approve', $adj->id) }}" method="POST" class="inline" id="approve_adj_{{ $adj->id }}">
                                            @csrf
                                            <button type="button" onclick="confirmDelete('approve_adj_{{ $adj->id }}', 'Setujui Penyesuaian {{ $adj->adjustment_number }}?', 'Stok fisik gudang akan langsung diperbarui dan mutasi terposting!')" class="p-1.5 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer" title="Setujui & Posting Stok">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <button type="button" onclick="openEditAdjustmentModal({{ $adj->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition cursor-pointer" title="Edit Penyesuaian">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Delete Draft Button -->
                                        <form action="{{ route('stock-adjustments.destroy', $adj->id) }}" method="POST" class="inline" id="delete_adj_{{ $adj->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_adj_{{ $adj->id }}', 'Hapus Draft {{ $adj->adjustment_number }}?', 'Data draft penyesuaian ini akan dihapus permanen.')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Draft">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Cancel Approved Adjustment Button -->
                                    @if($adj->status === 'approved')
                                        <form action="{{ route('stock-adjustments.destroy', $adj->id) }}" method="POST" class="inline" id="cancel_adj_{{ $adj->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('cancel_adj_{{ $adj->id }}', 'Batalkan Penyesuaian {{ $adj->adjustment_number }}?', 'Mutasi persediaan akan otomatis dibalik (stok dan batch dikembalikan ke kondisi semula)!')" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer" title="Batalkan & Kembalikan Stok">
                                                <i data-lucide="ban" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Delete Cancelled Record Button -->
                                    @if($adj->status === 'cancelled')
                                        <form action="{{ route('stock-adjustments.destroy', $adj->id) }}" method="POST" class="inline" id="delete_cancelled_adj_{{ $adj->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_cancelled_adj_{{ $adj->id }}', 'Hapus Data {{ $adj->adjustment_number }}?', 'Data penyesuaian yang telah dibatalkan ini akan dihapus permanen.')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Permanen">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="sliders-horizontal" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Dokumen Penyesuaian Stok</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Buat Penyesuaian Baru" di atas untuk mengoreksi stok barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($adjustments->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>

</div>

@push('modals')
    @include('stocks.adjustments._create_modal')
@endpush

@endsection
