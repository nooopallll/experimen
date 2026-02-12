<x-app-layout>
    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- HEADER --}}
    <h1 class="text-4xl font-bold text-[#7FB3D5] mb-8">Daftar Belanja Kebutuhan</h1>

    {{-- MAIN WRAPPER (ALPINE JS) --}}
    <div x-data="ownerKebutuhanHandler()" class="max-w-6xl">
        
        {{-- HEADER TABEL (Proporsi Sama Dengan Admin) --}}
        <div class="grid grid-cols-12 gap-4 mb-2 px-2">
            <div class="col-span-7 text-sm font-bold text-gray-500 uppercase">Nama Kebutuhan</div>
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Tanggal</div>
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Stok Terakhir</div>
            <div class="col-span-1 text-sm font-bold text-gray-500 uppercase text-center">Beli</div>
        </div>

        {{-- LIST DATA --}}
        <div class="space-y-3">
            <template x-for="(row, index) in rows" :key="row.db_id">
                <div class="grid grid-cols-12 gap-4 items-center animate-fade-in">
                    
                    {{-- 1. NAMA KEBUTUHAN (Read Only) --}}
                    <div class="col-span-7">
                        <input type="text" 
                               x-model="row.nama"
                               readonly
                               class="w-full p-3 bg-white border border-gray-200 rounded-xl text-gray-700 font-medium">
                    </div>

                    {{-- 2. TANGGAL (Read Only) --}}
                    <div class="col-span-2">
                        <input type="text" 
                               x-model="row.tanggal"
                               readonly
                               class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-600 text-center text-sm">
                    </div>

                    {{-- 3. STOK TERAKHIR (Read Only) --}}
                    <div class="col-span-2">
                        <div class="bg-red-50 border border-red-100 rounded-xl p-3 text-center">
                            <span x-text="row.stok" class="text-red-600 font-bold"></span>
                        </div>
                    </div>

                    {{-- 4. TOMBOL CENTANG (HAPUS) --}}
                    <div class="col-span-1 flex justify-center">
                        <button type="button" 
                                @click="confirmPurchase(row.db_id, index)"
                                class="group flex items-center justify-center w-12 h-12 rounded-xl border-2 border-gray-200 text-gray-300 hover:border-green-500 hover:bg-green-500 hover:text-white transition-all duration-200 shadow-sm"
                                title="Tandai Sudah Dibeli">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>

            {{-- TAMPILAN JIKA KOSONG --}}
            <template x-if="rows.length === 0">
                <div class="bg-white p-10 rounded-3xl border border-dashed border-gray-300 text-center">
                    <p class="text-gray-400 font-medium">Tidak ada daftar belanjaan saat ini.</p>
                </div>
            </template>
        </div>
    </div>

    {{-- LOGIKA JAVASCRIPT --}}
    <script>
        function ownerKebutuhanHandler() {
            return {
                rows: [],
                
                init() {
                    // AMBIL DATA DARI DATABASE
                    const existingData = @json($kebutuhans);
                    
                    // Kita asumsikan data yang datang dari Controller sudah diformat di index()
                    // Jika belum diformat, gunakan map seperti ini:
                    this.rows = existingData.map(item => ({
                        db_id: item.id,                
                        nama: item.nama || item.nama_kebutuhan,
                        tanggal: item.tanggal,
                        stok: item.stok || item.stok_terakhir
                    }));
                },

                confirmPurchase(dbId, index) {
                    if (confirm('Apakah barang ini sudah dibeli? Data akan dihapus dari daftar.')) {
                        this.deleteRow(dbId, index);
                    }
                },

                deleteRow(dbId, index) {
                    fetch(`/owner/kebutuhan/${dbId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success' || data.message) {
                            // Hapus dari tampilan Alpine
                            this.rows.splice(index, 1);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal menghapus data.');
                    });
                }
            }
        }
    </script>
</x-app-layout>