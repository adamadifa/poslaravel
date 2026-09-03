@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- WARNING ALERTS BANNER (Stok Kritis & Piutang / Hutang Jatuh Tempo) -->
    @if(($lowStockCount ?? 0) > 0 || ($overdueReceivables ?? 0) > 0 || ($overduePayables ?? 0) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if(($lowStockCount ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200/80 dark:border-rose-500/20 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-rose-900 dark:text-rose-300">{{ $lowStockCount }} Produk Stok Kritis!</div>
                            <p class="text-[11px] text-rose-700 dark:text-rose-400 mt-0.5">Kuantitas di bawah stok minimum.</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.stocks', ['filter_stock' => 'low']) }}" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Cek Stok
                    </a>
                </div>
            @endif

            @if(($overdueReceivables ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/80 dark:border-amber-500/20 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="coins" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-amber-900 dark:text-amber-300">Piutang Overdue (>30 Hari)</div>
                            <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">Rp {{ number_format($overdueReceivables, 0, ',', '.') }} perlu ditagih.</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.receivables') }}" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Lihat AR
                    </a>
                </div>
            @endif

            @if(($overduePayables ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200/80 dark:border-blue-500/20 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-blue-900 dark:text-blue-300">Hutang Supplier Jatuh Tempo</div>
                            <p class="text-[11px] text-blue-700 dark:text-blue-400 mt-0.5">Rp {{ number_format($overduePayables, 0, ',', '.') }} tagihan PO.</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.payables') }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Lihat AP
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- ROW 1: TOP 4 STAT CARDS (Daily, Weekly, Monthly Sales & Active Transactions) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        
        <!-- Card 1: Penjualan Hari Ini -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Penjualan Hari Ini</h4>
                    <p class="text-[11px] text-slate-400">Total omset kasir terbayar</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-500/20">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 dark:text-white font-mono-num tracking-tight">
                    Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 dark:border-slate-800 pt-2 text-slate-500">
                <span>{{ $todayTransactions ?? 0 }} transaksi struk</span>
                <a href="{{ route('reports.sales') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold flex items-center gap-0.5">
                    <span>Laporan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: Penjualan Minggu Ini -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Penjualan Minggu Ini</h4>
                    <p class="text-[11px] text-slate-400">Akumulasi minggu berjalan</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-400 flex items-center justify-center border border-brand-100 dark:border-brand-500/20">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 dark:text-white font-mono-num tracking-tight">
                    Rp {{ number_format($thisWeekSales ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 dark:border-slate-800 pt-2 text-slate-500">
                <span>Senin - Minggu</span>
                <a href="{{ route('reports.sales') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold flex items-center gap-0.5">
                    <span>Detail</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 3: Penjualan Bulan Ini -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Penjualan Bulan Ini</h4>
                    <p class="text-[11px] text-slate-400">Omset bulan kalender</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 flex items-center justify-center border border-blue-100 dark:border-blue-500/20">
                    <i data-lucide="line-chart" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 dark:text-white font-mono-num tracking-tight">
                    Rp {{ number_format($thisMonthSales ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 dark:border-slate-800 pt-2 text-slate-500">
                <span>{{ now()->translatedFormat('F Y') }}</span>
                <a href="{{ route('reports.profit-loss') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold flex items-center gap-0.5">
                    <span>Laba Rugi</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 4: Total Produk & Member -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Katalog Produk & Member</h4>
                    <p class="text-[11px] text-slate-400">Data master aktif</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 flex items-center justify-center border border-amber-100 dark:border-amber-500/20">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 dark:text-white font-mono-num tracking-tight">
                    {{ number_format($totalProducts ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">SKU ({{ $totalCustomers ?? 0 }} Member)</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 dark:border-slate-800 pt-2 text-slate-500">
                <span>Persediaan siap jual</span>
                <a href="{{ route('reports.stocks') }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 font-bold flex items-center gap-0.5">
                    <span>Stok</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- ROW 2: 30 DAYS SALES TREND (STEP-LINE & AREA STYLED CHART) & BUSINESS TRAFFIC METRICS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left: Stepline & Gradient Sales Trend Chart (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xs relative">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 dark:bg-brand-500/10 border border-brand-100 dark:border-brand-500/20 flex items-center justify-center text-brand-500">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tren Performa Penjualan (30 Hari)</h3>
                        <p class="text-[11px] text-slate-400">Grafik omset harian kasir & transaksi POS</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Realtime Live
                    </span>
                </div>
            </div>

            <div id="salesTrendChart" class="w-full -ml-2"></div>
        </div>

        <!-- Right: Business Traffics, Jam Ramai, & Metode Pembayaran (5 cols) -->
        <div class="lg:col-span-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 flex items-center justify-center text-brand-500">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Jam Ramai Kasir (Peak Hours)</h3>
                            <p class="text-[11px] text-slate-400">Intensitas transaksi per jam operasional</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">08:00 - 22:00</span>
                </div>

                <!-- Bar Chart: Hourly Rush Traffic -->
                <div id="hourlyRushChart" class="w-full"></div>
            </div>

            <!-- Payment Method Multi-segment Breakdown Bar -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="font-bold text-slate-800 dark:text-slate-200">Distribusi Metode Bayar</span>
                    <span class="text-[11px] text-slate-400">Semua Waktu</span>
                </div>

                @php
                    $totalPayCount = $paymentDistribution->sum('count');
                @endphp
                <div class="flex h-3 rounded-full overflow-hidden gap-0.5 bg-slate-100 dark:bg-slate-800 mb-3">
                    @foreach($paymentDistribution as $idx => $pay)
                        @php
                            $pct = $totalPayCount > 0 ? (($pay->count / $totalPayCount) * 100) : 0;
                            $bgColors = ['bg-orange-500', 'bg-emerald-500', 'bg-blue-500', 'bg-purple-500'];
                            $bg = $bgColors[$idx % count($bgColors)];
                        @endphp
                        <div class="h-full {{ $bg }}" style="width: {{ $pct }}%" title="{{ strtoupper($pay->payment_method) }}: {{ number_format($pct, 1) }}%"></div>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    @foreach($paymentDistribution as $idx => $pay)
                        @php
                            $dotColors = ['bg-orange-500', 'bg-emerald-500', 'bg-blue-500', 'bg-purple-500'];
                            $dot = $dotColors[$idx % count($dotColors)];
                        @endphp
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-2 h-2 rounded-full {{ $dot }} shrink-0"></span>
                            <span class="truncate text-[11px] text-slate-600 dark:text-slate-400">{{ strtoupper($pay->payment_method) }}: <strong class="text-slate-900 dark:text-white">{{ $pay->count }}</strong></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- ROW 3: CATEGORY PERFORMANCE & RECENT ACTIVITY TRANSACTIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left: Donut Chart Penjualan per Kategori (5 cols) -->
        <div class="lg:col-span-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-50 dark:bg-orange-500/10 border border-orange-100 dark:border-orange-500/20 flex items-center justify-center text-brand-500">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Kontribusi Kategori Produk</h3>
                            <p class="text-[11px] text-slate-400">Pangsa omset per kategori produk</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.sales.categories') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Detail
                    </a>
                </div>

                <div id="categoryDonutChart" class="w-full flex justify-center py-2"></div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
                @foreach($categorySales as $idx => $cat)
                @php
                    $colors = ['#f97316', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'];
                    $c = $colors[$idx % count($colors)];
                @endphp
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $c }}"></span>
                        {{ $cat->category_name }}
                    </span>
                    <span class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($cat->total_revenue, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Realtime Transaksi Kasir Terbaru (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Aktivitas Transaksi Kasir Terkini</h3>
                            <p class="text-[11px] text-slate-400">Daftar struk penjualan real-time terbaru</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.sales') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Lihat Semua
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="text-slate-400 text-[10px] font-bold uppercase border-b border-slate-100 dark:border-slate-800 pb-2">
                                <th class="pb-2">Invoice</th>
                                <th class="pb-2">Pelanggan</th>
                                <th class="pb-2 text-center">Metode</th>
                                <th class="pb-2 text-right">Total</th>
                                <th class="pb-2 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($recentSales as $sale)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-2.5 font-bold font-mono text-brand-600 dark:text-brand-400">
                                    {{ $sale->invoice_number }}
                                </td>
                                <td class="py-2.5 text-slate-700 dark:text-slate-300">
                                    {{ $sale->customer->name ?? 'Pelanggan Umum' }}
                                </td>
                                <td class="py-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $sale->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' }}">
                                        {{ $sale->payment_method ?? 'CASH' }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right font-black font-mono-num text-slate-900 dark:text-white">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 text-right text-slate-400 text-[11px]">
                                    {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->diffForHumans() : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    Belum ada transaksi hari ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ROW 3.5: PERBANDINGAN PER CABANG / GUDANG (MULTI-CABANG ANALYTICS) -->
    @if(isset($outletSales) && count($outletSales) > 0)
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-6 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Perbandingan Penjualan per Cabang / Outlet</h3>
                    <p class="text-[11px] text-slate-400">Kontribusi omset dan volume struk tiap titik penjualan</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-400">{{ count($outletSales) }} Cabang Aktif</span>
        </div>

        @php
            $maxSales = $outletSales->max('total_sales') ?: 1;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($outletSales as $outlet)
            @php
                $pct = ($outlet->total_sales / $maxSales) * 100;
            @endphp
            <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $outlet->outlet_name }}</span>
                    <span class="text-emerald-600 font-extrabold">Rp {{ number_format($outlet->total_sales, 0, ',', '.') }}</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-500 to-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400">
                    <span>{{ $outlet->total_tx }} Transaksi Selesai</span>
                    <span>{{ number_format($pct, 1) }}% Kapasitas</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ROW 4: TOP 10 PRODUK TERLARIS TABLE CARD (Solid Orange Header Theme) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 pt-5 pb-3 bg-brand-500 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="award" class="w-5 h-5 text-white"></i>
                <h3 class="font-black text-sm tracking-tight text-white">Top 10 Produk Terlaris (Best Sellers)</h3>
            </div>
            <a href="{{ route('reports.sales.products') }}" class="text-xs font-bold text-white/90 hover:text-white underline">
                Lihat Laporan Margin Produk
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-brand-500 text-white/95 font-bold text-xs">
                        <th class="py-3 px-5 border-b border-white/10 w-12 text-center">Rank</th>
                        <th class="py-3 px-4 border-b border-white/10">Produk & SKU</th>
                        <th class="py-3 px-4 border-b border-white/10 text-center">Total Kuantitas Terjual</th>
                        <th class="py-3 px-5 border-b border-white/10 text-right">Total Nilai Omset (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($topProducts as $rank => $prod)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="py-3 px-5 text-center">
                            @if($rank === 0)
                                <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-black text-xs inline-flex items-center justify-center">1</span>
                            @elseif($rank === 1)
                                <span class="w-6 h-6 rounded-full bg-slate-400 text-white font-black text-xs inline-flex items-center justify-center">2</span>
                            @elseif($rank === 2)
                                <span class="w-6 h-6 rounded-full bg-amber-700 text-white font-black text-xs inline-flex items-center justify-center">3</span>
                            @else
                                <span class="text-slate-400 font-bold font-mono">{{ $rank + 1 }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $prod->product_name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $prod->product_code }}</div>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($prod->total_qty, 0, ',', '.') }} Unit
                        </td>
                        <td class="py-3 px-5 text-right font-black text-brand-600 dark:text-brand-400">
                            Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400">
                            Belum ada data penjualan produk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Theme Colors Adapter
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#1e293b' : '#f1f5f9';
        const textColor = isDark ? '#64748b' : '#94a3b8';

        // 1. Line / Area Stepline Chart: Tren Penjualan 30 Hari
        const trendDates = @json($trendDates);
        const trendSales = @json($trendSales);

        const salesTrendOptions = {
            series: [{
                name: 'Total Omset (Rp)',
                data: trendSales
            }],
            chart: {
                type: 'area',
                height: 290,
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            colors: ['#f97316'],
            stroke: {
                curve: 'stepline',
                width: 2.5,
                colors: ['#f97316']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100],
                    colorStops: [
                        { offset: 0, color: '#f97316', opacity: 0.4 },
                        { offset: 100, color: '#f97316', opacity: 0.0 }
                    ]
                }
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            xaxis: {
                categories: trendDates,
                labels: {
                    rotate: -45,
                    style: { fontSize: '10px', colors: textColor, fontWeight: 500 }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return 'Rp ' + (val / 1000).toLocaleString('id-ID') + 'k';
                    },
                    style: { fontSize: '10px', colors: textColor, fontWeight: 500 }
                }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
                    }
                }
            }
        };

        const salesChart = new ApexCharts(document.querySelector("#salesTrendChart"), salesTrendOptions);
        salesChart.render();

        // 2. Bar Chart: Hourly Rush Traffic POS
        const hourlyLabels = @json($hourlyLabels);
        const hourlyData = @json($hourlyData);

        const hourlyOptions = {
            series: [{
                name: 'Jumlah Transaksi',
                data: hourlyData
            }],
            chart: {
                type: 'bar',
                height: 180,
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '55%',
                    distributed: false
                }
            },
            colors: ['#3b82f6'],
            dataLabels: { enabled: false },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            xaxis: {
                categories: hourlyLabels,
                labels: {
                    style: { fontSize: '9px', colors: textColor }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', colors: textColor }
                }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        return val + ' Transaksi';
                    }
                }
            }
        };

        const hourlyChart = new ApexCharts(document.querySelector("#hourlyRushChart"), hourlyOptions);
        hourlyChart.render();

        // 3. Donut Chart: Penjualan per Kategori
        const categoryNames = @json($categorySales->pluck('category_name'));
        const categoryAmounts = @json($categorySales->pluck('total_revenue')->map(fn($v) => (float)$v));

        const donutOptions = {
            series: categoryAmounts.length > 0 ? categoryAmounts : [1],
            labels: categoryNames.length > 0 ? categoryNames : ['Belum Ada Transaksi'],
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            colors: ['#f97316', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%'
                    }
                }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        return 'Rp ' + parseInt(val).toLocaleString('id-ID');
                    }
                }
            }
        };

        const donutChart = new ApexCharts(document.querySelector("#categoryDonutChart"), donutOptions);
        donutChart.render();

        // Listen to Theme Toggle Event and update charts instantly
        window.addEventListener('themeChanged', function (e) {
            const isDarkNow = e.detail.theme === 'dark';
            const newGridColor = isDarkNow ? '#1e293b' : '#f1f5f9';
            const newTextColor = isDarkNow ? '#64748b' : '#94a3b8';
            const newTheme = isDarkNow ? 'dark' : 'light';

            salesChart.updateOptions({
                grid: { borderColor: newGridColor },
                xaxis: { labels: { style: { colors: newTextColor } } },
                yaxis: { labels: { style: { colors: newTextColor } } },
                tooltip: { theme: newTheme }
            });

            hourlyChart.updateOptions({
                grid: { borderColor: newGridColor },
                xaxis: { labels: { style: { colors: newTextColor } } },
                yaxis: { labels: { style: { colors: newTextColor } } },
                tooltip: { theme: newTheme }
            });

            donutChart.updateOptions({
                tooltip: { theme: newTheme }
            });
        });
    });
</script>
@endpush
