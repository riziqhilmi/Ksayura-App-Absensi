<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center space-x-2">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Dashboard</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-blue-50 to-emerald-50 rounded-xl">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-blue-500 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">{{ Auth::user()->name[0] }}</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ Auth::user()->name }}!</h3>
                            <p class="text-sm text-gray-600">Anda login sebagai {{ ucfirst(Auth::user()->role) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>