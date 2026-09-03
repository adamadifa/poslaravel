@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    <!-- STOCK WARNING ALERTS BANNER (If any alerts exist) -->
    @if(($lowStockCount ?? 0) > 0 || ($expiringCount ?? 0) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if(($lowStockCount ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200/80 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="package-x" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-rose-900">Peringatan: {{ $lowStockCount }} Produk Stok Menipis!</div>
                            <p class="text-[11px] text-rose-700 mt-0.5">Stok fisik produk telah berada di bawah batas minimum.</p>
                        </div>
                    </div>
                    <a href="{{ route('stocks.alerts', ['tab' => 'low_stock']) }}" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Cek Produk
                    </a>
                </div>
            @endif

            @if(($expiringCount ?? 0) > 0)
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="clock-alert" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-amber-900">Perhatian: {{ $expiringCount }} Batch Mendekati Expired!</div>
                            <p class="text-[11px] text-amber-700 mt-0.5">Ada kelompok batch stok yang kedaluwarsa $\le$ 30 hari ke depan.</p>
                        </div>
                    </div>
                    <a href="{{ route('stocks.alerts', ['tab' => 'expiring']) }}" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition shrink-0">
                        Cek Batch
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- ROW 1: TOP 4 STAT CARDS (Real Business Operational Metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        
        <!-- Card 1: Penjualan Hari Ini -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Penjualan Hari Ini</h4>
                    <p class="text-[11px] text-slate-400">Total omset kasir terbayar</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 pt-2 text-slate-500">
                <span>{{ $todayTransactions ?? 0 }} transaksi berhasil</span>
                <a href="{{ url('/sales') }}" class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-0.5">
                    <span>Detail</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: Transaksi Kasir Hari Ini -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Transaksi Kasir</h4>
                    <p class="text-[11px] text-slate-400">Aktivitas struk penjualan POS</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-500 flex items-center justify-center border border-brand-100">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    {{ number_format($todayTransactions ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Struk</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 pt-2 text-slate-500">
                <span>Kasir aktif POS</span>
                <a href="{{ route('pos.index') }}" class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-0.5">
                    <span>Buka POS</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 3: Katalog Produk Aktif -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Katalog Produk</h4>
                    <p class="text-[11px] text-slate-400">Total SKU produk aktif</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    {{ number_format($totalProducts ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Item</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 pt-2 text-slate-500">
                <span>Multi satuan & barcode</span>
                <a href="{{ route('products.index') }}" class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-0.5">
                    <span>Kelola</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

        <!-- Card 4: Pelanggan Terdaftar -->
        <div class="rounded-2xl bg-white border border-slate-200/90 p-5 shadow-2xs flex flex-col justify-between min-h-[135px]">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Total Pelanggan</h4>
                    <p class="text-[11px] text-slate-400">Member & customer retail</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="my-2">
                <div class="text-2xl font-black text-slate-900 font-mono-num tracking-tight">
                    {{ number_format($totalCustomers ?? 0, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Member</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] border-t border-slate-100 pt-2 text-slate-500">
                <span>Tier member & poin</span>
                <a href="{{ route('customers.index') }}" class="text-brand-600 hover:text-brand-700 font-bold flex items-center gap-0.5">
                    <span>Lihat</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- ROW 2: WORKFLOW PERFORMANCE & BUSINESS TRAFFICS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Left Column: Workflow Performance Chart (7 cols) -->
        <div class="lg:col-span-7 rounded-2xl bg-white border border-slate-200/70 p-6 shadow-2xs transition-colors duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                        <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Workflow Performance</h3>
                        <p class="text-[11px] text-slate-400">Performance of your active automations</p>
                    </div>
                </div>
                <button class="text-slate-400 hover:text-slate-600 p-1">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Custom Interactive Chart Area -->
            <div class="relative w-full">
                <div id="workflowChart" class="w-full -ml-3"></div>

                <!-- Highlight Floating Tooltip Card -->
                <div class="absolute top-12 left-1/2 -translate-x-12 bg-white/95 backdrop-blur-xs border border-orange-200 rounded-xl p-3 shadow-md text-xs w-48 pointer-events-none hidden md:block">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-1.5">
                        <span class="text-[10px] text-slate-400 font-medium">05 December 25</span>
                    </div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-bold text-slate-800">98% Success</span>
                        <span class="text-[11px] text-emerald-600 font-bold flex items-center">↑ 16.2%</span>
                    </div>
                    <div class="space-y-0.5 text-[11px] text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Automation</span>
                            <span class="text-red-500 font-semibold">↓ 4.7%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Revenue</span>
                            <span class="text-emerald-600 font-semibold">↑ 20.9%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Business Traffics Progress & Breakdown (5 cols) -->
        <div class="lg:col-span-5 rounded-2xl bg-white border border-slate-200/70 p-6 shadow-2xs flex flex-col justify-between transition-colors duration-200">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Business Traffics</h3>
                            <p class="text-[11px] text-slate-400">Keep an eye to your business orders</p>
                        </div>
                    </div>
                    <button class="text-slate-400 hover:text-slate-600 p-1">
                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="font-bold text-slate-900">240K / 500K <span class="font-normal text-slate-400">M Traffic targets</span></span>
                    <span class="text-emerald-600 font-bold flex items-center gap-0.5">
                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i> 5.2% vs yesterday
                    </span>
                </div>

                <!-- Multi-segment Strip Progress Bar -->
                <div class="flex h-6 rounded-lg overflow-hidden gap-1 p-1 bg-slate-50 border border-slate-100 mb-6">
                    <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-sm" style="width: 72%"></div>
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-sm" style="width: 28%"></div>
                </div>

                <!-- Platforms Table -->
                <div class="space-y-3">
                    <div class="grid grid-cols-12 text-[11px] font-semibold text-slate-400 pb-2 border-b border-slate-100">
                        <div class="col-span-5">Platforms</div>
                        <div class="col-span-2 text-right">Yesterday</div>
                        <div class="col-span-3 text-right">Today</div>
                        <div class="col-span-2 text-right">Growth</div>
                    </div>

                    <!-- Row 1: Website -->
                    <div class="grid grid-cols-12 items-center text-xs py-1.5 hover:bg-slate-50 rounded-lg px-1 transition">
                        <div class="col-span-5 flex items-center gap-2 font-medium text-slate-800">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Website</span>
                        </div>
                        <div class="col-span-2 text-right text-slate-500 font-medium">1,23K</div>
                        <div class="col-span-3 text-right text-slate-800 font-bold">10,24K</div>
                        <div class="col-span-2 text-right text-emerald-600 font-bold text-[11px]">↑ 34.7%</div>
                    </div>

                    <!-- Row 2: Marketplace -->
                    <div class="grid grid-cols-12 items-center text-xs py-1.5 hover:bg-slate-50 rounded-lg px-1 transition">
                        <div class="col-span-5 flex items-center gap-2 font-medium text-slate-800">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Marketplace</span>
                        </div>
                        <div class="col-span-2 text-right text-slate-500 font-medium">590</div>
                        <div class="col-span-3 text-right text-slate-800 font-bold">180K</div>
                        <div class="col-span-2 text-right text-emerald-600 font-bold text-[11px]">↑ 80.5%</div>
                    </div>

                    <!-- Row 3: Retail POS -->
                    <div class="grid grid-cols-12 items-center text-xs py-1.5 hover:bg-slate-50 rounded-lg px-1 transition">
                        <div class="col-span-5 flex items-center gap-2 font-medium text-slate-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <i data-lucide="store" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>Retail POS</span>
                        </div>
                        <div class="col-span-2 text-right text-slate-500 font-medium">986</div>
                        <div class="col-span-3 text-right text-slate-800 font-bold">598</div>
                        <div class="col-span-2 text-right text-red-500 font-bold text-[11px]">↓ 15.6%</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ROW 3: CONVERSION FUNNEL, CUSTOMER SENTIMENT, & ACTIVITIES FEEDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        
        <!-- Card 1: Conversion Funnel -->
        <div class="rounded-2xl bg-white border border-slate-200/70 p-6 shadow-2xs flex flex-col justify-between transition-colors duration-200">
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Conversion Funnel</h3>
                        <p class="text-[11px] text-slate-400">Track how users move through your business flow</p>
                    </div>
                </div>

                <!-- Tabs Filter -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-100/80 rounded-xl mb-6 text-xs font-medium">
                    <button class="flex-1 py-1.5 rounded-lg bg-white shadow-2xs text-slate-800 font-semibold text-center">Website</button>
                    <button class="flex-1 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 text-center">Marketplace</button>
                    <button class="flex-1 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 text-center">Retails</button>
                </div>

                <!-- Funnel Stats Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Visitors</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900">3,218</div>
                    </div>

                    <div class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                            <span>Added to Cart</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900">412</div>
                    </div>

                    <div class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            <span>Checkout Started</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900">184</div>
                    </div>

                    <div class="p-3.5 bg-slate-50/70 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-1">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>Completed Orders</span>
                        </div>
                        <div class="text-xl font-bold text-slate-900">118</div>
                    </div>
                </div>
            </div>

            <a href="#" class="mt-6 flex items-center justify-between text-xs font-semibold text-slate-700 hover:text-brand-600 transition pt-3 border-t border-slate-100">
                <span>See monthly reports</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </a>
        </div>

        <!-- Card 2: Customer Sentiment -->
        <div class="rounded-2xl bg-white border border-slate-200/70 p-6 shadow-2xs flex flex-col justify-between transition-colors duration-200">
            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                        <i data-lucide="smile" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Customer Sentiment</h3>
                        <p class="text-[11px] text-slate-400">Aggregated from emails + reviews + chats</p>
                    </div>
                </div>

                <!-- Gauge Chart -->
                <div class="relative flex flex-col items-center justify-center my-2">
                    <div id="sentimentGauge" class="w-full"></div>
                    <div class="absolute inset-x-0 bottom-6 flex flex-col items-center justify-center">
                        <div class="text-3xl font-extrabold text-slate-900 tracking-tight">4.6 <span class="text-base font-semibold text-slate-400">/ 5.0</span></div>
                        <div class="text-[11px] font-medium text-slate-400">This month's score</div>
                    </div>
                </div>
            </div>

            <!-- Sentiment Breakdown Tags -->
            <div class="grid grid-cols-3 gap-2 text-center pt-3 border-t border-slate-100 text-xs">
                <div class="p-2 rounded-xl bg-orange-50/50">
                    <div class="flex items-center justify-center gap-1 text-[11px] text-slate-500 mb-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Positive
                    </div>
                    <div class="font-bold text-slate-800 text-sm">82%</div>
                </div>

                <div class="p-2 rounded-xl bg-amber-50/50">
                    <div class="flex items-center justify-center gap-1 text-[11px] text-slate-500 mb-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Neutral
                    </div>
                    <div class="font-bold text-slate-800 text-sm">11%</div>
                </div>

                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="flex items-center justify-center gap-1 text-[11px] text-slate-500 mb-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Negative
                    </div>
                    <div class="font-bold text-slate-800 text-sm">5%</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Activities Feeds -->
        <div class="rounded-2xl bg-white border border-slate-200/70 p-6 shadow-2xs flex flex-col justify-between transition-colors duration-200">
            <div>
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-7 h-7 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-brand-500">
                        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Activities Feeds</h3>
                        <p class="text-[11px] text-slate-400">Stay updated with your business is doing in real time</p>
                    </div>
                </div>

                <!-- Feed Items -->
                <div class="space-y-4">
                    
                    <!-- Item 1: AI Generated Report -->
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shrink-0 mt-0.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand-500"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-700 leading-snug">AI generated a performance report for <strong class="text-slate-900 font-semibold">Sales Overview</strong>.</p>
                            <span class="text-[10px] text-slate-400">2m ago</span>
                        </div>
                    </div>

                    <!-- Item 2: User Action -->
                    <div class="flex items-start gap-3 text-xs">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80" alt="Avatar" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5">
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-700 leading-snug"><strong class="text-slate-900 font-semibold">Mike Rowen</strong> changed the execution time in <span class="text-slate-900 font-medium">Sales</span>.</p>
                            <span class="text-[10px] text-slate-400">10m ago</span>
                        </div>
                    </div>

                    <!-- Item 3: Workflow Sync -->
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shrink-0 mt-0.5">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-700 leading-snug">Workflow <strong class="text-slate-900 font-semibold">Invoice Sync</strong> completed successfully.</p>
                            <span class="text-[10px] text-slate-400">17m ago</span>
                        </div>
                    </div>

                    <!-- Item 4: AI Anomaly -->
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600 shrink-0 mt-0.5">
                            <i data-lucide="trending-down" class="w-3.5 h-3.5 text-red-500"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-700 leading-snug">AI flagged an anomaly in <span class="text-slate-900 font-medium">Monthly Revenue Trend</span>.</p>
                            <span class="text-[10px] text-slate-400">45m ago</span>
                        </div>
                    </div>

                    <!-- Item 5: Another User -->
                    <div class="flex items-start gap-3 text-xs">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" alt="Avatar" class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5">
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-700 leading-snug"><strong class="text-slate-900 font-semibold">Natalie</strong> set revenue gap up to 10% in <span class="text-slate-900 font-medium">Sales Automation</span>.</p>
                            <span class="text-[10px] text-slate-400">1h ago</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        function getChartColors() {
            const isDark = document.documentElement.classList.contains('dark');
            return {
                gridColor: isDark ? '#1e293b' : '#f1f5f9',
                textColor: isDark ? '#64748b' : '#94a3b8',
                trackColor: isDark ? '#1e293b' : '#f1f5f9'
            };
        }

        let themeColors = getChartColors();

        // 1. Workflow Performance Stepped Chart
        var workflowOptions = {
            series: [{
                name: 'Automation',
                data: [30, 80, 20, 85, 20, 60, 60]
            }],
            chart: {
                type: 'line',
                height: 230,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            stroke: {
                curve: 'stepline',
                width: 2.5,
                colors: ['#f97316']
            },
            colors: ['#f97316'],
            grid: {
                borderColor: themeColors.gridColor,
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } }
            },
            xaxis: {
                categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: themeColors.textColor, fontSize: '11px', fontWeight: 500 }
                }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 5,
                labels: {
                    style: { colors: themeColors.textColor, fontSize: '11px', fontWeight: 500 }
                }
            },
            tooltip: {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        return val + "% Performance";
                    }
                }
            }
        };

        var workflowChart = new ApexCharts(document.querySelector("#workflowChart"), workflowOptions);
        workflowChart.render();


        // 2. Customer Sentiment Radial Gauge
        var gaugeOptions = {
            series: [92],
            chart: {
                type: 'radialBar',
                height: 250,
                offsetY: -10,
                sparkline: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: themeColors.trackColor,
                        strokeWidth: '97%',
                        margin: 5,
                    },
                    dataLabels: {
                        name: { show: false },
                        value: { show: false }
                    }
                }
            },
            grid: {
                padding: { top: -10 }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    shadeIntensity: 0.4,
                    inverseColors: false,
                    gradientToColors: ['#f97316'],
                    stops: [0, 50, 65, 91]
                },
                colors: ['#fb923c']
            },
            labels: ['Sentiment'],
        };

        var sentimentChart = new ApexCharts(document.querySelector("#sentimentGauge"), gaugeOptions);
        sentimentChart.render();

        // Listen for theme toggle to adapt chart grid & colors
        window.addEventListener('themeChanged', function (e) {
            const isDark = e.detail.theme === 'dark';
            workflowChart.updateOptions({
                grid: { borderColor: isDark ? '#1e293b' : '#f1f5f9' },
                xaxis: { labels: { style: { colors: isDark ? '#64748b' : '#94a3b8' } } },
                yaxis: { labels: { style: { colors: isDark ? '#64748b' : '#94a3b8' } } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            });
            sentimentChart.updateOptions({
                plotOptions: {
                    radialBar: {
                        track: { background: isDark ? '#1e293b' : '#f1f5f9' }
                    }
                }
            });
        });

    });
</script>
@endpush
