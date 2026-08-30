<x-guest-layout>
    <div class="min-h-screen bg-slate-50">
        <div class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative hidden overflow-hidden bg-emerald-700 px-12 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.22),_transparent_34%),linear-gradient(135deg,_#047857_0%,_#0f766e_48%,_#1d4ed8_100%)]"></div>
                <div class="absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-black/20 to-transparent"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/25 backdrop-blur">
                        <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                        KSayura App Absensi
                    </div>
                </div>

                <div class="relative z-10 max-w-xl">
                    <div class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-2xl shadow-emerald-950/30">
                        <img src="{{ asset('images/logo.png') }}" alt="Kantor Sayur" class="h-14 w-auto">
                    </div>
                    <h1 class="text-5xl font-bold leading-tight tracking-normal">
                        Selamat datang di Kantor Sayur.
                    </h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-emerald-50">
                        Kelola absensi dengan tampilan yang lebih nyaman, cepat dibaca, dan tetap rapi di semua ukuran layar.
                    </p>
                </div>

                <div class="relative z-10 grid grid-cols-3 gap-4 text-sm">
                    <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/20 backdrop-blur">
                        <p class="text-2xl font-bold">Real-time</p>
                        <p class="mt-1 text-emerald-50">Pantau kehadiran harian</p>
                    </div>
                    <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/20 backdrop-blur">
                        <p class="text-2xl font-bold">Aman</p>
                        <p class="mt-1 text-emerald-50">Akses akun terlindungi</p>
                    </div>
                    <div class="rounded-2xl bg-white/12 p-4 ring-1 ring-white/20 backdrop-blur">
                        <p class="text-2xl font-bold">Mudah</p>
                        <p class="mt-1 text-emerald-50">Cocok untuk mobile</p>
                    </div>
                </div>
            </section>

            <main class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8 lg:px-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center lg:hidden">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">
                            <img src="{{ asset('images/logo.png') }}" alt="Kantor Sayur" class="h-14 w-auto">
                        </div>
                        <p class="text-sm font-semibold uppercase text-emerald-700">KSayura App Absensi</p>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-200/80 sm:p-8">
                        <div class="mb-7">
                            
                        </div>

                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <div>
                                <x-input-label for="email" :value="__('Alamat Email')" class="text-sm font-semibold text-slate-700" />
                                <div class="relative mt-2">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                    <x-text-input id="email" class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-slate-900 shadow-sm transition duration-200 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div x-data="{ showPassword: false }">
                                <x-input-label for="password" :value="__('Kata Sandi')" class="text-sm font-semibold text-slate-700" />
                                <div class="relative mt-2">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <x-text-input id="password" class="block w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-14 text-slate-900 shadow-sm transition duration-200 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi" />
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-2xl text-slate-400 transition hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.16-3.568m3.036-2.187A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.132 5.236M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L4 4m16 16l-4.5-4.5" />
                                        </svg>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                                <label for="remember_me" class="inline-flex items-center">
                                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" name="remember">
                                    <span class="ml-2 text-slate-600">{{ __('Ingat Saya') }}</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a class="font-semibold text-emerald-700 transition hover:text-emerald-900 hover:underline" href="{{ route('password.request') }}">
                                        {{ __('Lupa Kata Sandi?') }}
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                                {{ __('Masuk') }}
                            </button>
                        </form>
                    </div>

                    <p class="mt-6 text-center text-sm text-slate-500">
                        &copy; {{ date('Y') }} Kantor Sayur. All rights reserved.
                    </p>
                </div>
            </main>
        </div>
    </div>
</x-guest-layout>
