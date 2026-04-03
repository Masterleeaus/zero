<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CommerceCore') }} — Sign In</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900|outfit:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200">
    <div class="min-h-screen flex">

        {{-- LEFT: Gradient Illustration Panel --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-violet-800">
            {{-- Abstract Background Shapes --}}
            <div class="absolute inset-0">
                <div class="absolute top-[10%] left-[10%] w-72 h-72 bg-white/5 rounded-full blur-3xl animate-float"></div>
                <div class="absolute bottom-[15%] right-[5%] w-96 h-96 bg-violet-500/10 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s"></div>
                <div class="absolute top-[50%] left-[40%] w-48 h-48 bg-cyan-400/10 rounded-full blur-2xl animate-float" style="animation-delay: 3s"></div>
                {{-- Grid Pattern --}}
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between p-12 w-full">
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center">
                        <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                    </div>
                    <span class="font-display font-bold text-xl text-white">CommerceCore</span>
                </div>

                {{-- Hero Content --}}
                <div class="max-w-md">
                    <h2 class="font-display font-black text-4xl text-white leading-tight mb-4">
                        Your Complete<br>
                        <span class="text-cyan-300">Business OS</span>
                    </h2>
                    <p class="text-white/70 text-base leading-relaxed mb-8">
                        POS, ERP, E-commerce, CRM, and Website Builder — all in one premium platform. Built for businesses that demand excellence.
                    </p>

                    {{-- Feature Pills --}}
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold border border-white/10">🧾 POS System</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold border border-white/10">🏢 ERP Suite</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold border border-white/10">🛒 E-commerce</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold border border-white/10">🧱 Website Builder</span>
                        <span class="px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-white/90 text-xs font-semibold border border-white/10">📊 Analytics</span>
                    </div>
                </div>

                {{-- Bottom --}}
                <p class="text-white/40 text-xs font-medium">&copy; {{ date('Y') }} CommerceCore. Engineered for scale.</p>
            </div>
        </div>

        {{-- RIGHT: Login Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative">
            {{-- Background decoration --}}
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-[30%] -right-[20%] w-[60%] h-[60%] rounded-full bg-primary-500/5 dark:bg-primary-500/10 blur-[100px]"></div>
                <div class="absolute -bottom-[20%] -left-[10%] w-[40%] h-[40%] rounded-full bg-violet-500/5 dark:bg-violet-500/10 blur-[80px]"></div>
            </div>

            {{-- Dark mode toggle --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="absolute top-6 right-6 p-2.5 rounded-xl text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 transition-all">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            <div class="w-full max-w-[420px] relative z-10">
                {{-- Mobile Logo --}}
                <div class="lg:hidden text-center mb-8">
                    <a href="/" class="inline-flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center shadow-lg shadow-primary-500/25">
                            <img src="{{ asset('images/favicon.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                        </div>
                        <span class="font-display font-bold text-2xl gradient-text">CommerceCore</span>
                    </a>
                </div>

                {{-- Header --}}
                <div class="mb-8">
                    <h1 class="font-display font-bold text-2xl text-surface-900 dark:text-white mb-2">Welcome back</h1>
                    <p class="text-surface-500 text-sm">Sign in to your business dashboard</p>
                </div>

                {{ $slot }}

                {{-- Register Prompt --}}
                <p class="mt-8 text-center text-sm text-surface-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">Create one</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
