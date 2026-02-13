<nav class="bg-white border-b border-gray-100 dark:border-gray-700 h-16">
    <div class="px-4 sm:px-6 lg:px-8 h-full">
        <div class="flex justify-between items-center h-full">
            
            {{-- BAGIAN KIRI: Toggle Mobile & Menu Desktop --}}
            <div class="flex items-center h-full">
                
                {{-- 1. Tombol Hamburger (Hanya muncul di Mobile / md:hidden) --}}
                <button @click="sidebarOpen = !sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out md:hidden">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- 2. Menu Links (Hanya muncul di Desktop / hidden md:flex) --}}
                {{-- Link yang Anda kirim saya letakkan di sini --}}
                <div class="hidden md:flex space-x-8 sm:ml-10 h-full">
                    
                    <x-nav-link :href="route('owner.dashboard')" :active="request()->routeIs('owner.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(auth()->user()->role === 'owner')
                        <x-nav-link :href="route('owner.laporan')" :active="request()->routeIs('owner.laporan')">
                            {{ __('Laporan Pendapatan') }}
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('owner.kebutuhan')" :active="request()->routeIs('owner.kebutuhan')">
                        {{ __('Belanja Kebutuhan') }}
                    </x-nav-link>

                </div>
            </div>

            {{-- BAGIAN KANAN: Dropdown Profil User --}}
            <div class="flex items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            
        </div>
    </div>
</nav>   