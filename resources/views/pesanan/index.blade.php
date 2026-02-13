<x-app-layout>
    {{-- CSS untuk menyembunyikan kolom Aksi pada modal rincian pesanan --}}
    <style>
        /* Target modal dengan ID yang diawali "modal-" dan sembunyikan kolom terakhir (Aksi) */
        div[id^="modal-"] table th:last-child,
        div[id^="modal-"] table td:last-child {
            display: none;
        }
    </style>

    {{-- Tambahkan x-data untuk mengontrol modal filter --}}
    <div class="bg-white min-h-screen p-4 md:p-8" x-data="{ showFilterModal: false }">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            {{-- Title --}}
            <h1 class="text-3xl font-bold text-[#7FB3D5] self-start md:self-center">
                Manajemen Pesanan
            </h1>
            
            {{-- Search & Filter Wrapper --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                
                {{-- SEARCH FORM --}}
                <form id="search-form" action="{{ route('pesanan.index') }}" method="GET" class="relative w-full sm:w-80 flex items-center">
                    {{-- Input Search --}}
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <input type="text" 
                               id="search-input"
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari Invoice, Nama..." 
                               class="pl-10 pr-12 py-2.5 border border-gray-300 rounded-full w-full focus:ring-blue-500 focus:border-blue-500 text-gray-700 placeholder-gray-400 shadow-sm transition"
                               autocomplete="off">
                    </div>

                    {{-- Tombol Search (Submit) --}}
                    <button type="submit" class="absolute right-1.5 top-1.5 p-1.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition shadow-sm" title="Cari">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>

                {{-- TOMBOL FILTER BARU (Mirip Laporan Owner) --}}
                <button @click="showFilterModal = true" class="group inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-sm text-gray-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm h-full whitespace-nowrap">
                    <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter
                </button>
            </div>
        </div>

        {{-- AREA HASIL PENCARIAN --}}
        <div id="search-results">
            @include('pesanan.partials.list')
        </div>

        {{-- MODAL FILTER (Dari Laporan Owner, disesuaikan Route-nya) --}}
        <div x-show="showFilterModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
             
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showFilterModal = false">
                {{-- Modal Header --}}
                <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-bold text-xl text-gray-800 uppercase tracking-wide">Filter Pesanan</h3>
                    <button @click="showFilterModal = false" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                </div>

                {{-- Modal Body (Action diarahkan ke pesanan.index) --}}
                <form action="{{ route('pesanan.index') }}" method="GET" class="p-6 space-y-4">
                    {{-- Pertahankan kata kunci pencarian jika ada --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Tanggal Masuk --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Masuk</label>
                            <input type="date" name="tgl_masuk" value="{{ request('tgl_masuk') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                        </div>

                        {{-- Tanggal Keluar --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Keluar</label>
                            <input type="date" name="tgl_keluar" value="{{ request('tgl_keluar') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                        </div>

                        {{-- Kategori Customer --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Customer</label>
                            <select name="kategori_customer" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Semua Kategori</option>
                                <option value="Member" {{ request('kategori_customer') == 'Member' ? 'selected' : '' }}>Member</option>
                                <option value="Repeat Order" {{ request('kategori_customer') == 'Repeat Order' ? 'selected' : '' }}>Repeat Order</option>
                                <option value="New Customer" {{ request('kategori_customer') == 'New Customer' ? 'selected' : '' }}>New Customer</option>
                            </select>
                        </div>

                        {{-- Treatment --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Treatment</label>
                            <select name="treatment" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                <option value="">Semua Treatment</option>
                                @if(isset($treatments))
                                    @foreach($treatments as $t)
                                        <option value="{{ $t->nama_treatment }}" {{ request('treatment') == $t->nama_treatment ? 'selected' : '' }}>{{ $t->nama_treatment }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Komplain Checkbox --}}
                    <div class="flex items-center gap-2 bg-red-50 p-3 rounded-lg border border-red-100">
                        <input type="checkbox" name="komplain" id="komplain" value="1" {{ request('komplain') ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <label for="komplain" class="text-sm font-bold text-red-700 select-none cursor-pointer">Tampilkan yang ada Komplain / Catatan Khusus</label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="button" @click="showFilterModal = false" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-lg transition mr-2">Batal</button>
                        {{-- Tombol Reset (Opsional) --}}
                        <a href="{{ route('pesanan.index') }}" class="px-5 py-2 text-red-600 font-bold hover:bg-red-50 rounded-lg transition mr-2">Reset</a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg transition transform hover:scale-105">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- SCRIPT SEARCH ENGINE (Tetap dipertahankan) --}}
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if(modal) modal.classList.remove('hidden');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if(modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const searchForm = document.getElementById('search-form');
            const resultsContainer = document.getElementById('search-results');
            let timeout = null;

            function performSearch(query) {
                resultsContainer.style.opacity = '0.5';
                // Ambil semua parameter URL saat ini (termasuk filter)
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', query);

                fetch(currentUrl.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                    resultsContainer.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsContainer.style.opacity = '1';
                });
            }

            if(searchInput) {
                searchInput.addEventListener('keyup', function () {
                    clearTimeout(timeout);
                    const query = this.value;
                    timeout = setTimeout(() => { performSearch(query); }, 300);
                });
            }

            if(searchForm) {
                searchForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    clearTimeout(timeout);
                    const query = searchInput.value;
                    performSearch(query);
                });
            }
        });
    </script>

</x-app-layout>