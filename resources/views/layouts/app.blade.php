<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Louwes App') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            html, body { height: 100%; overflow: hidden; margin: 0; }
        </style>
    </head>
    <body class="font-sans antialiased h-full">
        
        <div x-data="{ sidebarOpen: false }" class="flex h-full bg-white relative">
            
            {{-- SIDEBAR --}}
            <aside x-cloak
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                   class="fixed inset-y-0 left-0 z-50 w-64 bg-[#003d4d] text-white flex flex-col p-6 rounded-r-[30px] transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto shadow-2xl -translate-x-full h-full">
                
                {{-- Tombol Close (Mobile) --}}
                <button @click="sidebarOpen = false" class="md:hidden absolute top-4 right-4 text-white hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                {{-- PROFILE INFO (STATIS SESUAI PERMINTAAN) --}}
                <div class="flex flex-col items-center mb-10 shrink-0">
                    <img src="{{ asset('assets/icons/pickup.png') }}" onerror="this.src='https://ui-avatars.com/api/?name=LC&background=random'" class="w-20 h-20 rounded-full border-2 border-gray-400 mb-2 object-cover">
                    
                    <h2 class="font-bold text-lg">Louwes Care</h2>
                    <p class="text-xs text-gray-300 text-center mt-2">JL. Ringroad Timur No 9, Plumbon , Banguntapan , Bantul , DIY 55196</p>
                    <div class="text-xs text-gray-300 mt-1 text-center">
                        <p>📧 Admin@gmail.com</p>
                        <p>📞 081390154885</p>
                    </div>
                </div>

                {{-- MENU LINKS --}}
                <nav class="space-y-2 flex-1 overflow-y-auto">
                    
                    {{-- LOGIKA PEMISAHAN MENU --}}
                    @if(auth()->user()->role === 'owner')
                        
                        {{-- MENU KHUSUS OWNER --}}
                        {{-- Style disamakan persis dengan menu admin di bawah --}}
                        <a href="{{ route('owner.dashboard') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.dashboard') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Dashboard Owner
                        </a>

 <a href="{{ route('owner.kebutuhan') }}" 
           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.kebutuhan') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
            Kebutuhan
        </a>

        <a href="{{ route('owner.karyawan.index') }}" 
           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.karyawan.index') ? 'bg-white/20 font-semibold text-white' : 'hover:bg-white/10' }}">
            Manajemen Karyawan
        </a>

                    @else
                        
                        {{-- MENU KHUSUS ADMIN (KASIR) --}}
                        <a href="{{ route('order.search') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('order.search') || request()->routeIs('order.check') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Input Order
                        </a>

                        <a href="{{ route('pesanan.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('pesanan.index') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Manajemen Pesanan
                        </a>
                        
                        <a href="{{ route('kebutuhan.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('kebutuhan.index') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Kebutuhan
                        </a>

                    @endif
                    
                </nav>

                {{-- LOGOUT --}}
                <div class="mt-auto pt-10 border-t border-white/10 shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            {{-- KONTEN UTAMA --}}
            <div class="flex-1 flex flex-col h-full overflow-hidden bg-white w-full">
                
                {{-- Header Mobile --}}
                <div class="p-4 md:hidden flex justify-between items-center bg-white border-b shrink-0 z-40 relative shadow-sm">
                    <button @click="sidebarOpen = true" class="text-[#003d4d] focus:outline-none hover:bg-gray-100 p-2 rounded-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="font-bold text-[#003d4d] text-lg">Louwes App</span>
                    <div class="w-8"></div> 
                </div>

                {{-- Main Content --}}
                <main class="flex-1 overflow-y-auto p-6 md:p-12">
                    {{ $slot }}
                </main>
            </div>

            {{-- Overlay Mobile --}}
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false" 
                 class="fixed inset-0 bg-black/50 z-40 md:hidden" 
                 style="display: none;">
            </div>

        </div>
    </body>
</html>