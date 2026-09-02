<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Mare POS Pro') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Theme Initializer (Default Light Mode) -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="h-full font-sans bg-[#f1f3f6] dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex flex-col items-center justify-center p-4 sm:p-6 antialiased transition-colors duration-200">

    <!-- Top Theme Toggle -->
    <div class="w-full max-w-md flex justify-end mb-3">
        <button id="guestThemeToggleBtn" title="Ganti Mode (Light / Dark)" class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs transition">
            <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
            <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-slate-600"></i>
        </button>
    </div>

    <!-- Main Form Container Matching Mare Theme -->
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-7 sm:p-9 shadow-sm shadow-slate-200/50 dark:shadow-none space-y-6 transition-colors duration-200">
        
        <!-- Brand Logo & Title -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-500 to-amber-500 text-white font-black text-xl shadow-md shadow-brand-500/30 mb-1">
                <i data-lucide="zap" class="w-6 h-6 fill-white stroke-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Mare<span class="text-brand-500">™</span> POS Pro</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Sistem Kasir & Manajemen Retail Profesional</p>
        </div>

        {{ $slot }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            const toggle = document.getElementById('guestThemeToggleBtn');
            if (toggle) {
                toggle.addEventListener('click', function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    lucide.createIcons();
                });
            }
        });
    </script>
</body>
</html>
