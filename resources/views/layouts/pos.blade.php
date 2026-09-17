<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kasir / POS' }} - POS Retail Pro</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Theme Initializer -->
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
            user-select: none;
        }
        .font-mono-num {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
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

        /* Premium SweetAlert2 Global Theme Overrides */
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
        /* Custom SweetAlert Icons */
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
        /* Modern Buttons */
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
        /* Toast Notifications */
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
        // Set Default SweetAlert2 Options Globally
        const POSSwal = Swal.mixin({
            confirmButtonText: 'Oke, Mengerti',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            scrollbarPadding: false,
            heightAuto: false
        });

        function showPosAlert(icon, title, text = '', timer = null) {
            return POSSwal.fire({
                icon: icon,
                title: title,
                text: text,
                timer: timer,
                showConfirmButton: timer ? false : true
            });
        }

        function showPosToast(type, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                iconColor: '#ffffff',
                title: message,
                showConfirmButton: false,
                timer: 3000,
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
    </script>
    @stack('styles')
</head>
<body class="h-screen w-screen overflow-hidden text-slate-800 antialiased bg-slate-100 flex flex-col transition-colors duration-200">

    <!-- TOP POS HEADER BAR -->
    <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 select-none shadow-2xs z-20 transition-colors duration-200">
        <div class="flex items-center gap-5">
            <!-- Brand & Mode -->
            <div class="flex items-center gap-2.5">
                @if(!empty($appLogoSetting))
                    <img src="{{ asset('storage/' . $appLogoSetting) }}" alt="{{ $appNameSetting ?? 'Logo' }}" class="w-9 h-9 rounded-xl object-contain bg-white border border-slate-200 p-1 shadow-xs shrink-0">
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-sm shadow-sm shadow-brand-500/30">
                        <i data-lucide="zap" class="w-4.5 h-4.5 fill-white stroke-white"></i>
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <span class="font-bold text-lg tracking-tight text-slate-900">{{ $appNameSetting ?? 'WarungPro' }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                    </span>
                </div>
            </div>

            <div class="h-6 w-px bg-slate-200"></div>

            <!-- Store / Branch Info -->
            <div class="flex items-center gap-1.5 text-slate-700 font-semibold bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                <i data-lucide="store" class="w-3.5 h-3.5 text-brand-500"></i>
                <span id="posWarehouseDisplayName">{{ $defaultWarehouse->name ?? 'Cabang Utama' }}</span>
            </div>
        </div>

        <!-- Middle Quick Keyboard Badges -->
        <div class="hidden xl:flex items-center gap-2 text-xs text-slate-500">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono-num text-slate-700 shadow-2xs font-bold">F1</kbd> Cari
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono-num text-slate-700 shadow-2xs font-bold">F2</kbd> Customer
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono-num text-slate-700 shadow-2xs font-bold">F7</kbd> Hold
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200">
                <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono-num text-slate-700 shadow-2xs font-bold">F9</kbd> Diskon
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-brand-50 border border-brand-200 text-brand-700 font-semibold">
                <kbd class="px-1.5 py-0.5 bg-brand-500 text-white rounded text-[10px] font-mono-num font-bold shadow-2xs">F12</kbd> Bayar
            </span>
        </div>

        <!-- Right User & Exit Controls -->
        <div class="flex items-center gap-2">
            <!-- Kas Keluar / Biaya Operasional Button (F4) -->
            <button 
                type="button" 
                onclick="openShiftExpenseModal()" 
                title="Catat Kas Keluar / Biaya Operasional Kasir (Tekan F4)" 
                class="h-9 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200/90 text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap shadow-2xs cursor-pointer"
            >
                <i data-lucide="wallet-cards" class="w-4 h-4 text-amber-600"></i>
                <span>Kas Keluar</span>
                <kbd class="px-1 py-0.5 bg-amber-200/80 border border-amber-300/80 rounded text-[9px] font-mono-num text-amber-900 font-bold leading-none">F4</kbd>
                <span id="header_shift_expense_badge" class="hidden px-1.5 py-0.5 rounded-md bg-rose-500 text-white text-[10px] font-black font-mono-num shadow-2xs">-Rp 0</span>
            </button>

            <!-- Tutup Shift Button -->
            <button 
                type="button" 
                onclick="openCloseShiftDialog()" 
                title="Tutup Sesi Shift Kasir & Rekap Kas Fisik" 
                class="h-9 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200/90 text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap shadow-2xs cursor-pointer"
            >
                <i data-lucide="lock" class="w-3.5 h-3.5 text-rose-600"></i>
                <span>Tutup Shift</span>
            </button>

            <!-- Cashier Shift Badge -->
            <div class="hidden sm:flex h-9 items-center gap-2 bg-slate-50 px-3 rounded-xl border border-slate-200 text-xs whitespace-nowrap">
                <div class="w-5 h-5 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-[10px]">
                    {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}
                </div>
                <span class="text-slate-600 font-medium">Kasir: <strong class="text-slate-900 font-bold max-w-[140px] truncate inline-block align-bottom" id="posCashierName">{{ auth()->user()->name ?? 'Kasir' }}</strong></span>
            </div>

            <!-- Back to Dashboard / Back Office -->
            <a href="{{ url('/') }}" title="Back to Admin Dashboard" class="h-9 flex items-center gap-1.5 px-3 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 transition shadow-2xs whitespace-nowrap">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-slate-500"></i>
                <span class="hidden md:inline">Back Office</span>
            </a>

            <!-- Theme Toggle Button -->
            <button id="posThemeToggleBtn" title="Ganti Mode (Light / Dark)" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 border border-slate-200 transition shrink-0">
                <i data-lucide="sun" class="w-4 h-4 hidden text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 block text-slate-600"></i>
            </button>
        </div>
    </header>

    <!-- POS MAIN WORKSPACE -->
    <main class="flex-1 flex overflow-hidden">
        @yield('content')
    </main>

    <!-- Initialize Lucide Icons & Theme Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();

            const posThemeToggleBtn = document.getElementById('posThemeToggleBtn');
            if (posThemeToggleBtn) {
                posThemeToggleBtn.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    lucide.createIcons();
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
