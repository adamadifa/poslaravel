@extends('layouts.admin')

@section('title', 'Laporan Penjualan per Kategori')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Penjualan per Kategori',
        'subtitle' => 'Ringkasan kontribusi pendapatan, volume produk, dan perolehan laba kotor berdasarkan kategori barang.',
    ])

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.sales.categories') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Dari Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Dari Tanggal
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Sampai Tanggal (Col 3) -->
            <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Sampai Tanggal
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
            </div>

            <!-- Gudang / Cabang (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Gudang / Cabang
                </label>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                    <option value="">Semua Cabang / Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['start_date', 'end_date', 'warehouse_id']))
                    <a href="{{ route('reports.sales.categories') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- CATEGORY TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        
        <!-- Solid Orange Card Header -->
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="tag" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Kontribusi Omset & Margin Kategori</h3>
                <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                    {{ count($categoriesReport) }} Kategori
                </span>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10">Nama Kategori</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Variasi Produk Terjual</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Total Item (Qty)</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total Pendapatan</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Total HPP Modal</th>
                        <th class="py-3 px-4 border-b border-white/10 text-right">Laba Kotor</th>
                        <th class="py-3 px-5 border-b border-white/10 text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categoriesReport as $cat)
                    @php
                        $margin = $cat->total_revenue > 0 ? (($cat->gross_profit / $cat->total_revenue) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-5 font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            {{ $cat->category_name }}
                        </td>
                        <td class="py-3 px-4 text-center text-slate-600 font-semibold">
                            {{ $cat->unique_products_count }} Produk
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ number_format($cat->total_qty, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-900">
                            Rp {{ number_format($cat->total_revenue, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">
                            Rp {{ number_format($cat->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-emerald-600">
                            Rp {{ number_format($cat->gross_profit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold {{ $margin >= 25 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ number_format($margin, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="tag" class="w-10 h-10 text-slate-300 mb-2"></i>
                                <p class="text-sm font-semibold">Tidak ada transaksi kategori pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
