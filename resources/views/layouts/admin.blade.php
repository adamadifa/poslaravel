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

    <!-- Force Light Mode on Admin -->
    <script>
        document.documentElement.classList.remove('dark');
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
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
<body class="h-screen w-screen overflow-hidden text-slate-800 antialiased bg-[#f8fafc] p-0 m-0 transition-colors duration-200">

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Edge-to-Edge True Full Screen Flex Layout -->
    <div class="h-full w-full bg-[#f8fafc] flex overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 lg:static w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 p-6 select-none -translate-x-full lg:translate-x-0 h-full overflow-y-auto overflow-x-hidden transition-colors duration-200">
            <div>
                <!-- Brand Header -->
                <div class="brand-header flex items-center justify-between px-1 h-12 mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-lg shadow-sm shadow-brand-500/30 shrink-0">
                            <i data-lucide="zap" class="w-5 h-5 fill-white stroke-white"></i>
                        </div>
                        <div class="brand-text flex items-center gap-1">
                            <span class="font-bold text-xl tracking-tight text-slate-900">Mare<span class="text-brand-500">™</span></span>
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
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="nav-item flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-semibold text-xs transition {{ request()->routeIs('dashboard') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-200/50 dark:border-brand-500/20 shadow-2xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="layout-grid" class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-500 dark:text-brand-400' : 'text-slate-400' }}"></i>
                        <span class="nav-text truncate font-bold">Dashboard</span>
                    </a>
                    
                    <a href="{{ route('pos.index') }}" title="Kasir / POS" class="nav-item flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <i data-lucide="shopping-cart" class="w-4 h-4 shrink-0 text-slate-400"></i>
                        <span class="nav-text truncate">Kasir POS (F12)</span>
                    </a>

                    <!-- Section: Master Data -->
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Master Data</p>
                        
                        <a href="{{ route('products.index') ?? url('/products') }}" title="Produk & Barcode" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('products*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="package" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Master Produk</span>
                        </a>

                        <a href="{{ route('categories.index') ?? url('/categories') }}" title="Kategori" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('categories*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="folder-tree" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Kategori</span>
                        </a>

                        <a href="{{ route('units.index') ?? url('/units') }}" title="Satuan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('units*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="scale" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Satuan</span>
                        </a>

                        <a href="{{ url('/customers') }}" title="Pelanggan & Member" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('customers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="user-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pelanggan & Member</span>
                        </a>

                        <a href="{{ route('suppliers.index') ?? url('/suppliers') }}" title="Pemasok / Supplier" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('suppliers*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pemasok (Supplier)</span>
                        </a>

                        <a href="{{ route('warehouses.index') ?? url('/warehouses') }}" title="Gudang & Cabang" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('warehouses*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="warehouse" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Gudang & Cabang</span>
                        </a>
                    </div>

                    <!-- Section: Transaksi & Stok -->
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Operasional</p>
                        
                        <a href="{{ route('discounts.index') ?? url('/discounts') }}" title="Diskon & Promo" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->is('discounts*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="badge-percent" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Diskon & Promo</span>
                        </a>

                        <a href="{{ url('/purchases') }}" title="Pembelian / PO" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="shopping-bag" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pembelian & PO</span>
                        </a>

                        <a href="{{ url('/stocks') }}" title="Stok & Opname" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="boxes" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Stok & Opname (FIFO)</span>
                        </a>

                        <a href="{{ url('/sales') }}" title="Riwayat Penjualan" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="receipt" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Riwayat Penjualan</span>
                        </a>
                    </div>

                    <!-- Section: Laporan & User -->
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Laporan & Pengaturan</p>

                        <a href="{{ url('/reports') }}" title="Laporan & Laba Rugi" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Laporan & Analitik</span>
                        </a>

                        <a href="{{ route('users.index') }}" title="Manajemen Pengguna" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition {{ request()->routeIs('users*') ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                            <i data-lucide="users" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Staf & Pengguna</span>
                        </a>

                        <a href="{{ url('/settings') }}" title="Pengaturan Toko" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="settings" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pengaturan Toko</span>
                        </a>
                    </div>
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

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 bg-[#f8fafc] h-full overflow-y-auto transition-colors duration-200">
            
            <!-- TOP NAVBAR -->
            <header class="px-8 lg:px-10 py-4 flex items-center justify-between gap-4 border-b border-slate-200/80 shrink-0 bg-white sticky top-0 z-30 transition-colors duration-200">
                <div class="flex items-center gap-3.5">
                    <!-- Mobile Hamburger Menu Button -->
                    <button id="openMobileSidebarBtn" class="lg:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 border border-slate-200">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <span class="text-sm font-bold text-slate-800 tracking-tight sm:hidden">{{ $headerTitle ?? 'Dashboard' }}</span>
                </div>

                <div class="flex items-center gap-3.5 ml-auto">
                    <!-- Search Input -->
                    <div class="relative hidden sm:flex items-center">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none"></i>
                        <input type="text" placeholder="Search" class="pl-10 pr-16 py-2 bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition w-64">
                        <div class="absolute right-3 flex items-center gap-0.5 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 px-1.5 py-0.5 rounded-md shadow-2xs">
                            ⌘ + F
                        </div>
                    </div>

                    <!-- Action Icons -->
                    <button class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200/70 rounded-xl transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </button>
                    
                    <button class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200/70 rounded-xl transition relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="w-2 h-2 rounded-full bg-brand-500 absolute top-2 right-2 ring-2 ring-white"></span>
                    </button>

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
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $headerTitle ?? 'Dashboard' }}</h1>
                    @if(isset($headerDescription) && $headerDescription)
                        <p class="text-xs text-slate-500 mt-1">{{ $headerDescription }}</p>
                    @endif
                </div>

                <!-- Breadcrumbs on the right -->
                <nav class="flex items-center gap-1.5 text-xs font-medium text-slate-400 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-xl shadow-2xs shrink-0 self-start sm:self-auto">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition flex items-center gap-1 text-slate-500">
                        <i data-lucide="home" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Home</span>
                    </a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                    @if(isset($breadcrumbParent))
                        <span class="text-slate-500">{{ $breadcrumbParent }}</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
                    @endif
                    <span class="text-slate-800 font-bold">{{ $breadcrumbCurrent ?? ($headerTitle ?? 'Dashboard') }}</span>
                </nav>
            </div>

            <!-- VIEW CONTENT -->
            <div class="p-8 lg:px-10 space-y-7 flex-1 bg-[#f8fafc] transition-colors duration-200">
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

            if (closeMobileSidebarBtn) closeMobileSidebarBtn.addEventListener('click', closeMobileNav);
        });
    </script>

    <!-- Global Toast & SweetAlert Helpers -->
    <style>
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
        }
        .swal2-toast .swal2-close {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .swal2-toast .swal2-close:hover {
            color: #ffffff !important;
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
                confirmButtonColor: '#f97316', // Mare Brand Orange
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="lucide lucide-trash-2 mr-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3xl border border-slate-200 p-6 shadow-2xl',
                    title: 'text-lg font-bold text-slate-900',
                    htmlContainer: 'text-xs text-slate-500 font-medium',
                    confirmButton: 'px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md shadow-brand-500/25',
                    cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            });
        }

        // Auto trigger toast on flash session messages
        @if(session('success'))
            showToast('success', "{{ session('success') }}");
        @endif

        @if(session('error'))
            showToast('error', "{{ session('error') }}");
        @endif

        @if(session('warning'))
            showToast('warning', "{{ session('warning') }}");
        @endif

        @if(session('info'))
            showToast('info', "{{ session('info') }}");
        @endif
    </script>
    @stack('scripts')
</body>
</html>
