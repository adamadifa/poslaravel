<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Email Kasir / Admin</label>
            <div class="relative">
                <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="email" type="email" name="email" value="{{ old('email', 'admin@pospro.com') }}" required autofocus autocomplete="username" placeholder="nama@email.com" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>

            <div class="relative">
                <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input id="password" type="password" name="password" required value="password" autocomplete="current-password" placeholder="••••••••" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 hover:bg-slate-100/80 dark:hover:bg-slate-900 focus:bg-white dark:focus:bg-slate-950 border border-slate-200 dark:border-slate-800 focus:border-brand-500 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-600 dark:text-slate-400">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-500 border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950">
                <span>Ingat saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-brand-500 to-amber-500 hover:from-brand-600 hover:to-amber-600 active:scale-[0.99] text-white font-extrabold text-sm shadow-md shadow-brand-500/30 flex items-center justify-center gap-2 transition pt-3">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            <span>Masuk ke Akun</span>
        </button>
    </form>

    <!-- Demo Credentials Footer -->
    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
        <p class="font-medium">Akun Demo Default:</p>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-[11px] font-mono">
            <span>admin@pospro.com</span> • <span>password</span>
        </div>
    </div>
</x-guest-layout>
