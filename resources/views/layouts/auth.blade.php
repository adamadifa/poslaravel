<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Autentikasi' }} - POS Retail Pro</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS & JS via Vite -->
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
<body class="h-full bg-[#f1f3f6] text-slate-800 flex flex-col items-center justify-center p-4 sm:p-6 antialiased transition-colors duration-200">

    <!-- Top Floating Theme Toggle -->
    <div class="w-full max-w-md flex justify-end mb-3">
        <button id="authThemeToggleBtn" title="Ganti Mode (Light / Dark)" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 bg-white border border-slate-200/80 shadow-2xs transition">
            <i data-lucide="sun" class="w-4 h-4 hidden text-amber-400"></i>
            <i data-lucide="moon" class="w-4 h-4 block text-slate-600"></i>
        </button>
    </div>

    <!-- Auth Container -->
    <div class="w-full max-w-md">
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            const authThemeToggleBtn = document.getElementById('authThemeToggleBtn');
            if (authThemeToggleBtn) {
                authThemeToggleBtn.addEventListener('click', function () {
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
