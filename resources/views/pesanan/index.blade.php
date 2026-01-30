<x-app-layout>
    {{-- Main Container --}}
    <div class="bg-white min-h-screen p-4 md:p-8">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            {{-- Title --}}
            <h1 class="text-3xl font-bold text-[#7FB3D5] self-start md:self-center">
                Manajemen Pesanan
            </h1>
            
            {{-- Search & Filter --}}
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                
                {{-- Search Bar (Updated untuk Live Search) --}}
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    {{-- ID search-input ditambahkan untuk JavaScript --}}
                    <input type="text" 
                           id="search-input"
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search (Live...)" 
                           class="pl-10 pr-4 py-2 border border-gray-400 rounded-full w-full focus:ring-blue-500 focus:border-blue-500 text-gray-700 placeholder-gray-400 shadow-sm"
                           autocomplete="off">
                </div>

                {{-- Filter Button --}}
                <button class="flex items-center gap-2 font-semibold text-gray-700 hover:text-gray-900 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                    <span class="text-lg">Filter</span>
                </button>
            </div>
        </div>

        {{-- 
            AREA HASIL PENCARIAN
            Isi tabel dan mobile view dipanggil dari partial 
            agar bisa di-refresh via AJAX tanpa reload halaman.
        --}}
        <div id="search-results">
            @include('pesanan.partials.list')
        </div>

    </div>

{{-- SCRIPT JAVASCRIPT & AJAX LIVE SEARCH --}}
<script>
    // Fungsi Modal Tetap Ada
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Logic Live Search
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const resultsContainer = document.getElementById('search-results');
        let timeout = null;

        searchInput.addEventListener('keyup', function () {
            // Hapus timeout sebelumnya (Debouncing)
            clearTimeout(timeout);
            const query = this.value;

            // Tunggu 300ms setelah user berhenti mengetik
            timeout = setTimeout(() => {
                // Request AJAX ke controller
                fetch(`{{ route('pesanan.index') }}?search=${query}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Update isi div #search-results dengan HTML baru
                    resultsContainer.innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
            }, 300); 
        });
    });
</script>

</x-app-layout>