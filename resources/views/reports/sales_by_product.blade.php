@extends('layouts.admin')

@section('title', 'Laporan Penjualan per Produk & Margin')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Penjualan per Produk & Margin Laba',
        'subtitle' => 'Analisis performa produk terlaris, pendapatan kotor, total HPP persediaan, dan estimasi margin keuntungan per produk.',
        'exportExcelUrl' => route('reports.sales.export-excel', request()->query())
    ])

    <!-- KPI Metric Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Pendapatan Produk</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($sumRevenue, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Akumulasi penjualan kotor produk</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Modal / HPP Produk</p>
            <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($sumCost, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-500 mt-2">Biaya pokok barang terjual</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Total Laba Kotor (Profit)</p>
            <h3 class="text-xl font-black text-emerald-600 mt-1.5">Rp {{ number_format($sumProfit, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-emerald-600 mt-2 font-semibold">Pendapatan - HPP Modal</p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <p class="text-xs font-bold text-slate-500">Rata-rata Margin Keuntungan</p>
            <h3 class="text-xl font-black text-brand-600 mt-1.5">{{ number_format($sumMarginPercent, 1) }}%</h3>
            <p class="text-[11px] text-slate-500 mt-2">Persentase laba terhadap omset</p>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.sales.products') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Cari Produk (Col 4) -->
            <div class="lg:col-span-4 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Nama Produk / SKU
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}" 
                        placeholder="Contoh: Aqua, PRD-0001..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Dari Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Sampai Tanggal (Col 2) -->
            <div class="lg:col-span-2 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Kategori (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Kategori Produk
                </label>
                <select name="category_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['search', 'start_date', 'end_date', 'category_id', 'warehouse_id']))
                    <a href="{{ route('reports.sales.products') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- PRODUCT PERFORMANCE TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="package" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Rincian Performa & Margin Produk</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ $report->total() }} Produk
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Produk</th>
                        <th class="py-3 px-4 border-b border-white/10">Kategori</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Qty Terjual</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Penjualan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total HPP Modal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Laba Kotor (Rp)</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($report as $item)
                    @php
                        $marginPercent = $item->total_revenue > 0 ? (($item->gross_profit / $item->total_revenue) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5">
                            <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $item->product_code }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            {{ $item->category_name ?? 'Tanpa Kategori' }}
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ number_format($item->total_qty, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-900">
                            Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">
                            Rp {{ number_format($item->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600">
                            Rp {{ number_format($item->gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $marginPercent >= 25 ? 'bg-emerald-50 text-emerald-700' : ($marginPercent > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ number_format($marginPercent, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="package-search" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada data penjualan produk ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($report->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $report->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
