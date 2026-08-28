<x-layouts.owner>
    <x-slot name="header">
        Edit Data Karyawan
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <form method="POST" action="{{ route('owner.employees.update', $employee) }}">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $employee->user->name) }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            @error('name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $employee->user->email) }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $employee->user->phone) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea id="address" name="address" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">{{ old('address', $employee->user->address) }}</textarea>
                    </div>
                    <!-- Di bagian Informasi Akun Login, tambahkan field password -->
<div>
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Informasi Akun Login
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $employee->user->name) }}" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
            @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $employee->user->email) }}" required
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
            <input type="password" id="password" name="password"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
        </div>
    </div>
</div>
                </div>

                <!-- Work Information -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Informasi Pekerjaan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Posisi</label>
                            <input type="text" id="position" name="position" value="{{ old('position', $employee->position) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                        </div>
                        <div>
                            <label for="salary_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Gaji *</label>
                            <select id="salary_type" name="salary_type" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                                <option value="daily" {{ old('salary_type', $employee->salary_type) == 'daily' ? 'selected' : '' }}>Harian</option>
                                <option value="weekly" {{ old('salary_type', $employee->salary_type) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                <option value="monthly" {{ old('salary_type', $employee->salary_type) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            </select>
                            @error('salary_type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="base_salary" class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="base_salary" name="base_salary" value="{{ old('base_salary', $employee->base_salary) }}" required
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            </div>
                            @error('base_salary')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                            <select id="status" name="status" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>Resign</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="daily_rate" class="block text-sm font-medium text-gray-700 mb-1">Tarif Harian</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="daily_rate" name="daily_rate" value="{{ old('daily_rate', $employee->daily_rate) }}"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            </div>
                        </div>
                        <div>
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">Tarif Per Jam</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                                <input type="number" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $employee->hourly_rate) }}"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('owner.employees.index') }}"
                        class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-emerald-500 text-white font-medium rounded-xl hover:from-blue-700 hover:to-emerald-600 transition duration-200 shadow-md hover:shadow-lg">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Update Data
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.owner>
