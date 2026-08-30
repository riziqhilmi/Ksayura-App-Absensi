<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <div class="flex items-center space-x-2">
                            <img src="{{ asset('images/logo.png') }}" alt="Kantor Sayur" class="h-10 w-10 rounded-xl object-contain shadow-sm">
                            <span class="font-bold text-xl text-gray-800">Kantor Sayur</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    @auth
                        @if(Auth::user()->role === 'owner')
                            <!-- Owner Navigation -->
                            <x-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.employees.index')" :active="request()->routeIs('owner.employees.*')">
                                {{ __('Karyawan') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.employee-shifts.index')" :active="request()->routeIs('owner.employee-shifts.*')">
                                {{ __('Penugasan Shift') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.employee-holidays.calendar')" :active="request()->routeIs('owner.employee-holidays.*')">
                                {{ __('Hari Libur') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.attendance.index')" :active="request()->routeIs('owner.attendance.*')">
                                {{ __('Monitoring Absensi') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.shifts.index')" :active="request()->routeIs('owner.shifts.*')">
                                {{ __('Master Shift') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('owner.salaries.index')" :active="request()->routeIs('owner.salaries.*')">
                                {{ __('Gaji') }}
                            </x-nav-link>
                            
                            <!-- Cuti dengan Badge Notifikasi -->
                            @php
                                $pendingLeaves = App\Models\LeaveRequest::where('status', 'pending')->count();
                            @endphp
                            <x-nav-link :href="route('owner.leaves.index')" :active="request()->routeIs('owner.leaves.*')">
                                <span class="flex items-center">
                                    {{ __('Pengajuan Cuti') }}
                                    @if($pendingLeaves > 0)
                                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full animate-pulse">
                                            {{ $pendingLeaves }}
                                        </span>
                                    @endif
                                </span>
                            </x-nav-link>

                        @else
                            <!-- Employee Navigation -->
                            <x-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('employee.calendar.index')" :active="request()->routeIs('employee.calendar.*')">
                                {{ __('Kalender Kerja') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('employee.attendance.my')" :active="request()->routeIs('employee.attendance.*')">
                                {{ __('Absensi Saya') }}
                            </x-nav-link>
                            
                            <x-nav-link :href="route('employee.salaries.my')" :active="request()->routeIs('employee.salaries.*')">
                                {{ __('Gaji Saya') }}
                            </x-nav-link>
                            
                            <!-- Cuti Employee dengan Badge -->
                            @php
                                $employee = App\Models\Employee::where('user_id', Auth::id())->first();
                                $pendingEmployeeLeaves = $employee ? App\Models\LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count() : 0;
                            @endphp
                            <x-nav-link :href="route('employee.leaves.my')" :active="request()->routeIs('employee.leaves.*')">
                                <span class="flex items-center">
                                    {{ __('Pengajuan Cuti') }}
                                    @if($pendingEmployeeLeaves > 0)
                                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-yellow-500 rounded-full animate-pulse">
                                            {{ $pendingEmployeeLeaves }}
                                        </span>
                                    @endif
                                </span>
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                    <div @click="open = !open">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-xl text-gray-500 bg-white hover:text-gray-700 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition duration-150 ease-in-out">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center text-white text-sm font-bold mr-2">
                                {{ Auth::user()->name[0] }}
                            </div>
                            {{ Auth::user()->name }}
                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;" @click.stop>
                        <!-- Profil -->
                        <a href="{{ route('profile.edit') }}" @click="open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Profil') }}
                            </span>
                        </a>

                        <!-- Pengaturan (Owner Only) -->
                        @if(Auth::user()->role === 'owner')
                            <a href="{{ route('owner.settings.index') }}" @click="open = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Pengaturan') }}
                                </span>
                            </a>
                        @endif

                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-1"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>{{ __('Keluar') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if(Auth::user()->role === 'owner')
                    <!-- Owner Mobile Navigation -->
                    <x-responsive-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.employees.index')" :active="request()->routeIs('owner.employees.*')">
                        {{ __('Karyawan') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.employee-shifts.index')" :active="request()->routeIs('owner.employee-shifts.*')">
                        {{ __('Penugasan Shift') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.employee-holidays.calendar')" :active="request()->routeIs('owner.employee-holidays.*')">
                        {{ __('Hari Libur') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.attendance.index')" :active="request()->routeIs('owner.attendance.*')">
                        {{ __('Monitoring Absensi') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.shifts.index')" :active="request()->routeIs('owner.shifts.*')">
                        {{ __('Master Shift') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.salaries.index')" :active="request()->routeIs('owner.salaries.*')">
                        {{ __('Gaji') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('owner.leaves.index')" :active="request()->routeIs('owner.leaves.*')">
                        <span class="flex items-center">
                            {{ __('Pengajuan Cuti') }}
                            @php
                                $pendingLeaves = App\Models\LeaveRequest::where('status', 'pending')->count();
                            @endphp
                            @if($pendingLeaves > 0)
                                <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full animate-pulse">
                                    {{ $pendingLeaves }}
                                </span>
                            @endif
                        </span>
                    </x-responsive-nav-link>

                @else
                    <!-- Employee Mobile Navigation -->
                    <x-responsive-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('employee.calendar.index')" :active="request()->routeIs('employee.calendar.*')">
                        {{ __('Kalender Kerja') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('employee.attendance.my')" :active="request()->routeIs('employee.attendance.*')">
                        {{ __('Absensi Saya') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('employee.salaries.my')" :active="request()->routeIs('employee.salaries.*')">
                        {{ __('Gaji Saya') }}
                    </x-responsive-nav-link>
                    
                    <x-responsive-nav-link :href="route('employee.leaves.my')" :active="request()->routeIs('employee.leaves.*')">
                        <span class="flex items-center">
                            {{ __('Pengajuan Cuti') }}
                            @php
                                $employee = App\Models\Employee::where('user_id', Auth::id())->first();
                                $pendingEmployeeLeaves = $employee ? App\Models\LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count() : 0;
                            @endphp
                            @if($pendingEmployeeLeaves > 0)
                                <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-yellow-500 rounded-full animate-pulse">
                                    {{ $pendingEmployeeLeaves }}
                                </span>
                            @endif
                        </span>
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Profil -->
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('Profil') }}
                    </span>
                </a>

                <!-- Pengaturan (Owner Only) -->
                @if(Auth::user()->role === 'owner')
                    <a href="{{ route('owner.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('Pengaturan') }}
                        </span>
                    </a>
                @endif

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition duration-150 ease-in-out flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ __('Keluar') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
