@extends('layouts.admin')

@section('title', 'Transfer Stok Antar Gudang - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Title & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Transfer Stok Antar Gudang</h2>
                    <p class="text-xs text-slate-400">Mutasi pengiriman barang antar gudang cabang & konfirmasi penerimaan</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateTransferModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Transfer Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('stock-transfers.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor Transfer
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Contoh: TRF-2026 atau catatan..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Transfer
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan (In Transit)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai Diterima</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Outset Floating-label From Warehouse Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang Asal
                </label>
                <select name="from_warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Gudang Asal</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Outset Floating-label Reset Button (Col 2) -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Gudang Tujuan
                    </label>
                    <select name="to_warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['search', 'status', 'from_warehouse_id', 'to_warehouse_id', 'start_date', 'end_date']))
                    <a href="{{ route('stock-transfers.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- TRANSFERS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="arrow-left-right" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Mutasi Transfer Stok</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $transfers->total() }} Mutasi
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Transfer</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Rute Pengiriman (Asal $\rightarrow$ Tujuan)</th>
                        <th class="py-3 px-4 border-b border-white/10">Total Item</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Status</th>
                        <th class="py-3 px-5 text-right w-40 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transfers as $tr)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Transfer -->
                            <td class="py-3.5 px-5">
                                <div class="font-extrabold text-brand-600 font-mono tracking-tight">{{ $tr->transfer_number }}</div>
                                <div class="text-[10px] text-slate-400">Pengirim: {{ $tr->sender?->name ?? 'Admin' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $tr->transfer_date->format('d/m/Y') }}
                            </td>

                            <!-- Rute Gudang -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2 font-bold text-slate-800">
                                    <span>{{ $tr->fromWarehouse?->name }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                    <span class="text-brand-700">{{ $tr->toWarehouse?->name }}</span>
                                </div>
                                @if($tr->notes)
                                    <div class="text-[10px] text-slate-400 italic mt-0.5">{{ $tr->notes }}</div>
                                @endif
                            </td>

                            <!-- Total Item -->
                            <td class="py-3.5 px-4 font-bold text-slate-900 font-mono-num">
                                {{ $tr->items->count() }} Produk <span class="text-slate-400 font-normal">({{ number_format($tr->items->sum('quantity_sent'), 2) }} unit)</span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($tr->status === 'draft')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Draft
                                    </span>
                                @elseif($tr->status === 'in_transit')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="truck" class="w-3 h-3"></i> In Transit
                                    </span>
                                @elseif($tr->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3"></i> Selesai Diterima
                                    </span>
                                @elseif($tr->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Dispatch Button (If draft) -->
                                    @if($tr->status === 'draft')
                                        <form action="{{ route('stock-transfers.dispatch', $tr->id) }}" method="POST" class="inline" id="dispatch_trf_{{ $tr->id }}">
                                            @csrf
                                            <button type="button" onclick="confirmDelete('dispatch_trf_{{ $tr->id }}', 'Kirim Transfer {{ $tr->transfer_number }}?', 'Stok barang akan langsung dikurangi dari gudang asal!')" class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 font-bold text-[11px] transition cursor-pointer flex items-center gap-1">
                                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                                <span>Kirim</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Receive Button (If in_transit) -->
                                    @if($tr->status === 'in_transit')
                                        <button type="button" onclick="openReceiveModal({{ $tr->id }})" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-[11px] transition cursor-pointer flex items-center gap-1">
                                            <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                                            <span>Terima</span>
                                        </button>
                                    @endif

                                    <!-- Cancel / Delete Button -->
                                    @if(in_array($tr->status, ['draft', 'in_transit']))
                                        <form action="{{ route('stock-transfers.destroy', $tr->id) }}" method="POST" class="inline" id="delete_trf_{{ $tr->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_trf_{{ $tr->id }}', '{{ $tr->status === 'draft' ? 'Hapus Draft' : 'Batalkan Pengiriman' }} {{ $tr->transfer_number }}?', '{{ $tr->status === 'in_transit' ? 'Stok akan dikembalikan ke gudang asal!' : 'Data draft akan dihapus permanen.' }}')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="{{ $tr->status === 'draft' ? 'Hapus Draft' : 'Batalkan' }}">
                                                <i data-lucide="{{ $tr->status === 'draft' ? 'trash-2' : 'ban' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i data-lucide="arrow-left-right" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Transaksi Transfer Antar Gudang</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Buat Transfer Baru" di atas untuk memindahkan stok barang.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>

@push('modals')
    @include('stocks.transfers._create_modal')
@endpush

@endsection
