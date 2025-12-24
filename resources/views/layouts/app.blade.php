<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
<div class="min-h-screen flex bg-white">
    <aside class="w-64 bg-[#003d4d] text-white flex flex-col p-6 rounded-r-[30px]">
        <div class="flex flex-col items-center mb-10">
            <img src="{{ asset('path_to_avatar.png') }}" class="w-20 h-20 rounded-full border-2 border-gray-400 mb-2">
            <h2 class="font-bold text-lg">Louwes Care</h2>
            <p class="text-xs text-gray-300 text-center mt-2">JL. Ringroad Timur No 9, Plumbon , Banguntapan , Bantul , DIY 55196</p>
            <div class="text-xs text-gray-300 mt-1">
                <p>📧 Admin@gmail.com</p>
                <p>📞 081390154885</p>
            </div>
        </div>

        <nav class="space-y-2">
            <a href="{{ route('dashboard') }}" 
       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
       Input Order
    </a>

    <a href="{{ route('pesanan.index') }}" 
       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('pesanan.index') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
       Manajemen Pesanan
    </a>
           <a href="{{ route('kebutuhan.index') }}" 
       class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('kebutuhan.index') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
       Kebutuhan
    </a>
        </nav>
        <div class="mt-auto pt-10">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm text-gray-400 hover:text-white transition">
            Logout
        </button>
    </form>
</div>
    </aside>

    <main class="flex-1 p-12">
        {{ $slot }}
    </main>
</div>
    </body>
</html>
