@extends('layouts.admin')

@section('title', 'Laporan Nilai Persediaan & Stok Barang')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Nilai Persediaan & Stok',
        'subtitle' => 'Pantau total aset nilai modal persediaan gudang (Qty × HPP), estimasi nilai omset jual, serta status stok minimum/kritis.',
        'exportPdfUrl' => route('reports.stocks.export-pdf', request()->query()),
        'exportExcelUrl' => route('reports.stocks.export-excel', request()->query())
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Nilai Modal Persediaan (HPP) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Nilai Modal Persediaan (HPP)</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($totalValuation, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Total aset fisik stok gudang saat ini (Qty × Harga Beli)
            </div>
        </div>

        <!-- Card 2: Potensi Nilai Jual -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Potensi Nilai Jual (Omset)</p>
                    <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($totalPotentialRevenue, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-emerald-600 font-semibold">
                Estimasi Laba Potensial: Rp {{ number_format($totalPotentialRevenue - $totalValuation, 0, ',', '.') }}
            </div>
        </div>

        <!-- Card 3: Total Unit Fisik Stok -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Kuantitas Fisik</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">{{ number_format($totalStockQty, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Unit</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Dari <strong>{{ $totalItemsCount }}</strong> SKU / variasi stok gudang
            </div>
        </div>

        <!-- Card 4: Stok Kritis / Perlu Restock -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Stok Kritis / Habis</p>
                    <h3 class="text-xl font-black {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1.5">{{ number_format($lowStockCount, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">Produk</span></h3>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] {{ $lowStockCount > 0 ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                {{ $lowStockCount > 0 ? 'Perlu segera dibuat Purchase Order!' : 'Semua stok dalam batas aman' }}
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.stocks') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Cari Produk (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nama Produk / SKU / Barcode
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Contoh: Aqua, PRD-0001, 899..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Gudang (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang
                </label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Kategori (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kategori
                </label>
                <select name="category_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Stok + Reset (Col 3) -->
            <div class="lg:col-span-3 flex items-center gap-2">
                <div class="relative flex-1 rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Status Stok
                    </label>
                    <select name="filter_stock" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Stok</option>
                        <option value="low" {{ $filterStock === 'low' ? 'selected' : '' }}>Stok Kritis (<= Min)</option>
                        <option value="out" {{ $filterStock === 'out' ? 'selected' : '' }}>Stok Habis (= 0)</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'warehouse_id', 'category_id', 'filter_stock']))
                    <a href="{{ route('reports.stocks') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- STOCK TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="boxes" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Rincian Stok Barang & Nilai Persediaan</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $stocks->total() }} Produk
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Diperbarui: {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Produk & SKU</th>
                        <th class="py-3 px-4 border-b border-white/10">Kategori</th>
                        <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Sisa Stok</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Stok Min</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">HPP Modal (Rp)</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Harga Jual (Rp)</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Total Nilai Persediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $s)
                    @php
                        $qty = (float) $s->quantity;
                        $min = (float) ($s->product->min_stock ?? 0);
                        $cost = (float) ($s->product->purchase_price ?? 0);
                        $price = (float) ($s->product->selling_price ?? 0);
                        $valuation = $qty * $cost;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-bold text-slate-900">{{ $s->product?->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $s->product?->code }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            {{ $s->product?->category?->name ?? 'Tanpa Kategori' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $s->warehouse?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <span class="font-bold {{ $qty <= 0 ? 'text-rose-600' : ($qty <= $min ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ number_format($qty, 0, ',', '.') }} {{ $s->product?->baseUnit?->name ?? 'Pcs' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">
                            {{ number_format($min, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 font-medium">
                            Rp {{ number_format($cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-800 font-semibold">
                            Rp {{ number_format($price, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-right font-bold text-brand-600">
                            Rp {{ number_format($valuation, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="boxes" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada data stok produk ditemukan.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter pencarian di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $stocks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
