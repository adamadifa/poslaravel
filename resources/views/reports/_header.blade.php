@php
    $currentRoute = Route::currentRouteName();
@endphp

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2.5">
            <span class="p-2 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            </span>
            {{ $title ?? 'Laporan & Analitik Bisnis' }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            {{ $subtitle ?? 'Pantau ringkasan omset, laba kotor, performa produk, dan transaksi secara real-time.' }}
        </p>
    </div>

    <!-- Quick Action / Export if available -->
    @if(isset($exportPdfUrl) || isset($exportExcelUrl))
    <div class="flex items-center gap-2">
        @if(isset($exportExcelUrl))
        <a href="{{ $exportExcelUrl }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 font-semibold text-xs transition border border-emerald-200/60 dark:border-emerald-500/20 shadow-xs">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
            <span>Export Excel (CSV)</span>
        </a>
        @endif

        @if(isset($exportPdfUrl))
        <a href="{{ $exportPdfUrl }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 font-semibold text-xs transition border border-rose-200/60 dark:border-rose-500/20 shadow-xs">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            <span>Export PDF</span>
        </a>
        @endif
    </div>
    @endif
</div>

<!-- Tabs Sub-Navigation -->
<div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 border-b border-slate-200 dark:border-slate-800 scrollbar-none">
    <a href="{{ route('reports.sales') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.sales' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="receipt" class="w-4 h-4"></i>
        <span>Penjualan Harian & Kasir</span>
    </a>

    <a href="{{ route('reports.sales.products') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.sales.products' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="package" class="w-4 h-4"></i>
        <span>Penjualan per Produk & Margin</span>
    </a>

    <a href="{{ route('reports.sales.categories') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.sales.categories' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="tag" class="w-4 h-4"></i>
        <span>Penjualan per Kategori</span>
    </a>

    <a href="{{ route('reports.sales.customers') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.sales.customers' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="users" class="w-4 h-4"></i>
        <span>Penjualan per Pelanggan</span>
    </a>

    <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1 shrink-0"></div>

    <a href="{{ route('reports.purchases') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.purchases' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="truck" class="w-4 h-4"></i>
        <span>Laporan Pembelian (PO)</span>
    </a>

    <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1 shrink-0"></div>

    <a href="{{ route('reports.stocks') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.stocks' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="boxes" class="w-4 h-4"></i>
        <span>Nilai Persediaan & Stok</span>
    </a>

    <a href="{{ route('reports.stock-opnames') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.stock-opnames' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
        <span>Hasil Stok Opname</span>
    </a>

    <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1 shrink-0"></div>

    <a href="{{ route('reports.profit-loss') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.profit-loss' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="line-chart" class="w-4 h-4"></i>
        <span>Laba Rugi (P&L)</span>
    </a>

    <a href="{{ route('reports.payables') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.payables' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="receipt-text" class="w-4 h-4"></i>
        <span>Hutang Supplier (AP)</span>
    </a>

    <a href="{{ route('reports.receivables') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.receivables' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="coins" class="w-4 h-4"></i>
        <span>Piutang Customer (AR)</span>
    </a>

    <a href="{{ route('reports.cash-flows') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.cash-flows' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="arrow-down-up" class="w-4 h-4"></i>
        <span>Arus Kas & Bank</span>
    </a>

    <a href="{{ route('reports.cashier-shifts') }}" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition flex items-center gap-2 {{ $currentRoute === 'reports.cashier-shifts' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
        <i data-lucide="user-check" class="w-4 h-4"></i>
        <span>Rekap Shift Kasir</span>
    </a>
</div>
