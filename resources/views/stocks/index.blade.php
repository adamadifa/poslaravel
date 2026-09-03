@extends('layouts.admin')

@section('title', 'Kartu Stok & FIFO - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Action & Filter Bar (2-Row Spacious Responsive Grid) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-4">
        
        <!-- Row 1: Header Info & Active Stock Summary -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100/60 shadow-2xs">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-900">Kartu Stok & Alokasi FIFO</h2>
                    <p class="text-xs text-slate-400">Audit mutasi persediaan masuk/keluar & pantau sisa batch FIFO</p>
                </div>
            </div>

            <!-- Current Stock Summary Card (If product selected) -->
            @if(!is_null($currentStock))
                <div class="px-4 py-2 rounded-xl bg-brand-50/70 border border-brand-200/80 flex items-center gap-3 shrink-0">
                    <i data-lucide="package-check" class="w-5 h-5 text-brand-600"></i>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Stok Fisik Saat Ini</div>
                        <div class="text-sm font-black text-brand-600 font-mono-num">{{ number_format($currentStock, 2) }} Unit Dasar</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="h-px bg-slate-100 w-full"></div>

        <!-- Row 2: Comprehensive Multi-Filter Bar -->
        <form action="{{ route('stocks.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Outset Floating-label Search Input (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Produk / Referensi
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama produk, kode, atau keterangan..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Outset Floating-label Product Select (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Filter Produk
                </label>
                <select name="product_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Outset Floating-label Warehouse Filter (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
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

            <!-- Outset Floating-label Type Filter + Reset (Col 2) -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Mutasi
                    </label>
                    <select name="type" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua</option>
                        <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk (IN)</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar (OUT)</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'product_id', 'warehouse_id', 'type', 'start_date', 'end_date']))
                    <a href="{{ route('stocks.index') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Active FIFO Batches Overview Section -->
    @if($batches->isNotEmpty())
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-6 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-brand-500"></i>
                    <h3 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">Sisa Batch Stok FIFO Aktif (Siap Dikonsumsi Kasir / Penjualan)</h3>
                </div>
                <span class="text-[11px] text-slate-400 font-semibold">Diurutkan berdasarkan tanggal masuk tertua</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/60 text-slate-700 font-extrabold text-[10px] uppercase border-b border-slate-200 tracking-wider">
                            <th class="py-2.5 px-5">Produk</th>
                            <th class="py-2.5 px-4">Gudang</th>
                            <th class="py-2.5 px-4 font-mono">No. Batch</th>
                            <th class="py-2.5 px-4">Tgl Masuk</th>
                            <th class="py-2.5 px-4">Expired Date</th>
                            <th class="py-2.5 px-4 text-right">Sisa Stok / Awal</th>
                            <th class="py-2.5 px-5 text-right">HPP Dasar (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($batches as $b)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-5 font-bold text-slate-800">{{ $b->product?->name }} <span class="text-[10px] text-slate-400 font-mono">({{ $b->product?->code }})</span></td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $b->warehouse?->name }}</td>
                                <td class="py-2.5 px-4 font-mono font-bold text-brand-600">{{ $b->batch_number ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-slate-600">{{ $b->entry_date ? $b->entry_date->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-4 text-slate-600">
                                    @if($b->expiry_date)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $b->expiry_date->isPast() ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $b->expiry_date->format('d/m/Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900 font-mono-num">
                                    {{ number_format($b->qty_remaining, 2) }} / {{ number_format($b->qty_in, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $b->product?->baseUnit?->name ?? 'Unit' }}</span>
                                </td>
                                <td class="py-2.5 px-5 text-right font-black text-slate-900 font-mono-num">
                                    Rp {{ number_format($b->unit_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MAIN STOCK MOVEMENT TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white uppercase">Riwayat Kartu Mutasi Stok</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $movements->total() }} Transaksi
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                        <th class="py-3 px-5 border-b border-white/10">Waktu & Tanggal</th>
                        <th class="py-3 px-4 border-b border-white/10">Produk</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 text-center border-b border-white/10">Tipe Mutasi</th>
                        <th class="py-3 px-4 text-right border-b border-white/10">Qty Mutasi</th>
                        <th class="py-3 px-4 text-right border-b border-white/10">Stok Sebelum $\rightarrow$ Sesudah</th>
                        <th class="py-3 px-5 border-b border-white/10">Keterangan / Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Waktu & Tanggal -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800">{{ $m->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i:s') }}</div>
                            </td>

                            <!-- Produk -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $m->product?->name }}</div>
                                <div class="text-[10px] text-brand-600 font-mono font-bold">{{ $m->product?->code }}</div>
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                {{ $m->warehouse?->name ?? 'Gudang Utama' }}
                            </td>

                            <!-- Tipe Mutasi -->
                            <td class="py-3.5 px-4 text-center">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i> MASUK
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i> KELUAR
                                    </span>
                                @endif
                            </td>

                            <!-- Qty Mutasi -->
                            <td class="py-3.5 px-4 text-right font-black font-mono-num {{ $m->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->type === 'in' ? '+' : '-' }}{{ number_format($m->quantity, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $m->product?->baseUnit?->name ?? 'Unit' }}</span>
                            </td>

                            <!-- Sebelum -> Sesudah -->
                            <td class="py-3.5 px-4 text-right font-mono-num text-slate-700 font-semibold">
                                <span class="text-slate-400">{{ number_format($m->before_stock, 2) }}</span>
                                <span class="text-slate-300 mx-1">$\rightarrow$</span>
                                <span class="font-black text-slate-900">{{ number_format($m->after_stock, 2) }}</span>
                            </td>

                            <!-- Keterangan / Referensi Link -->
                            <td class="py-3.5 px-5">
                                <div class="font-medium text-slate-800 leading-tight">{{ $m->description ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <span class="font-mono">{{ $m->reference_type }} #{{ $m->reference_id }}</span>
                                    <span>•</span>
                                    <span>Oleh: {{ $m->creator?->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="boxes" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-sm text-slate-600">Belum Ada Riwayat Mutasi Stok</p>
                                <p class="text-xs text-slate-400 mt-0.5">Semua pergerakan barang masuk/keluar kasir & pembelian akan tercatat otomatis di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
