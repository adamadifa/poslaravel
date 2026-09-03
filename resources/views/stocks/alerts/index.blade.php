@extends('layouts.admin')

@section('title', 'Peringatan & Monitoring Stok - Mare POS')

@section('content')
<div class="space-y-6">

    <!-- Top Alert Metrics Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Low Stock Alert Card -->
        <a href="{{ route('stocks.alerts', ['tab' => 'low_stock']) }}" class="p-5 rounded-2xl border transition shadow-2xs flex items-center justify-between {{ $tab === 'low_stock' ? 'bg-rose-50/70 border-rose-300 ring-2 ring-rose-500/20' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="package-x" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Menipis / Di Bawah Min</div>
                    <div class="text-2xl font-black text-rose-600 font-mono-num mt-0.5">{{ $totalLowStockCount }} Produk</div>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-bold {{ $tab === 'low_stock' ? 'text-rose-700 bg-rose-100' : 'text-slate-500 bg-slate-100' }} px-3 py-1 rounded-full">
                    {{ $tab === 'low_stock' ? 'Sedang Aktif' : 'Lihat Data' }}
                </span>
            </div>
        </a>

        <!-- Expiring / Expired Batches Alert Card -->
        <a href="{{ route('stocks.alerts', ['tab' => 'expiring']) }}" class="p-5 rounded-2xl border transition shadow-2xs flex items-center justify-between {{ $tab === 'expiring' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 hover:border-slate-300' }}">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="clock-alert" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Mendekati / Lewat Expired</div>
                    <div class="text-2xl font-black text-amber-600 font-mono-num mt-0.5">{{ $totalExpiringCount }} Batch</div>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-bold {{ $tab === 'expiring' ? 'text-amber-700 bg-amber-100' : 'text-slate-500 bg-slate-100' }} px-3 py-1 rounded-full">
                    {{ $tab === 'expiring' ? 'Sedang Aktif' : 'Lihat Data' }}
                </span>
            </div>
        </a>

    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
        <form action="{{ route('stocks.alerts') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Outset Floating-label Search Input (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 hover:border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 bg-white transition px-4 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cari Produk / SKU / Batch
                </label>
                <div class="flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Ketik nama produk atau barcode..." 
                        class="w-full bg-transparent border-0 p-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:ring-0 focus:outline-none"
                    >
                </div>
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

            @if($tab === 'low_stock')
                <!-- Outset Floating-label Category Filter (Col 3) -->
                <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Kategori
                    </label>
                    <select name="category_id" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <!-- Outset Floating-label Days Threshold Filter (Col 3) -->
                <div class="lg:col-span-3 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                    <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                        Rentang Kedaluwarsa
                    </label>
                    <select name="days" onchange="this.form.submit()" class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-800 focus:ring-0 focus:outline-none cursor-pointer">
                        <option value="30" {{ request('days', 30) == 30 ? 'selected' : '' }}>Kedaluwarsa $\le$ 30 Hari</option>
                        <option value="60" {{ request('days') == 60 ? 'selected' : '' }}>Kedaluwarsa $\le$ 60 Hari</option>
                        <option value="90" {{ request('days') == 90 ? 'selected' : '' }}>Kedaluwarsa $\le$ 90 Hari</option>
                    </select>
                </div>
            @endif

            <!-- Reset Button (Col 1) -->
            <div class="lg:col-span-1 flex items-center justify-center">
                @if(request()->hasAny(['search', 'warehouse_id', 'category_id', 'days']))
                    <a href="{{ route('stocks.alerts', ['tab' => $tab]) }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- TAB 1: LOW STOCK TABLE -->
    @if($tab === 'low_stock')
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
                    <h3 class="font-black text-sm tracking-tight text-white uppercase">Daftar Produk Menipis di Bawah Minimum</h3>
                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                        {{ $lowStockProducts->total() }} Produk
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                            <th class="py-3 px-5 border-b border-white/10">Informasi Produk</th>
                            <th class="py-3 px-4 border-b border-white/10">Kategori</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Stok Fisik Riil</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Batas Min Stok</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Defisit / Kebutuhan PO</th>
                            <th class="py-3 px-5 text-right w-36 border-b border-white/10">Tindakan Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($lowStockProducts as $p)
                            @php
                                $cur = (float) $p->current_stock;
                                $min = (float) $p->min_stock;
                                $deficit = $min - $cur;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $p->code }} • Barcode: {{ $p->barcode ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-medium">
                                    {{ $p->category?->name ?? 'Uncategorized' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-black font-mono-num {{ $cur <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ number_format($cur, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $p->baseUnit?->name }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-700 font-mono-num">
                                    {{ number_format($min, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $p->baseUnit?->name }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-black text-rose-600 font-mono-num">
                                    -{{ number_format($deficit > 0 ? $deficit : 0, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $p->baseUnit?->name }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs border border-brand-200 transition">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                        <span>Buat PO</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto mb-2 text-emerald-400"></i>
                                    <p class="font-bold text-sm text-slate-600">Semua Stok Produk Aman</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Tidak ada produk yang berada di bawah kuantiti minimum persediaan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lowStockProducts->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $lowStockProducts->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: EXPIRING BATCHES TABLE -->
    @if($tab === 'expiring')
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="clock-alert" class="w-5 h-5 text-white"></i>
                    <h3 class="font-black text-sm tracking-tight text-white uppercase">Monitoring Batch Mendekati / Lewat Expired</h3>
                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-white font-bold text-xs">
                        {{ $expiringBatches->total() }} Batch
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-500 text-white/95 uppercase font-extrabold text-[10px] tracking-wider">
                            <th class="py-3 px-5 border-b border-white/10">Produk & No. Batch</th>
                            <th class="py-3 px-4 border-b border-white/10">Gudang</th>
                            <th class="py-3 px-4 border-b border-white/10">Tanggal Expired</th>
                            <th class="py-3 px-4 border-b border-white/10 text-center">Status Kedaluwarsa</th>
                            <th class="py-3 px-4 border-b border-white/10 text-right">Sisa Stok Batch</th>
                            <th class="py-3 px-5 text-right w-36 border-b border-white/10">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($expiringBatches as $b)
                            @php
                                $isPast = $b->expiry_date->isPast();
                                $daysDiff = (int) now()->diffInDays($b->expiry_date, false);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900">{{ $b->product?->name }}</div>
                                    <div class="text-[10px] text-brand-600 font-mono font-bold">{{ $b->batch_number ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-medium">
                                    {{ $b->warehouse?->name }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $b->expiry_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($isPast)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                            <i data-lucide="alert-octagon" class="w-3 h-3"></i> Sudah Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="clock" class="w-3 h-3"></i> {{ $daysDiff }} hari lagi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-black font-mono-num text-slate-900">
                                    {{ number_format($b->qty_remaining, 2) }} <span class="text-[10px] text-slate-400 font-normal">{{ $b->product?->baseUnit?->name }}</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <a href="{{ route('stock-adjustments.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition" title="Buat Penyesuaian Pengurangan Barang Expired">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Write-off</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i data-lucide="shield-check" class="w-10 h-10 mx-auto mb-2 text-emerald-400"></i>
                                    <p class="font-bold text-sm text-slate-600">Tidak Ada Batch Kedaluwarsa</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Semua persediaan batch aktif masih dalam masa aman.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expiringBatches->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $expiringBatches->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
