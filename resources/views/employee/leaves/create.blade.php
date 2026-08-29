<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <svg class="w-6 h-6 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajukan Cuti
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Info Card -->
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl shadow-lg p-6 mb-6 text-white">
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Perhatian!</h3>
                        <p class="text-yellow-100 text-sm">Pastikan Anda mengisi data dengan benar. Pengajuan cuti akan diproses oleh Owner.</p>
                        <p class="text-yellow-100 text-sm mt-1">⚠️ Anda hanya dapat mengajukan cuti untuk tanggal yang akan datang.</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form method="POST" action="{{ route('employee.leaves.store') }}" class="space-y-6" id="leaveForm">
                    @csrf

                    <!-- Jenis Cuti -->
                    <div>
                        <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Cuti <span class="text-red-500">*</span>
                        </label>
                        <select id="leave_type" name="leave_type" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition duration-200 @error('leave_type') border-red-500 @enderror">
                            <option value="">Pilih Jenis Cuti</option>
                            <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Cuti Sakit</option>
                            <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Cuti Pribadi</option>
                            <option value="maternity" {{ old('leave_type') == 'maternity' ? 'selected' : '' }}>Cuti Melahirkan</option>
                            <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('leave_type')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date" name="start_date" 
                                value="{{ old('start_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                required
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition duration-200 @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="end_date" name="end_date" 
                                value="{{ old('end_date', date('Y-m-d', strtotime('+2 day'))) }}"
                                required
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition duration-200 @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Info Durasi -->
                    <div id="duration-info" class="p-3 bg-blue-50 rounded-xl hidden">
                        <p class="text-sm text-blue-700">
                            <span class="font-semibold">Durasi Cuti:</span> 
                            <span id="total-days" class="font-bold">0</span> hari
                        </p>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Cuti <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="4" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition duration-200 @error('reason') border-red-500 @enderror"
                            placeholder="Jelaskan alasan Anda mengajukan cuti...">{{ old('reason') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Maksimal 500 karakter</p>
                        @error('reason')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('employee.leaves.my') }}"
                            class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-medium rounded-xl transition duration-200">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-medium rounded-xl hover:from-yellow-600 hover:to-orange-600 transition duration-200 shadow-md hover:shadow-lg">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Ajukan Cuti
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto calculate duration
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const durationInfo = document.getElementById('duration-info');
            const totalDays = document.getElementById('total-days');

            function calculateDuration() {
                if (startDate.value && endDate.value) {
                    const start = new Date(startDate.value);
                    const end = new Date(endDate.value);
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    
                    if (diffDays > 0) {
                        durationInfo.classList.remove('hidden');
                        totalDays.textContent = diffDays;
                    } else {
                        durationInfo.classList.add('hidden');
                    }
                }
            }

            startDate.addEventListener('change', calculateDuration);
            endDate.addEventListener('change', calculateDuration);
            
            // Set min date for end date based on start date
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
                calculateDuration();
            });

            // Initial calculation
            calculateDuration();
        });
    </script>
    @endpush
</x-app-layout>
