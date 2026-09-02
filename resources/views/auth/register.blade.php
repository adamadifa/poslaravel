<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div class="space-y-1.5">
            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Nama Lengkap</label>
            <div class="relative">
                <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Budi Santoso" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Alamat Email</label>
            <div class="relative">
                <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="budi@pospro.com" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Password</label>
            <div class="relative">
                <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Konfirmasi Password</label>
            <div class="relative">
                <i data-lucide="shield-check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 active:scale-[0.99] text-white font-extrabold text-sm shadow-md shadow-brand-500/30 flex items-center justify-center gap-2 transition pt-3">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Daftar Akun Baru</span>
        </button>

        <div class="pt-3 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                Sudah punya akun? Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>
