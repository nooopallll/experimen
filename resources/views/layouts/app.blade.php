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
            
            /* Styling untuk Loading Bar (NProgress) */
            #nprogress .bar { background: #00e676 !important; height: 3px !important; }
            #nprogress .peg { box-shadow: 0 0 10px #00e676, 0 0 5px #00e676 !important; }
        </style>

        {{-- 1. NProgress (Loading Bar Visual) --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

        {{-- 2. Instant.page (Otomatis Preload Halaman saat Mouse Hover) --}}
        <script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0ygqeac2q7CVYMbh84GO0uHryrrjkGy9/qhXA6U/CSIjhMTp3"></script>

        {{-- 3. PWA Manifest (Agar bisa Install App & Fullscreen Permanen) --}}
        <link rel="manifest" href="{{ asset('manifest.json') }}">
    </head>
    <body class="font-sans antialiased h-full">
        
        <div x-data="{ sidebarOpen: false }" class="flex h-full bg-white relative">
            
            {{-- SIDEBAR --}}
            <aside x-cloak
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                   class="fixed inset-y-0 left-0 z-50 w-64 bg-[#003d4d] text-white flex flex-col p-6 rounded-r-[30px] transition-transform duration-300 ease-in-out xl:translate-x-0 xl:static xl:inset-auto shadow-2xl -translate-x-full h-full">
                
                {{-- Tombol Close (Mobile) --}}
                <button @click="sidebarOpen = false" class="xl:hidden absolute top-4 right-4 text-white hover:text-gray-300">
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

                        <a href="{{ route('owner.laporan') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.laporan') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Laporan Pendapatan
                        </a>

 <a href="{{ route('owner.kebutuhan') }}" 
           class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.kebutuhan') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
            Kebutuhan
        </a>

        <a href="{{ route('owner.settings.index') }}" class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.settings.index') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
                            Pengaturan
                        </a>


<a href="{{ route('owner.treatments.index') }}" 
   class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('owner.treatments.*') ? 'bg-white/20 font-semibold' : 'hover:bg-white/10' }}">
    Manajemen Treatment
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
            <div class="flex-1 flex flex-col h-full overflow-hidden bg-white w-full relative">
                
                {{-- Tombol Fullscreen (Desktop) --}}
                <button x-data="{ isFullscreen: false }"
                        x-init="isFullscreen = !!document.fullscreenElement; document.addEventListener('fullscreenchange', () => { isFullscreen = !!document.fullscreenElement })"
                        @click="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()" 
                        class="hidden xl:block absolute top-4 right-6 p-2 text-gray-400 hover:text-[#003d4d] transition duration-150 ease-in-out z-30"
                        :title="isFullscreen ? 'Keluar Fullscreen' : 'Masuk Fullscreen'">
                    {{-- Icon Enter --}}
                    <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                    {{-- Icon Exit --}}
                    <svg x-show="isFullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v3m0 0h3m-3 0l-5-5M15 4.5v3m0 0h-3m3 0l5-5M9 19.5v-3m0 0h3m-3 0l-5 5M15 19.5v-3m0 0h-3m3 0l5 5" />
                    </svg>
                </button>
                
                {{-- Header Mobile --}}
                <div class="p-4 xl:hidden flex justify-between items-center bg-white border-b shrink-0 z-40 relative shadow-sm">
                    <button @click="sidebarOpen = true" class="text-[#003d4d] focus:outline-none hover:bg-gray-100 p-2 rounded-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <span class="font-bold text-[#003d4d] text-lg">Louwes App</span>
                    
                    {{-- Tombol Fullscreen (Mobile) --}}
                    <button x-data="{ isFullscreen: false }"
                            x-init="isFullscreen = !!document.fullscreenElement; document.addEventListener('fullscreenchange', () => { isFullscreen = !!document.fullscreenElement })"
                            @click="document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()" 
                            class="text-[#003d4d] focus:outline-none hover:bg-gray-100 p-2 rounded-md"
                            :title="isFullscreen ? 'Keluar Fullscreen' : 'Masuk Fullscreen'">
                        {{-- Icon Enter --}}
                        <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                        {{-- Icon Exit --}}
                        <svg x-show="isFullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v3m0 0h3m-3 0l-5-5M15 4.5v3m0 0h-3m3 0l5-5M9 19.5v-3m0 0h3m-3 0l-5 5M15 19.5v-3m0 0h-3m3 0l5 5" />
                        </svg>
                    </button>
                </div>

                {{-- Main Content --}}
                <main class="flex-1 overflow-y-auto p-6 md:p-12 {{ request()->routeIs('dashboard', 'order.search', 'order.check') ? 'flex flex-col justify-center items-center' : '' }}">
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
                 class="fixed inset-0 bg-black/50 z-40 xl:hidden" 
                 style="display: none;">
            </div>

        </div>

        {{-- GLOBAL SCRIPT: PRINTER BLUETOOTH --}}
        <script>
            // Fungsi ini bisa dipanggil dari file view manapun (misal: saat AJAX Success)
            window.printOrderReceipt = async function(orderData) {
                // 1. Ambil Settingan dari LocalStorage
                const settings = JSON.parse(localStorage.getItem('printerSettings'));
                if (!settings) {
                    alert('Harap atur printer bluetooth di menu Pengaturan terlebih dahulu.');
                    return;
                }

                // LOGIKA BARU: Cek Tipe Koneksi (Bluetooth vs LAN)
                if (settings.type === 'lan') {
                    // Kirim request ke Backend PHP untuk handle printing socket
                    fetch(`/orders/${orderData.id}/print-lan`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            ip: settings.lanIp,
                            port: settings.lanPort,
                            header_text: settings.headerText,
                            footer_text: settings.footerText
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'error') alert(data.message);
                        else console.log(data.message);
                    })
                    .catch(err => alert('Gagal menghubungi server: ' + err));
                    return;
                }

                try {
                    // 2. Koneksi Bluetooth (Browser akan memunculkan popup pilih device)
                    // Note: Browser mewajibkan interaksi user (klik) untuk membuka popup ini.
                    const device = await navigator.bluetooth.requestDevice({
                        filters: [{ services: ['000018f0-0000-1000-8000-00805f9b34fb'] }]
                    });
                    
                    const server = await device.gatt.connect();
                    const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                    const characteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

                    // 3. Format Data Struk
                    const encoder = new TextEncoder();
                    const formatter = new Intl.NumberFormat('id-ID');
                    const date = new Date(orderData.created_at).toLocaleDateString('id-ID');
                    
                    let text = "\x1B\x40"; // Init Printer
                    text += "\x1B\x61\x01"; // Align Center
                    text += `\n${settings.headerText}\n`;
                    text += "--------------------------------\n";
                    
                    text += "\x1B\x61\x00"; // Align Left
                    text += `No Nota : ${orderData.no_invoice}\n`;
                    text += `Tgl     : ${date}\n`;
                    text += `Plg     : ${orderData.customer ? orderData.customer.nama : 'Guest'}\n`;
                    text += "--------------------------------\n";

                    if (orderData.details && orderData.details.length > 0) {
                        orderData.details.forEach(item => {
                            text += `${item.nama_barang}\n`;
                            text += `   ${item.layanan} : Rp ${formatter.format(item.harga)}\n`;
                        });
                    }
                    
                    text += "--------------------------------\n";
                    text += `TOTAL   : Rp ${formatter.format(orderData.total_harga)}\n`;
                    text += "--------------------------------\n";
                    text += "\x1B\x61\x01"; // Align Center
                    text += `${settings.footerText}\n`;
                    text += "\n\n\n"; 

                    await characteristic.writeValue(encoder.encode(text));
                } catch (error) {
                    console.error("Print Error:", error);
                    alert("Gagal mencetak struk: " + error.message);
                }
            };
        </script>
    </body>
</html>