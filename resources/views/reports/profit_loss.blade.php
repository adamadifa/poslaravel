@extends('layouts.admin')

@section('title', 'Laporan Laba Rugi Sederhana')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    @include('reports._header', [
        'title' => 'Laporan Laba Rugi Sederhana (P&L)',
        'subtitle' => 'Ringkasan performa finansial: Pendapatan Penjualan Bersih dikurangi Beban HPP FIFO dan Biaya Operasional Kas Keluar.',
    ])

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Penjualan Bersih -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Pendapatan Penjualan Bersih</p>
                    <h3 class="text-xl font-black text-slate-900 mt-1.5">Rp {{ number_format($netSales, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Penjualan kotor Rp {{ number_format($grossSales, 0, ',', '.') }} (Diskon: Rp {{ number_format($salesDiscounts, 0, ',', '.') }})
            </div>
        </div>

        <!-- Card 2: HPP Barang Terjual -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total HPP FIFO Modal</p>
                    <h3 class="text-xl font-black text-amber-600 mt-1.5">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Laba Kotor (Gross Profit): <strong class="text-emerald-600">Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- Card 3: Biaya Operasional Kas Keluar -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Total Biaya Operasional</p>
                    <h3 class="text-xl font-black text-rose-600 mt-1.5">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="arrow-down-circle" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-500">
                Akumulasi pengeluaran kas operasional
            </div>
        </div>

        <!-- Card 4: Laba Bersih (Net Profit) -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500">Laba Bersih (Net Profit)</p>
                    <h3 class="text-xl font-black {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1.5">
                        {{ $netProfit < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($netProfit), 0, ',', '.') }}
                    </h3>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $netProfit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                Margin Bersih: {{ number_format($netProfitMargin, 1) }}%
            </div>
        </div>
    </div>

    <!-- FILTER SECTION (Outset Floating Label Standard) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('reports.profit-loss') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
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

            <!-- Gudang (Col 5) -->
            <div class="lg:col-span-5 relative rounded-xl border border-slate-200 bg-white px-3.5 pt-3 pb-2">
                <label class="absolute -top-2.5 left-3.5 bg-white px-1.5 text-[11px] font-bold text-slate-700">
                    Cabang / Gudang
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
                    <a href="{{ route('reports.profit-loss') }}" class="p-2.5 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 hover:bg-rose-50 transition shrink-0" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- P&L STATEMENT DETAIL CARD (Solid Orange Header Theme) -->
    <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="line-chart" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Laporan Laba Rugi Komprehensif</h3>
            </div>
            <span class="text-xs text-white/80 font-medium">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="p-6 divide-y divide-slate-100 text-xs space-y-4">
            <!-- Bagian 1: Pendapatan Penjualan -->
            <div class="pt-2">
                <div class="flex items-center justify-between font-extrabold text-sm text-slate-900 pb-2">
                    <span class="uppercase tracking-wider">1. Pendapatan Operasional (Sales)</span>
                    <span>Rp {{ number_format($netSales, 0, ',', '.') }}</span>
                </div>
                <div class="pl-4 space-y-1.5 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Penjualan Kotor (Gross Sales)</span>
                        <span>Rp {{ number_format($grossSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-rose-600">
                        <span>Potongan Diskon Penjualan</span>
                        <span>-Rp {{ number_format($salesDiscounts, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Harga Pokok Penjualan (HPP) -->
            <div class="pt-4">
                <div class="flex items-center justify-between font-extrabold text-sm text-amber-600 pb-2">
                    <span class="uppercase tracking-wider">2. Beban Pokok Penjualan (HPP FIFO)</span>
                    <span>-Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                </div>
                <div class="pl-4 space-y-1.5 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Biaya Pembelian & Persediaan Terkonsumsi</span>
                        <span>-Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <!-- Subtotal Laba Kotor -->
                <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between font-bold text-slate-900">
                    <span>LABA KOTOR (GROSS PROFIT)</span>
                    <span class="text-emerald-600 text-sm">Rp {{ number_format($grossProfit, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Bagian 3: Biaya Operasional / Pengeluaran Kas -->
            <div class="pt-4">
                <div class="flex items-center justify-between font-extrabold text-sm text-rose-600 pb-2">
                    <span class="uppercase tracking-wider">3. Biaya & Beban Operasional</span>
                    <span>-Rp {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                </div>
                <div class="pl-4 space-y-1.5 text-slate-600">
                    @forelse($expensesByCategory as $exp)
                    <div class="flex items-center justify-between">
                        <span>Beban: {{ ucfirst($exp->category ?: 'Operasional Umum') }}</span>
                        <span>-Rp {{ number_format($exp->total_expense, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-slate-400 italic">Tidak ada pengeluaran kas operasional tercatat pada periode ini.</div>
                    @endforelse
                </div>
            </div>

            <!-- Total Akhir: Laba Bersih -->
            <div class="pt-6">
                <div class="p-4 rounded-2xl bg-brand-50 border border-brand-200/80 flex items-center justify-between">
                    <div>
                        <h4 class="font-black text-base text-slate-900">LABA BERSIH TAHUN/PERIODE BERJALAN</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Laba Kotor - Total Biaya Operasional</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $netProfit < 0 ? '-Rp ' : 'Rp ' }}{{ number_format(abs($netProfit), 0, ',', '.') }}
                        </span>
                        <div class="text-[11px] font-bold text-slate-500 mt-0.5">
                            Margin Laba: {{ number_format($netProfitMargin, 1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
