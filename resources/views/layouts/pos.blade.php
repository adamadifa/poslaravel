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
    </style>
    @stack('styles')
</head>
<body class="h-screen w-screen overflow-hidden text-slate-800 antialiased bg-slate-100 flex flex-col transition-colors duration-200">

    <!-- TOP POS HEADER BAR -->
    <header class="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shrink-0 select-none shadow-2xs z-20 transition-colors duration-200">
        <div class="flex items-center gap-5">
            <!-- Brand & Mode -->
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-500 flex items-center justify-center text-white font-black text-sm shadow-sm shadow-brand-500/30">
                    <i data-lucide="zap" class="w-4.5 h-4.5 fill-white stroke-white"></i>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-lg tracking-tight text-slate-900">Mare<span class="text-brand-500">POS</span></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                    </span>
                </div>
            </div>

            <div class="h-6 w-px bg-slate-200"></div>

            <!-- Store / Branch Selector & Register Info -->
            <div class="flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1.5 text-slate-700 font-semibold bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-brand-500"></i>
                    <span>Cabang Utama (Jakarta)</span>
                </div>
                <div class="text-slate-500 font-medium">
                    Register: <strong class="text-slate-800 font-bold">#POS-01</strong>
                </div>
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
        <div class="flex items-center gap-3">
            <!-- Theme Toggle Button -->
            <button id="posThemeToggleBtn" title="Ganti Mode (Light / Dark)" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 border border-slate-200 transition">
                <i data-lucide="sun" class="w-4 h-4 hidden text-amber-400"></i>
                <i data-lucide="moon" class="w-4 h-4 block text-slate-600"></i>
            </button>

            <!-- Cashier Shift Badge -->
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 text-xs">
                <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-[10px]">
                    N
                </div>
                <span class="text-slate-600 font-medium">Kasir: <strong class="text-slate-900 font-bold">Nanda (Shift 1)</strong></span>
            </div>

            <!-- Back to Dashboard / Back Office -->
            <a href="{{ url('/') }}" title="Back to Admin Dashboard" class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 transition shadow-2xs">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Back Office</span>
            </a>
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
