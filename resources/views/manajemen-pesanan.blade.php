<x-app-layout>
    {{-- 
        ROOT WRAPPER (Wajib ada x-data untuk Alpine.js)
        Ini mengontrol logika Modal Pop-up
    --}}
        <div x-data="{ 
        showModal: false,
        form: {
            id: null,
            actionUrl: '',
            nama_customer: '',
            status: '',
            catatan: '',
            details: []
        },
        openModal(id, nama, status, catatan, detailsData) {
            this.form.id = id;
            this.form.actionUrl = '{{ url('/pesanan/update') }}/' + id;
            this.form.nama_customer = nama;
            this.form.status = status;
            this.form.catatan = catatan;
            this.form.details = detailsData; 
            this.showModal = true;
        }
    }" class="min-h-screen bg-gray-50 font-sans text-gray-900">

        <div class="py-8 px-4 md:px-8 max-w-7xl mx-auto">
            
            {{-- HEADER HALAMAN --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Manajemen Pesanan</h1>
                    <p class="text-sm text-gray-500 mt-1">Kelola data transaksi dan pantau progress produksi.</p>
                </div>

                {{-- FORM PENCARIAN --}}
                <div class="w-full md:w-auto">
                    <form action="{{ route('pesanan.index') }}" method="GET" class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                            class="block w-full md:w-80 pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl text-sm shadow-sm placeholder-gray-400
                                   focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200 ease-in-out"
                            placeholder="Cari Invoice, Nama, atau No HP...">
                    </form>
                </div>
            </div>

            {{-- TABEL DATA (Tampilan Desktop) --}}
            <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Layanan</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition duration-150">
                            {{-- Invoice & Tanggal --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900">{{ $order->no_invoice }}</span>
                                    <span class="text-xs text-gray-500 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </td>

                            {{-- Info Pelanggan --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900">{{ $order->customer->nama ?? $order->nama_customer }}</span>
                                    <span class="text-xs text-gray-500">{{ $order->customer->no_hp ?? $order->no_hp }}</span>
                                    @if($order->tipe_customer == 'Member')
                                        <span class="mt-1 w-max inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-600 border border-pink-100">
                                            MEMBER
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Layanan / Barang --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1">
                                    @foreach($order->details->take(2) as $item)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <div class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></div>
                                            <span class="truncate max-w-[180px]">{{ $item->nama_barang }}</span>
                                        </div>
                                    @endforeach
                                    @if($order->details->count() > 2)
                                        <span class="text-xs text-blue-500 font-medium pl-3.5">+{{ $order->details->count() - 2 }} item lainnya</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Total Harga --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-gray-900">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClasses = match($order->status_order) {
                                        'Selesai' => 'bg-green-50 text-green-700 border-green-200',
                                        'Proses' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'Diambil' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Batal' => 'bg-red-50 text-red-700 border-red-200',
                                        default => 'bg-gray-100 text-gray-600 border-gray-200',
                                    };
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border {{ $statusClasses }}">
                                    {{ $order->status_order }}
                                </span>
                            </td>

                            {{-- Tombol Aksi --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    {{-- TOMBOL EDIT --}}
                                    <button 
                                        @click='openModal(
                                            "{{ $order->id }}", 
                                            "{{ addslashes($order->customer->nama ?? $order->nama_customer) }}", 
                                            "{{ $order->status_order }}", 
                                            "{{ addslashes($order->catatan ?? '') }}",
                                            {{ $order->details->toJson() }}
                                        )'
                                        class="p-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm" title="Edit Data">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </button>

                                    {{-- TOMBOL DETAIL --}}
                                    <a href="{{ route('pesanan.show', $order->id) }}" class="p-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition" title="Lihat Detail">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p>Belum ada pesanan yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN MOBILE (Cards) --}}
            <div class="grid grid-cols-1 gap-4 md:hidden">
                @foreach($orders as $order)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 relative">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-xs font-bold text-blue-500 uppercase">{{ $order->no_invoice }}</span>
                            <h2 class="font-bold text-gray-800">{{ $order->customer->nama ?? $order->nama_customer }}</h2>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        
                        @php
                            $mobileStatusClass = match($order->status_order) {
                                'Selesai' => 'bg-green-50 text-green-700 border-green-200',
                                'Proses' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'Diambil' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Batal' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-gray-100 text-gray-600 border-gray-200',
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $mobileStatusClass }}">
                            {{ $order->status_order }}
                        </span>
                    </div>

                    <div class="border-t border-dashed border-gray-100 py-4 mb-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Total Tagihan</span>
                            <span class="text-lg font-bold text-gray-900">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            @click="openModal(
                                '{{ $order->id }}', 
                                '{{ addslashes($order->customer->nama ?? $order->nama_customer) }}', 
                                '{{ $order->status_order }}', 
                                '{{ addslashes($order->catatan ?? '') }}',
                                {{ $order->details->toJson() }}
                            )"
                            class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition">
                            Edit Data
                        </button>
                        <a href="{{ route('pesanan.show', $order->id) }}" class="flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition">
                            Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        </div>

        {{-- ============================================= --}}
        {{--      MODAL POP-UP EDIT (DESAIN UI BARU)       --}}
        {{-- ============================================= --}}
        <div 
            x-show="showModal" 
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            {{-- 1. Backdrop Gelap & Blur --}}
            <div 
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                @click="showModal = false"
            ></div>

            {{-- 2. Container Modal --}}
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div 
                    x-show="showModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all"
                >
                    {{-- Header Modal --}}
                    <div class="bg-white px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900" id="modal-title">Edit Pesanan</h3>
                            <p class="text-xs text-gray-500 mt-1">Perbarui data pesanan #<span x-text="form.id"></span></p>
                        </div>
                        <button @click="showModal = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 transition">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Form Content --}}
                    <form method="POST" :action="form.actionUrl">
                        @csrf
                        @method('PATCH')
                        
                        <div class="px-6 py-6 space-y-5">
                            
                            {{-- Input Nama --}}
                            <div class="space-y-1">
                                <label for="nama_customer" class="block text-sm font-semibold text-gray-700">Nama Pelanggan</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="nama_customer" x-model="form.nama_customer" id="nama_customer" 
                                        class="block w-full rounded-xl border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 transition" 
                                        placeholder="Masukkan nama lengkap">
                                </div>
                            </div>

                            {{-- Input Status (DIPERBAIKI DENGAN LOGIKA WARNA DINAMIS) --}}
                            <div class="space-y-1">
                                <label for="status" class="block text-sm font-semibold text-gray-700">Status Pesanan</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <select name="status" x-model="form.status" id="status" 
                                        :class="{
                                            'bg-green-50 text-green-700 border-green-200': form.status === 'Selesai',
                                            'bg-blue-50 text-blue-700 border-blue-200': form.status === 'Diambil',
                                            'bg-yellow-50 text-yellow-700 border-yellow-200': form.status === 'Proses',
                                            'bg-red-50 text-red-700 border-red-200': form.status === 'Batal',
                                            'bg-white text-gray-700': !['Selesai', 'Diambil', 'Proses', 'Batal'].includes(form.status)
                                        }"
                                        class="block w-full rounded-xl border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2.5 transition font-semibold">
                                        <option value="Pending">Pending</option>
                                        <option value="Proses">Proses</option>
                                        <option value="Selesai">Selesai</option>
                                        <option value="Diambil">Diambil</option>
                                        <option value="Batal">Batal</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Rincian Pesanan (Bisa Di-edit Langsung) --}}
                            <div class="space-y-2 col-span-2 pt-2">
                                <label class="block text-sm font-bold text-gray-700">Rincian Layanan</label>
                                
                                <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-50 text-xs font-bold text-gray-600 uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 w-1/4">Item / Barang</th>
                                                <th class="px-4 py-3 w-1/4">Layanan</th>
                                                <th class="px-4 py-3 w-[15%]">Est. Keluar</th>
                                                <th class="px-4 py-3 w-[15%]">Status Item</th>
                                                <th class="px-4 py-3 w-[20%] text-right">Harga (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            <template x-for="(detail, index) in form.details" :key="detail.id">
                                                <tr class="hover:bg-blue-50/30 transition-colors">
                                                    
                                                    {{-- 1. INPUT ITEM / BARANG --}}
                                                    <td class="p-2">
                                                        <input type="text" :name="'details['+detail.id+'][nama_barang]'" x-model="detail.nama_barang" 
                                                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all cursor-text">
                                                    </td>
                                                    
                                                    {{-- 2. INPUT LAYANAN --}}
                                                    <td class="p-2">
                                                        <input type="text" :name="'details['+detail.id+'][layanan]'" x-model="detail.layanan" 
                                                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all cursor-text">
                                                    </td>

                                                    {{-- 3. INPUT ESTIMASI KELUAR (KALENDER) --}}
                                                    <td class="p-2">
                                                        <input type="date" :name="'details['+detail.id+'][estimasi_keluar]'" x-model="detail.estimasi_keluar ? detail.estimasi_keluar.substring(0, 10) : ''" 
                                                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all cursor-pointer">
                                                    </td>

                                                    {{-- 4. INPUT STATUS (JUGA DIPERBAIKI WARNANYA) --}}
                                                    <td class="p-2">
                                                        <select :name="'details['+detail.id+'][status]'" x-model="detail.status" 
                                                            :class="{
                                                                'bg-green-50 text-green-700 border-green-200': detail.status === 'Selesai',
                                                                'bg-blue-50 text-blue-700 border-blue-200': detail.status === 'Diambil',
                                                                'bg-yellow-50 text-yellow-700 border-yellow-200': detail.status === 'Proses',
                                                                'bg-gray-50 text-gray-700 border-gray-200': !['Selesai', 'Diambil', 'Proses'].includes(detail.status)
                                                            }"
                                                            class="w-full px-3 py-2 rounded-lg text-xs font-semibold focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all cursor-pointer">
                                                            <option value="Proses">Proses</option>
                                                            <option value="Selesai">Selesai</option>
                                                            <option value="Diambil">Diambil</option>
                                                        </select>
                                                    </td>

                                                    {{-- 5. INPUT HARGA --}}
                                                    <td class="p-2">
                                                        <input type="number" :name="'details['+detail.id+'][harga]'" x-model="detail.harga" 
                                                            class="w-full px-3 py-2 text-right bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all cursor-text" 
                                                            placeholder="0">
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        
                                        {{-- BARIS TOTAL TAGIHAN --}}
                                        <tfoot class="bg-gray-50/80 border-t border-gray-200">
                                            <tr>
                                                <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-600 uppercase tracking-wider">
                                                    Total Tagihan
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <span class="text-lg font-black text-blue-600" 
                                                        x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(form.details.reduce((sum, item) => sum + parseInt(item.harga || 0), 0))">
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            {{-- Input Catatan --}}
                            <div class="space-y-1">
                                <label for="catatan" class="block text-sm font-semibold text-gray-700">Catatan Tambahan</label>
                                <textarea name="catatan" x-model="form.catatan" id="catatan" rows="3" 
                                    class="block w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-3 transition" 
                                    placeholder="Tulis catatan jika ada..."></textarea>
                            </div>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="showModal = false" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
                                Batal
                            </button>
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-xl font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition text-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-6">
            {{-- $orders->links() --}}
        </div>
    </div>

    <x-modal name="claim-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    🎁 Pilih Reward
                </h2>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                Pilih item di bawah ini untuk diklaim oleh customer (pastikan poin mencukupi).
            </p>

            {{-- 
               CATATAN: Pastikan variable $treatments dikirim dari Controller 
               ke view ini agar tidak error.
            --}}
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                @if(isset($treatments) && $treatments->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($treatments as $treatment)
                            <li class="p-4 hover:bg-gray-50 flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-800">{{ $treatment->nama_treatment }}</p>
                                    <p class="text-xs text-gray-500">Harga Normal: Rp {{ number_format($treatment->harga, 0, ',', '.') }}</p>
                                </div>
                                
                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-bold transition shadow-sm">
                                    Pilih
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="p-8 text-center flex flex-col items-center justify-center text-gray-400">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        <span>Belum ada data reward tersedia.</span>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Tutup') }}
                </x-secondary-button>
            </div>
        </div>
    </x-modal>

</x-app-layout>