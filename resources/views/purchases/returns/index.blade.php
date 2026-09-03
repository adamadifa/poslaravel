@extends('layouts.admin')

@section('title', 'Retur Pembelian - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Search Bar & Primary Action Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Retur Pembelian</h2>
                    <p class="text-xs text-slate-400">Pengembalian barang ke supplier & pemotongan hutang dagang</p>
                </div>
            </div>

            <!-- Action Button -->
            <button 
                onclick="openCreateReturnModal()" 
                type="button" 
                class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white font-bold text-xs shadow-md shadow-brand-500/25 transition shrink-0 whitespace-nowrap cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Retur Baru</span>
            </button>
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('purchase-returns.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nomor Retur / Supplier
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik No. Retur atau supplier..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Status Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Status Retur
                </label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>

            <!-- Outset Floating-label Supplier Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Supplier
                </label>
                <select name="supplier_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Outset Floating-label Warehouse Filter + Reset Button (Col 2) -->
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

                @if(request()->hasAny(['search', 'supplier_id', 'status', 'warehouse_id']))
                    <a href="{{ route('purchase-returns.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- RETURNS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="rotate-ccw" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Retur Pembelian</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $returns->total() }} Transaksi
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">No. Retur</th>
                        <th class="py-3 px-4 border-b border-white/10">Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Supplier & Penerimaan</th>
                        <th class="py-3 px-4 border-b border-white/10">Alasan Retur</th>
                        <th class="py-3 px-4 text-right border-b border-white/10">Nilai Retur</th>
                        <th class="py-3 px-4 text-center border-b border-white/10">Status</th>
                        <th class="py-3 px-5 text-right w-24 border-b border-white/10">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($returns as $r)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Retur -->
                            <td class="py-3.5 px-5">
                                <div class="font-extrabold text-brand-600 font-mono tracking-tight">{{ $r->return_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $r->warehouse?->name ?? 'Gudang Utama' }}</div>
                            </td>

                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 font-semibold text-slate-700">
                                {{ $r->return_date->format('d/m/Y') }}
                            </td>

                            <!-- Supplier & GRN Ref -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $r->supplier?->name ?? '-' }}</div>
                                @if($r->receipt)
                                    <div class="text-[10px] text-slate-400 font-mono">Ref GRN: {{ $r->receipt->grn_number }}</div>
                                @else
                                    <div class="text-[10px] text-slate-400 italic">Retur Non-GRN</div>
                                @endif
                            </td>

                            <!-- Alasan -->
                            <td class="py-3.5 px-4 font-medium text-slate-600 max-w-xs truncate" title="{{ $r->reason }}">
                                {{ $r->reason ?? '-' }}
                            </td>

                            <!-- Nilai Retur -->
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 font-mono-num">
                                Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if($r->status === 'confirmed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Confirmed
                                    </span>
                                @elseif($r->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($r->status === 'confirmed')
                                        <form action="{{ route('purchase-returns.destroy', $r->id) }}" method="POST" class="inline" id="cancel_return_{{ $r->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('cancel_return_{{ $r->id }}', 'Batalkan Retur {{ $r->return_number }}?', 'Stok barang akan dikembalikan ke gudang dan status diubah jadi dibatalkan!')" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition cursor-pointer" title="Batalkan Retur & Kembalikan Stok">
                                                <i data-lucide="ban" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($r->status === 'cancelled')
                                        <form action="{{ route('purchase-returns.destroy', $r->id) }}" method="POST" class="inline" id="delete_return_{{ $r->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('delete_return_{{ $r->id }}', 'Hapus Data Retur {{ $r->return_number }}?', 'Data retur yang dibatalkan ini akan dihapus permanen dari sistem!')" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Hapus Permanen">
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
                                <i data-lucide="rotate-ccw" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Transaksi Retur Pembelian</p>
                                <p class="text-xs text-slate-400 mt-0.5">Klik tombol "Buat Retur Baru" di atas untuk mengembalikan barang rusak/cacat ke supplier.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>

@push('modals')
    @include('purchases.returns._create_modal')
@endpush

@endsection
