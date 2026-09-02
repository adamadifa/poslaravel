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

    <!-- Theme Initializer (Prevent Flash) -->
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
<body class="h-screen w-screen overflow-hidden text-slate-800 antialiased bg-white p-0 m-0 transition-colors duration-200">

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden lg:hidden transition-opacity"></div>

    <!-- Edge-to-Edge True Full Screen Flex Layout -->
    <div class="h-full w-full bg-white flex overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 lg:static w-64 bg-white border-r border-slate-100 flex flex-col justify-between shrink-0 p-6 select-none -translate-x-full lg:translate-x-0 h-full overflow-y-auto overflow-x-hidden transition-colors duration-200">
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
                            <span class="nav-text truncate">Satuan Satuan</span>
                        </a>

                        <a href="{{ url('/customers') }}" title="Pelanggan & Member" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="user-check" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pelanggan & Member</span>
                        </a>

                        <a href="{{ url('/suppliers') }}" title="Pemasok / Supplier" class="nav-item flex items-center gap-3.5 px-3.5 py-2 rounded-xl font-medium text-xs transition text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0 text-slate-400"></i>
                            <span class="nav-text truncate">Pemasok (Supplier)</span>
                        </a>
                    </div>

                    <!-- Section: Transaksi & Stok -->
                    <div class="pt-3">
                        <p class="section-header px-3 text-[10px] font-extrabold tracking-wider text-slate-400 dark:text-slate-500 uppercase mb-1.5">Operasional</p>
                        
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

            <!-- Profile & Progress Footer -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800">
                <div class="user-profile-box flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition mb-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-bold text-xs flex items-center justify-center shadow-2xs shrink-0 ring-2 ring-slate-100 dark:ring-slate-800">
                            {{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 1)) }}
                        </div>
                        <div class="sidebar-profile-text min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-xs text-slate-800 dark:text-slate-100 truncate">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    {{ ucfirst(str_replace('_', ' ', auth()->user()->roles->first()->name ?? 'Admin')) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">{{ auth()->user()->email ?? 'admin@pospro.com' }}</p>
                        </div>
                    </div>
                    
                    <!-- Logout Form Trigger -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="sidebar-profile-text p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>

                <!-- Progress Bar -->
                <div class="profile-progress px-1">
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full" style="width: 64%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1.5 text-[11px]">
                        <span class="text-slate-500 font-medium">Complete your profile</span>
                        <span class="text-slate-700 font-bold">64%</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 flex flex-col min-w-0 bg-white h-full overflow-y-auto transition-colors duration-200">
            
            <!-- TOP NAVBAR -->
            <header class="px-8 lg:px-10 py-5 flex items-center justify-between gap-4 border-b border-slate-100 shrink-0 bg-white sticky top-0 z-30 transition-colors duration-200">
                <div class="flex items-center gap-3.5">
                    <!-- Mobile Hamburger Menu Button -->
                    <button id="openMobileSidebarBtn" class="lg:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 border border-slate-200">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-none">{{ $headerTitle ?? 'Dashboard' }}</h1>
                </div>

                <div class="flex items-center gap-3.5 ml-auto">
                    <!-- Search Input -->
                    <div class="relative hidden sm:flex items-center">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 pointer-events-none"></i>
                        <input type="text" placeholder="Search" class="pl-10 pr-16 py-2.5 bg-slate-50 hover:bg-slate-100/80 focus:bg-white border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition w-64">
                        <div class="absolute right-3 flex items-center gap-0.5 text-[10px] font-semibold text-slate-400 bg-white border border-slate-200 px-1.5 py-0.5 rounded-md shadow-2xs">
                            ⌘ + F
                        </div>
                    </div>

                    <!-- Dark Mode Toggle Button -->
                    <button id="themeToggleBtn" title="Ganti Mode (Light / Dark)" class="p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200/70 rounded-xl transition">
                        <i data-lucide="sun" class="w-4 h-4 hidden text-amber-400"></i>
                        <i data-lucide="moon" class="w-4 h-4 block text-slate-600"></i>
                    </button>

                    <!-- Action Icons -->
                    <button class="p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200/70 rounded-xl transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </button>
                    
                    <button class="p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-50 border border-slate-200/70 rounded-xl transition relative">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="w-2 h-2 rounded-full bg-brand-500 absolute top-2 right-2 ring-2 ring-white"></span>
                    </button>

                    <!-- AI Support Button -->
                    <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 text-white text-xs font-bold shadow-sm shadow-brand-500/30 transition">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 fill-white"></i>
                        <span class="hidden sm:inline">AI Support</span>
                    </button>
                </div>
            </header>

            <!-- SUB-HEADER / ACTIONS BAR -->
            <div class="px-8 lg:px-10 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 shrink-0 bg-white transition-colors duration-200">
                <div class="flex items-center gap-2.5">
                    <button class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Create Automation</span>
                    </button>
                    <button class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Customize</span>
                    </button>
                </div>

                <button class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Notes</span>
                </button>
            </div>

            <!-- VIEW CONTENT -->
            <div class="p-8 lg:p-10 space-y-7 flex-1 bg-white transition-colors duration-200">
                @yield('content')
            </div>

        </main>
    </div>

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
            if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileNav);
        });
    </script>
    @stack('scripts')
</body>
</html>
