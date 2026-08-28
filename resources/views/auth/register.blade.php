<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-emerald-50 via-white to-blue-50">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-2xl border border-gray-100">
            <!-- Logo dan Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-blue-500 shadow-lg mb-4">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Akun</h1>
                <p class="text-sm text-gray-500 mt-1">Bergabung dengan Kantor Sayur</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Nama Lengkap')" class="text-sm font-medium text-gray-700" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <x-text-input id="name" class="block w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition duration-200" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Alamat Email')" class="text-sm font-medium text-gray-700" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <x-text-input id="email" class="block w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition duration-200" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Kata Sandi')" class="text-sm font-medium text-gray-700" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <x-text-input id="password" class="block w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition duration-200" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="text-sm font-medium text-gray-700" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <x-text-input id="password_confirmation" class="block w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition duration-200" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 to-blue-600 hover:from-emerald-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-200 transform hover:scale-[1.02]">
                        {{ __('Daftar') }}
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-800 transition duration-200">
                            Masuk Sekarang
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Kantor Sayur. All rights reserved.</p>
        </div>
    </div>
</x-guest-layout>