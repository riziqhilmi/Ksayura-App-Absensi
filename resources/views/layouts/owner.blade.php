<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kantor Sayur') }} - Owner Panel</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center space-x-3">
                            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-blue-500 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span>{{ $header ?? 'Dashboard Owner' }}</span>
                        </h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500 hidden sm:inline">
                                {{ now()->format('l, d F Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-8">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>