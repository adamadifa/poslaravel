<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - POS Retail Pro</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS & JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- ApexCharts for rich analytics charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr (Modern Datepicker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <!-- Select2 (Modern Searchable Select) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- JsBarcode (High-Resolution Vector SVG Barcode Generator) -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <!-- Dark Mode Initializer (Prevents Flash of Wrong Theme) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Select2 Modern Tailwind Theme */
        .select2-container {
            width: 100% !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: transparent !important;
            border: none !important;
            height: auto !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            cursor: pointer !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
            line-height: 1.25rem !important;
            width: 100% !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-weight: 500 !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #64748b !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 0 !important;
            top: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #94a3b8 transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #f97316 !important;
            border-width: 0 4px 5px 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            color: #94a3b8 !important;
            font-size: 14px !important;
            font-weight: bold !important;
            margin-right: 6px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #f43f5e !important;
        }

        /* Select2 Dropdown Popup */
        .select2-dropdown {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.875rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            background-color: #ffffff !important;
            overflow: hidden !important;
            z-index: 999999 !important;
            padding: 6px !important;
        }
        .dark .select2-dropdown {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 4px 4px 6px 4px !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.625rem !important;
            padding: 7px 12px !important;
            font-size: 0.75rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            outline: none !important;
            width: 100% !important;
            background-color: #f8fafc !important;
            color: #0f172a !important;
            transition: all 0.15s ease-in-out !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #f97316 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #f97316 !important;
            background-color: #0f172a !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.25) !important;
        }
        .select2-results__options {
            max-height: 220px !important;
            overflow-y: auto !important;
            padding: 2px 0 !important;
        }
        .select2-results__option {
            padding: 7px 12px !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            border-radius: 0.5rem !important;
            margin-bottom: 2px !important;
            color: #334155 !important;
            cursor: pointer !important;
            transition: all 0.1s ease-in-out !important;
        }
        .dark .select2-results__option {
            color: #cbd5e1 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f97316 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #ffedd5 !important;
            color: #ea580c !important;
            font-weight: 700 !important;
        }
        .dark .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #7c2d12 !important;
            color: #ffedd5 !important;
        }
        .select2-container--default .select2-results__option[aria-disabled=true] {
            color: #94a3b8 !important;
            cursor: not-allowed !important;
        }
        .select2-results__message {
            font-size: 0.75rem !important;
            color: #94a3b8 !important;
            padding: 8px 12px !important;
            text-align: center !important;
        }
        /* Ensure floating labels stay cleanly on top of inputs/selects */
        .relative > label.absolute {
            z-index: 10 !important;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .dark ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /* Custom Flatpickr Airbnb Theme Tweaks */
        .flatpickr-calendar {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border-radius: 1rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            z-index: 99999 !important;
        }
        .dark .flatpickr-calendar {
            background: #0f172a !important;
            border-color: #334155 !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
        }
        .dark .flatpickr-calendar .flatpickr-month {
            background: #0f172a !important;
            color: #f8fafc !important;
            fill: #f8fafc !important;
        }
        .dark .flatpickr-calendar .flatpickr-weekday {
            background: #0f172a !important;
            color: #94a3b8 !important;
        }
        .dark .flatpickr-calendar .flatpickr-day {
            color: #cbd5e1 !important;
        }
        .dark .flatpickr-calendar .flatpickr-day.flatpickr-disabled {
            color: #475569 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #f97316 !important;
            border-color: #f97316 !important;
            color: #ffffff !important;
        }
        .flatpickr-day.today {
            border-color: #f97316 !important;
        }
        .flatpickr-day:hover {
            background: #ffedd5 !important;
        }
        .dark .flatpickr-day:hover {
            background: #1e293b !important;
            color: #f97316 !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-current-month input.cur-year {
            font-weight: 700 !important;
        }
        .dark .flatpickr-current-month .flatpickr-monthDropdown-months, .dark .flatpickr-current-month input.cur-year {
            color: #f8fafc !important;
        }
        .flatpickr-input[readonly] {
            cursor: pointer !important;
        }

        /* Sidebar collapse transition */
        #sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
        }
        .sidebar-collapsed {
            width: 5rem !important; /* w-20 */
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .section-header,
        .sidebar-collapsed .sidebar-profile-text,
        .sidebar-collapsed .brand-text,
        .sidebar-collapsed .profile-progress {
            display: none !important;
        }
        .sidebar-collapsed .nav-item {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        .sidebar-collapsed .brand-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-collapsed .user-profile-box {
            justify-content: center !important;
            padding: 0.5rem 0 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="h-screen w-screen overflow-hidden text-slate-800 dark:text-slate-100 antialiased bg-[#f8fafc] dark:bg-slate-950 p-0 m-0 transition-colors duration-200">

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Edge-to-Edge True Full Screen Flex Layout -->
    <div class="h-full w-full bg-[#f8fafc] dark:bg-slate-950 flex overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 lg:static w-64 bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 flex flex-col justify-between shrink-0 p-6 select-none -translate-x-full lg:translate-x-0 h-full overflow-y-auto overflow-x-hidden transition-colors duration-200">
            <div>
                <!-- Brand Header -->
                <div class="brand-header flex items-center justify-between px-1 h-12 mb-6">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if(!empty($appLogoSetting))
                            <img src="{{ asset('storage/' . $appLogoSetting) }}" alt="{{ $appNameSetting ?? 'Logo' }}" class="w-9 h-9 rounded-xl object-contain bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 p-1 shadow-xs shrink-0">
                        @else
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-lg shadow-sm shadow-brand-500/30 shrink-0">
                                <i data-lucide="zap" class="w-5 h-5 fill-white stroke-white"></i>
                            </div>
                        @endif
                        <div class="brand-text flex items-center gap-1 min-w-0">
                            <span class="font-bold text-lg tracking-tight text-slate-900 dark:text-white truncate" title="{{ $appNameSetting ?? 'WarungPro' }}">{{ $appNameSetting ?? 'WarungPro' }}</span>
                        </div>
                    </div>
                    
                    <!-- Desktop Minimize Toggle Button -->
                    <button id="toggleSidebarBtn" title="Toggle Sidebar" class="hidden lg:flex text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-100 items-center justify-center">
                        <i data-lucide="panel-left-close" class="w-4 h-4"></i>
                    </button>

                    <!-- Mobile Close Button -->
                    <button id="closeMobileSidebarBtn" class="lg:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Main Nav -->
                <div class="space-y-1">
                    @can('dashboard.view')
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="nav-item flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-semibold text-xs transition {{ request()->routeIs('dashboard') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-200/50 dark:border-brand-500/20 shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="layout-grid" class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-500 dark:text-brand-400' : 'text-slate-400' }}"></i>
                        <span class="nav-text truncate font-bold">Dashboard</span>
                    </a>
                    @endcan
                    
                    @can('sales.pos')
                    <a href="{{ route('pos.index') }}" title="Kasir / POS" class="nav-item flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <i data-lucide="shopping-cart" class="w-4 h-4 shrink-0 text-slate-400"></i>
                        <span class="nav-text truncate">Kasir POS (F12)</span>
                    </a>
                    @endcan

                    <!-- Section: Master Data -->
                    @canany(['products.view', 'categories.view', 'units.view', 'customers.view', 'suppliers.view', 'warehouses.view'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Master Data</p>
                        
                        @can('products.view')
                        <a href="{{ route('products.index') ?? url('/products') }}" title="Produk & Barcode" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('products*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="package" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Master Produk</span>
                        </a>
                        @endcan

                        @can('products.view')
                        <a href="{{ route('raw-materials.index') }}" title="Bahan Baku Mentah" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('raw-materials*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="boxes" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Bahan Baku (Raw)</span>
                        </a>
                        @endcan

                        @can('categories.view')
                        <a href="{{ route('categories.index') ?? url('/categories') }}" title="Kategori" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('categories*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="folder-tree" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Kategori</span>
                        </a>
                        @endcan

                        @can('units.view')
                        <a href="{{ route('units.index') ?? url('/units') }}" title="Satuan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('units*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="scale" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Satuan</span>
                        </a>
                        @endcan

                        @can('customers.view')
                        <a href="{{ route('customers.index') }}" title="Pelanggan & Member" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('customers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="user-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pelanggan & Member</span>
                        </a>
                        @endcan

                        @can('suppliers.view')
                        <a href="{{ route('suppliers.index') ?? url('/suppliers') }}" title="Pemasok / Supplier" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('suppliers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pemasok (Supplier)</span>
                        </a>
                        @endcan

                        @can('warehouses.view')
                        <a href="{{ route('warehouses.index') ?? url('/warehouses') }}" title="Gudang & Cabang" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('warehouses*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="warehouse" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Gudang & Cabang</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany

                    <!-- Section: Resto & Kuliner (F&B) -->
                    @if(in_array(\App\Models\Setting::get('business_type', 'retail'), ['fnb', 'hybrid']))
                    @canany(['tables.view', 'tables.reservations', 'modifiers.manage', 'kitchen.view', 'recipes.view'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-amber-500 dark:text-amber-400 uppercase mb-1.5">Resto & F&B</p>

                        @can('tables.view')
                        <a href="{{ route('tables.index') }}" title="Denah & Meja Resto" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('tables.index') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="layout-grid" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Denah & Meja Resto</span>
                        </a>
                        @endcan

                        @can('tables.reservations')
                        <a href="{{ route('tables.reservations') }}" title="Reservasi Meja" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('tables.reservations*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="calendar" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Reservasi Meja</span>
                        </a>
                        @endcan

                        @can('recipes.view')
                        <a href="{{ route('products.index') }}?type=fnb" title="Resep Menu & BOM" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->get('type') === 'fnb' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="chef-hat" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Resep Menu (BOM)</span>
                        </a>
                        @endcan

                        @can('products.view')
                        <a href="{{ route('raw-materials.index') }}" title="Bahan Baku Dapur" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('raw-materials*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="boxes" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Bahan Baku (Dapur)</span>
                        </a>
                        @endcan

                        @can('modifiers.manage')
                        <a href="{{ route('modifiers.index') }}" title="Menu Modifiers" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('modifiers*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="sliders" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Modifiers & Topping</span>
                        </a>
                        @endcan

                        @can('kitchen.view')
                        <a href="{{ route('kitchen.index') }}" title="Layar Dapur (KDS)" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('kitchen*') ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="flame" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Layar Dapur (KDS)</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany
                    @endif

                    <!-- Section: Layanan & Jasa (Service) -->
                    @if(in_array(\App\Models\Setting::get('business_type', 'retail'), ['service', 'hybrid']))
                    @canany(['service_queue.view', 'service_bookings.manage', 'service_staff.manage'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-emerald-600 dark:text-emerald-400 uppercase mb-1.5">Layanan & Jasa</p>

                        @can('service_queue.view')
                        <a href="{{ route('service-queue.index') }}" title="Antrian Layanan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('service-queue*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="activity" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                            <span class="nav-text truncate">Monitoring Antrian</span>
                        </a>
                        @endcan

                        @can('service_bookings.manage')
                        <a href="{{ route('service-bookings.index') }}" title="Booking & Janji Temu" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('service-bookings*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="calendar-check-2" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                            <span class="nav-text truncate">Booking & Janji Temu</span>
                        </a>
                        @endcan

                        @can('service_staff.manage')
                        <a href="{{ route('service-staff.index') }}" title="Staff & Komisi" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('service-staff*') ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="user-check" class="w-4 h-4 shrink-0 text-emerald-500"></i>
                            <span class="nav-text truncate">Staff & Komisi Jasa</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany
                    @endif

                    <!-- Section: Transaksi & Stok -->
                    @canany(['discounts.view', 'purchases.view', 'purchases.receive', 'purchases.return', 'stocks.view', 'stocks.opname', 'stocks.transfer', 'stocks.adjust', 'sales.view', 'sales.return'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Operasional</p>
                        
                        @can('discounts.view')
                        <a href="{{ route('discounts.index') ?? url('/discounts') }}" title="Diskon & Promo" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('discounts*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="badge-percent" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Diskon & Promo</span>
                        </a>
                        @endcan

                        @can('purchases.view')
                        <a href="{{ route('purchase-orders.index') }}" title="Purchase Order (PO)" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('purchase-orders*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Purchase Order (PO)</span>
                        </a>
                        @endcan

                        @can('purchases.receive')
                        <a href="{{ route('purchase-receipts.index') }}" title="Penerimaan Barang (GRN)" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('purchase-receipts*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="package-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Penerimaan Barang (GRN)</span>
                        </a>
                        @endcan

                        @can('purchases.return')
                        <a href="{{ route('purchase-returns.index') }}" title="Retur Pembelian" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('purchase-returns*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Retur Pembelian</span>
                        </a>
                        @endcan

                        @can('stocks.view')
                        <a href="{{ route('stocks.index') }}" title="Kartu Stok" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('stocks*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="boxes" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Kartu Stok (FIFO)</span>
                        </a>
                        @endcan

                        @can('stocks.opname')
                        <a href="{{ route('stock-opnames.index') }}" title="Stok Opname" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('stock-opnames*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="clipboard-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Stok Opname</span>
                        </a>
                        @endcan

                        @can('stocks.transfer')
                        <a href="{{ route('stock-transfers.index') }}" title="Transfer Stok" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('stock-transfers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="arrow-left-right" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Transfer Antar Gudang</span>
                        </a>
                        @endcan

                        @can('stocks.adjust')
                        <a href="{{ route('stock-adjustments.index') }}" title="Penyesuaian Stok" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('stock-adjustments*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Penyesuaian Stok (Adj)</span>
                        </a>
                        @endcan

                        @can('stocks.view')
                        <a href="{{ route('stocks.alerts') }}" title="Peringatan Stok" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('stock-alerts*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-amber-500"></i>
                            <span class="nav-text truncate">Peringatan Stok</span>
                        </a>
                        @endcan

                        @can('sales.view')
                        <a href="{{ route('sales.index') }}" title="Riwayat Penjualan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('sales*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="receipt" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Riwayat Penjualan</span>
                        </a>
                        @endcan

                        @can('sales.return')
                        <a href="{{ route('sale-returns.index') }}" title="Retur Penjualan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('sale-returns*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Retur Penjualan</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany

                    <!-- Section: Keuangan & Finansial (Phase 5) -->
                    @canany(['finance.accounts', 'finance.payable', 'finance.receivable', 'finance.cashflow', 'finance.transfer'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Keuangan & Kas</p>

                        @can('finance.accounts')
                        <a href="{{ route('accounts.index') }}" title="Akun Kas & Bank" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('accounts*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="wallet" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Akun Kas & Bank</span>
                        </a>

                        <a href="{{ route('ppob-products.index') }}" title="Katalog Produk PPOB & Pulsa" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('ppob-products*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="smartphone" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Katalog Produk PPOB</span>
                        </a>
                        @endcan

                        @can('finance.payable')
                        <a href="{{ route('payables.index') }}" title="Hutang Usaha" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('payables*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="receipt-text" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Hutang Pembelian (AP)</span>
                        </a>
                        @endcan

                        @can('finance.receivable')
                        <a href="{{ route('receivables.index') }}" title="Piutang Usaha" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('receivables*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="coins" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Piutang Penjualan (AR)</span>
                        </a>
                        @endcan

                        @can('finance.cashflow')
                        <a href="{{ route('cash-flows.index') }}" title="Arus Kas" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('cash-flows*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="arrow-down-up" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Arus Kas Masuk & Keluar</span>
                        </a>
                        @endcan

                        @can('finance.transfer')
                        <a href="{{ route('account-transfers.index') }}" title="Transfer Kas/Bank" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('account-transfers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="arrow-left-right" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Transfer Kas & Bank</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany

                    <!-- Section: Laporan & User -->
                    @canany(['reports.sales', 'reports.purchases', 'reports.inventory', 'reports.finance', 'reports.shifts', 'users.view', 'roles.manage', 'settings.manage', 'audit.view'])
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Laporan & Pengaturan</p>

                        @canany(['reports.sales', 'reports.purchases', 'reports.inventory', 'reports.finance', 'reports.shifts'])
                        <a href="{{ route('reports.sales') }}" title="Laporan & Laba Rugi" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('reports*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Laporan & Analitik</span>
                        </a>
                        @endcanany

                        @can('users.view')
                        <a href="{{ route('users.index') }}" title="Manajemen Pengguna" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('users*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="users" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Staf & Pengguna</span>
                        </a>
                        @endcan

                        @can('roles.manage')
                        <a href="{{ route('roles.index') }}" title="Hak Akses & Peran" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('roles*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Hak Akses & Peran</span>
                        </a>
                        @endcan

                        @can('settings.manage')
                        <a href="{{ route('settings.index') }}" title="Pengaturan Toko" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('settings*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="settings" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pengaturan Toko</span>
                        </a>
                        @endcan

                        @can('audit.view')
                        <a href="{{ route('audit-trails.index') }}" title="Log Aktivitas & Audit Trail" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('audit-trails*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Audit Trail (Log)</span>
                        </a>
                        @endcan
                    </div>
                    @endcanany
                </div>
            </div>

            <!-- Profile & Account Footer (Clean & Modern) -->
            <div class="pt-4 border-t border-slate-100">
                <div class="user-profile-box p-3 rounded-2xl bg-slate-50/80 border border-slate-200/70 hover:border-slate-300 transition-all flex items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 1)) }}
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white absolute -bottom-0.5 -right-0.5"></span>
                        </div>
                        <div class="sidebar-profile-text min-w-0">
                            <div class="font-bold text-xs text-slate-800 truncate leading-tight">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-block px-1.5 py-0.2 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-brand-50 text-brand-600 border border-brand-200/60 truncate max-w-[100px]">
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->roles->first()->name ?? 'Admin')) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logout Trigger Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="sidebar-profile-text w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <script>
            (function() {
                try {
                    var sb = document.getElementById('sidebar');
                    var s = sessionStorage.getItem('sidebar_scroll_top');
                    if (sb && s !== null) {
                        sb.scrollTop = parseInt(s, 10);
                    }
                } catch(e) {}
            })();
        </script>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] dark:bg-slate-950 h-full overflow-y-auto transition-colors duration-200">
            
            <!-- TOP NAVBAR -->
            <header class="px-8 lg:px-10 py-4 flex items-center justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900 sticky top-0 z-30 transition-colors duration-200">
                <div class="flex items-center gap-3.5">
                    <!-- Mobile Hamburger Menu Button -->
                    <button id="openMobileSidebarBtn" class="lg:hidden p-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <span class="text-sm font-bold text-slate-800 dark:text-white tracking-tight sm:hidden">{{ $headerTitle ?? 'Dashboard' }}</span>
                </div>

                <div class="flex items-center gap-3.5 ml-auto">
                    <!-- Search Input -->
                    <div class="relative hidden sm:flex items-center">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none"></i>
                        <input type="text" placeholder="Search" class="pl-10 pr-16 py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100/80 focus:bg-white dark:focus:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition w-64">
                        <div class="absolute right-3 flex items-center gap-0.5 text-[10px] font-semibold text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 rounded-md shadow-2xs">
                            ⌘ + F
                        </div>
                    </div>

                    <!-- Dark / Light Theme Toggle Button -->
                    <button id="themeToggleBtn" title="Toggle Tema Gelap / Terang" class="p-2 text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700 rounded-xl transition flex items-center justify-center">
                        <i data-lucide="moon" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                        <i data-lucide="sun" class="w-4 h-4 block dark:hidden text-amber-500"></i>
                    </button>

                    <!-- Action Icons -->
                    <a href="{{ route('reports.sales.export-excel') }}" title="Download Laporan Excel" class="p-2 text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700 rounded-xl transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </a>
                    
                    <a href="{{ route('stocks.alerts') }}" title="Peringatan Stok" class="p-2 text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/70 dark:border-slate-700 rounded-xl transition relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="w-2 h-2 rounded-full bg-brand-500 absolute top-2 right-2 ring-2 ring-white dark:ring-slate-900"></span>
                    </a>

                    <!-- AI Support Button -->
                    <button class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-sm shadow-brand-500/30 transition">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 fill-white"></i>
                        <span class="hidden sm:inline">AI Support</span>
                    </button>
                </div>
            </header>

            <!-- PAGE HEADER: TITLE (LEFT) & BREADCRUMBS (RIGHT) -->
            <div class="px-8 lg:px-10 pt-6 pb-2 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $headerTitle ?? 'Dashboard' }}</h1>
                    @if(isset($headerDescription) && $headerDescription)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $headerDescription }}</p>
                    @endif
                </div>

                <!-- Breadcrumbs on the right -->
                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 px-3.5 py-1.5 rounded-xl shadow-2xs shrink-0 self-start sm:self-auto">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition flex items-center gap-1 text-slate-500 dark:text-slate-400">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Home</span>
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600"></i>
                    @if(isset($breadcrumbParent))
                        <span class="text-slate-500 dark:text-slate-400">{{ $breadcrumbParent }}</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600"></i>
                    @endif
                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $breadcrumbCurrent ?? ($headerTitle ?? 'Dashboard') }}</span>
                </nav>
            </div>

            <!-- VIEW CONTENT -->
            <div class="p-8 lg:px-10 space-y-7 flex-1 bg-[#f8fafc] dark:bg-slate-950 transition-colors duration-200">
                @yield('content')
            </div>

        </main>
    </div>

    <!-- Modals Portal (Rendered at top-level body to ensure full screen overlay) -->
    @stack('modals')

    <!-- Interactive Scripts for Sidebar & Theme Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            const sidebar = document.getElementById('sidebar');
            const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
            const openMobileSidebarBtn = document.getElementById('openMobileSidebarBtn');
            const closeMobileSidebarBtn = document.getElementById('closeMobileSidebarBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const themeToggleBtn = document.getElementById('themeToggleBtn');

            // 1. Dark / Light Mode Toggle with Persistence
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    lucide.createIcons();

                    // Re-render charts for theme adaptation if needed
                    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: isDark ? 'dark' : 'light' } }));
                });
            }

            // 2. Desktop Toggle Sidebar Minimize
            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('sidebar-collapsed');
                    
                    const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
                    const iconContainer = toggleSidebarBtn.querySelector('i');
                    if (iconContainer) {
                        iconContainer.setAttribute('data-lucide', isCollapsed ? 'panel-left-open' : 'panel-left-close');
                        lucide.createIcons();
                    }

                    setTimeout(() => {
                        window.dispatchEvent(new Event('resize'));
                    }, 300);
                });
            }

            // 3. Mobile Open/Close Sidebar
            if (openMobileSidebarBtn) {
                openMobileSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarBackdrop.classList.remove('hidden');
                });
            }

            function closeMobileNav() {
                sidebar.classList.add('-translate-x-full');
                sidebarBackdrop.classList.add('hidden');
            }

            // 4. Sidebar Scroll Position Persistence
            if (sidebar) {
                const savedScroll = sessionStorage.getItem('sidebar_scroll_top');
                if (savedScroll !== null) {
                    sidebar.scrollTop = parseInt(savedScroll, 10);
                } else {
                    const activeItem = sidebar.querySelector('.nav-item.bg-brand-50, .nav-item.bg-amber-50, .nav-item.bg-emerald-50, .nav-item.font-bold');
                    if (activeItem) {
                        activeItem.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                    }
                }

                sidebar.addEventListener('scroll', function () {
                    sessionStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
                }, { passive: true });

                sidebar.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        sessionStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
                    });
                });

                window.addEventListener('beforeunload', function () {
                    sessionStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
                });
            }

            // 5. Horizontal Tab Menu Scroll Persistence (Reports, POS Categories, Floor Plan)
            const tabSubNav = document.getElementById('tab_sub_nav') || document.querySelector('.overflow-x-auto[class*="border-b"]');
            if (tabSubNav) {
                const savedTabScroll = sessionStorage.getItem('tab_sub_nav_scroll_left');
                if (savedTabScroll !== null) {
                    tabSubNav.scrollLeft = parseInt(savedTabScroll, 10);
                } else {
                    const activeTab = tabSubNav.querySelector('.bg-brand-500, .bg-brand-600, .text-brand-600, [class*="bg-brand-500"]');
                    if (activeTab) {
                        activeTab.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'instant' });
                    }
                }

                tabSubNav.addEventListener('scroll', function () {
                    sessionStorage.setItem('tab_sub_nav_scroll_left', tabSubNav.scrollLeft);
                }, { passive: true });

                tabSubNav.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', function () {
                        sessionStorage.setItem('tab_sub_nav_scroll_left', tabSubNav.scrollLeft);
                    });
                });
            }
        });
    </script>

    <!-- Global Toast & SweetAlert Helpers -->
    <style>
        /* Modal dialog backdrop (only for modal popups, never for toasts) */
        .swal2-container.swal2-backdrop-show {
            backdrop-filter: blur(4px) !important;
            -webkit-backdrop-filter: blur(4px) !important;
            background: rgba(15, 23, 42, 0.6) !important;
        }
        /* Completely clear backdrop when showing toast notifications */
        .swal2-toast-shown .swal2-container,
        .swal2-container:has(.swal2-toast),
        .swal2-container.swal2-top-end:not(:has(.swal2-modal)) {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important;
        }
        .swal2-toast {
            pointer-events: auto !important;
        }
        .swal2-popup:not(.swal2-toast) {
            border-radius: 1.75rem !important;
            padding: 2rem 1.75rem !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: #ffffff !important;
            max-width: 24rem !important;
        }
        .dark .swal2-popup {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em !important;
            margin-bottom: 0.5rem !important;
            padding: 0 !important;
        }
        .dark .swal2-title {
            color: #f8fafc !important;
        }
        .swal2-html-container {
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #64748b !important;
            line-height: 1.5 !important;
            margin: 0 0 1.5rem 0 !important;
        }
        .dark .swal2-html-container {
            color: #94a3b8 !important;
        }
        .swal2-icon {
            transform: scale(0.9) !important;
            margin: 0.5rem auto 1.25rem !important;
            border-width: 3px !important;
        }
        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
            background: #fffbeb !important;
        }
        .swal2-icon.swal2-error {
            border-color: #f43f5e !important;
            color: #f43f5e !important;
            background: #fff1f2 !important;
        }
        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
            background: #ecfdf5 !important;
        }
        .swal2-icon.swal2-info {
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
            background: #eff6ff !important;
        }
        .swal2-actions {
            margin-top: 0.5rem !important;
            gap: 0.75rem !important;
            width: 100% !important;
        }
        .swal2-styled {
            border-radius: 0.875rem !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            padding: 0.65rem 1.5rem !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
            box-shadow: none !important;
        }
        .swal2-confirm {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px -2px rgba(249, 115, 22, 0.4) !important;
            flex: 1 !important;
            border: none !important;
        }
        .swal2-confirm:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px -2px rgba(249, 115, 22, 0.5) !important;
        }
        .swal2-cancel {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            flex: 1 !important;
        }
        .swal2-cancel:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .swal2-toast.toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4), 0 8px 10px -6px rgba(16, 185, 129, 0.2) !important;
        }
        .swal2-toast.toast-error {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(244, 63, 94, 0.4), 0 8px 10px -6px rgba(244, 63, 94, 0.2) !important;
        }
        .swal2-toast.toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4), 0 8px 10px -6px rgba(245, 158, 11, 0.2) !important;
        }
        .swal2-toast.toast-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4), 0 8px 10px -6px rgba(59, 130, 246, 0.2) !important;
        }
        .swal2-toast .swal2-title {
            color: #ffffff !important;
            font-size: 0.8125rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
        }
        .swal2-toast .swal2-icon {
            border-color: rgba(255, 255, 255, 0.8) !important;
            color: #ffffff !important;
            margin: 0 !important;
            background: transparent !important;
        }
        .swal2-toast .swal2-close {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .swal2-toast .swal2-timer-progress-bar {
            background: rgba(255, 255, 255, 0.4) !important;
        }
    </style>
    <script>
        // Custom Themed Toast Function
        function showToast(type, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                iconColor: '#ffffff',
                title: message,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                showCloseButton: true,
                customClass: {
                    popup: `rounded-2xl p-3.5 toast-${type}`
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        }

        // Global Confirmation Dialog Helper
        function confirmDelete(formId, title = 'Hapus Data?', text = 'Data yang dihapus tidak dapat dipulihkan kembali!') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                scrollbarPadding: false,
                heightAuto: false,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            });
        }

        // Global Flatpickr Initializer
        function initFlatpickr(context = document) {
            if (typeof flatpickr !== 'undefined') {
                const dateInputs = context.querySelectorAll ? context.querySelectorAll("input[type='date'], .datepicker, .flatpickr") : document.querySelectorAll("input[type='date'], .datepicker, .flatpickr");
                dateInputs.forEach(input => {
                    if (!input._flatpickr) {
                        flatpickr(input, {
                            dateFormat: "Y-m-d",
                            altInput: true,
                            altFormat: "d M Y",
                            allowInput: true,
                            locale: "id",
                            disableMobile: true
                        });
                    }
                });

                const timeInputs = context.querySelectorAll ? context.querySelectorAll("input[type='time'], .timepicker") : document.querySelectorAll("input[type='time'], .timepicker");
                timeInputs.forEach(input => {
                    if (!input._flatpickr) {
                        flatpickr(input, {
                            enableTime: true,
                            noCalendar: true,
                            dateFormat: "H:i",
                            time_24hr: true,
                            disableMobile: true
                        });
                    }
                });
            }
        }

        function initSelect2Filters(container = document) {
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(container).find('.select2-filter').not('.select2-hidden-accessible').each(function() {
                    const $el = $(this);
                    const placeholder = $el.data('placeholder') || $el.find('option[value=""]').text() || $el.find('option:first').text() || 'Pilih...';
                    
                    $el.select2({
                        placeholder: placeholder,
                        allowClear: $el.data('allow-clear') !== false,
                        width: '100%'
                    }).on('change', function() {
                        if (!$el.attr('onchange') && $el.data('auto-submit') !== false) {
                            $el.closest('form').submit();
                        }
                    });
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initFlatpickr();
            initSelect2Filters();
        });

        // Global Modal Helper Functions
        function openModal(modalId) {
            const el = document.getElementById(modalId);
            if (el) {
                el.classList.remove('hidden');
                el.classList.add('flex');
                if (window.lucide) {
                    lucide.createIcons();
                }
                setTimeout(() => {
                    initFlatpickr(el);
                }, 50);
            }
        }

        function closeModal(modalId) {
            const el = document.getElementById(modalId);
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        }

        // Auto trigger toast on flash session messages
        @if(session('success'))
            showToast('success', {!! json_encode(session('success')) !!});
        @endif

        @if(session('error'))
            showToast('error', {!! json_encode(session('error')) !!});
        @endif

        @if(session('warning'))
            showToast('warning', {!! json_encode(session('warning')) !!});
        @endif

        @if(session('info'))
            showToast('info', {!! json_encode(session('info')) !!});
        @endif
    </script>
    @stack('scripts')
</body>
</html>
