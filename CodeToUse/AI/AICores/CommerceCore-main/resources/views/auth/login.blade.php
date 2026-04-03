<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false, loading: false }" @submit="loading = true" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1.5">Email address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4.5 h-4.5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L22 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="input-premium w-full pl-11 pr-4 py-3 rounded-xl text-sm font-medium bg-white dark:bg-surface-900 text-surface-900 dark:text-white placeholder-surface-400 focus:outline-none"
                       placeholder="you@company.com">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs font-medium text-rose-500 animate-fade-in-up">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-surface-700 dark:text-surface-300">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 transition-colors">Forgot?</a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4.5 h-4.5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="input-premium w-full pl-11 pr-12 py-3 rounded-xl text-sm font-medium bg-white dark:bg-surface-900 text-surface-900 dark:text-white placeholder-surface-400 focus:outline-none"
                       placeholder="••••••••">
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-surface-400 hover:text-surface-600 dark:hover:text-surface-300 transition-colors">
                    <svg x-show="!showPassword" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-medium text-rose-500 animate-fade-in-up">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded-md border-surface-300 dark:border-surface-600 dark:bg-surface-800 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 transition-colors cursor-pointer">
            <label for="remember_me" class="text-sm font-medium text-surface-600 dark:text-surface-400 cursor-pointer select-none">Remember me</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="btn-glow w-full py-3 px-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold text-sm shadow-lg shadow-primary-500/25 transition-all duration-200 flex items-center justify-center gap-2"
                :class="loading ? 'opacity-75 cursor-not-allowed' : ''"
                :disabled="loading">
            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-text="loading ? 'Signing in...' : 'Sign in'"></span>
        </button>
    </form>

    {{-- Demo Login Section --}}
    <div class="mt-8 pt-8 border-t border-surface-100 dark:border-white/5">
        <div class="text-center mb-4">
            <span class="text-xs font-semibold text-surface-400 dark:text-slate-500 uppercase tracking-widest">Or try with demo access</span>
        </div>
        <button type="button" 
                onclick="document.getElementById('email').value='demo@commercecore.com'; document.getElementById('password').value='password'; document.querySelector('form').dispatchEvent(new Event('submit')); document.querySelector('form').submit();"
                class="w-full flex items-center justify-between p-4 rounded-2xl glass-border glass hover:bg-white/5 group transition-all duration-300">
            <div class="flex items-center gap-4 text-left">
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-500 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white leading-tight">Instant Demo Login</h4>
                    <p class="text-xs text-slate-500">demo@commercecore.com / password</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-600 group-hover:translate-x-1 group-hover:text-violet-500 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</x-guest-layout>
