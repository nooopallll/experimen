<x-app-layout>
    {{-- Main Container --}}
    <div class="bg-white min-h-screen p-4 md:p-8">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            {{-- Title --}}
            <h1 class="text-3xl font-bold text-[#7FB3D5] self-start md:self-center">
                Manajemen Pesanan
            </h1>
            
            {{-- Search & Filter Wrapper --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                
                {{-- SEARCH FORM (Updated) --}}
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

                {{-- Filter Button (Opsional / Bisa difungsikan nanti) --}}
                <button class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                    <span>Filter</span>
                </button>
            </div>
        </div>

        {{-- AREA HASIL PENCARIAN --}}
        <div id="search-results">
            @include('pesanan.partials.list')
        </div>

    </div>

{{-- SCRIPT SEARCH ENGINE --}}
<script>
    // 1. Fungsi Modal (Tetap Ada)
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.add('hidden');
    }

    // 2. Logic Search (Live + Tombol + Enter)
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const resultsContainer = document.getElementById('search-results');
        let timeout = null;

        // Fungsi Fetch Data AJAX
        function performSearch(query) {
            // Tampilkan loading (opsional, visual feedback)
            resultsContainer.style.opacity = '0.5';

            fetch(`{{ route('pesanan.index') }}?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                resultsContainer.style.opacity = '1';
                // Re-initialize event listeners jika ada elemen dinamis baru
            })
            .catch(error => {
                console.error('Error:', error);
                resultsContainer.style.opacity = '1';
            });
        }

        // Event A: Ketik (Live Search dengan Delay)
        searchInput.addEventListener('keyup', function () {
            clearTimeout(timeout);
            const query = this.value;
            
            timeout = setTimeout(() => {
                performSearch(query);
            }, 300); // Delay 300ms agar tidak spam request
        });

        // Event B: Submit Form (Tekan Enter atau Klik Tombol)
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Mencegah reload halaman
            clearTimeout(timeout); // Batalkan live search yang pending jika ada
            const query = searchInput.value;
            performSearch(query);
        });
    });
</script>

</x-app-layout>