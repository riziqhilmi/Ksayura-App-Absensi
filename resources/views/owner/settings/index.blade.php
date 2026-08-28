<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Pengaturan Sistem
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Office Location Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Lokasi Kantor
                </h3>
                <p class="text-sm text-gray-500 mb-6">Atur lokasi kantor untuk validasi absensi karyawan</p>

                <form method="POST" action="{{ route('owner.settings.update-office') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                            <input type="text" id="latitude" name="latitude" 
                                   value="{{ old('latitude', $office['latitude']) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            <p class="text-xs text-gray-500 mt-1">Contoh: -6.200000</p>
                            @error('latitude')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                            <input type="text" id="longitude" name="longitude" 
                                   value="{{ old('longitude', $office['longitude']) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            <p class="text-xs text-gray-500 mt-1">Contoh: 106.816666</p>
                            @error('longitude')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="radius" class="block text-sm font-medium text-gray-700 mb-1">Radius Absensi (meter) *</label>
                        <input type="number" id="radius" name="radius" 
                               value="{{ old('radius', $office['radius']) }}" required
                               min="10" max="5000"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        <p class="text-xs text-gray-500 mt-1">Jarak maksimum karyawan dari lokasi kantor (10 - 5000 meter)</p>
                        @error('radius')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat Kantor</label>
                        <textarea id="address" name="address" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">{{ old('address', $office['address']) }}</textarea>
                        @error('address')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Simpan Pengaturan
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Settings Display -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Lokasi Saat Ini
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Latitude</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $office['latitude'] }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Longitude</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $office['longitude'] }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Radius Absensi</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $office['radius'] }} meter</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-500">Alamat</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $office['address'] }}</p>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-xl">
                    <p class="text-sm text-blue-700">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Karyawan hanya dapat melakukan absensi jika berada dalam radius {{ $office['radius'] }} meter dari lokasi kantor.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>