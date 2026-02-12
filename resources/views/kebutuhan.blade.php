<x-app-layout>
    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- HEADER --}}
    <h1 class="text-4xl font-bold text-[#7FB3D5] mb-8">Input Kebutuhan Toko</h1>

    {{-- MAIN WRAPPER (ALPINE JS) --}}
    <div x-data="kebutuhanHandler()" class="max-w-6xl">
        
        {{-- HEADER TABEL (Proporsi Diubah) --}}
        <div class="grid grid-cols-12 gap-4 mb-2 px-2">
            {{-- Nama diperlebar (7) --}}
            <div class="col-span-7 text-sm font-bold text-gray-500 uppercase">Nama Kebutuhan</div>
            {{-- Tanggal diperkecil (2) & Tengah --}}
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Tanggal</div>
            {{-- Stok diperkecil (2) & Tengah --}}
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Stok Terakhir</div>
            {{-- Aksi (1) --}}
            <div class="col-span-1 text-sm font-bold text-gray-500 uppercase text-center">Aksi</div>
        </div>

        {{-- LIST BARIS INPUT --}}
        <div class="space-y-3" id="kebutuhan-list">
            <template x-for="(row, index) in rows" :key="row.id">
                <div class="grid grid-cols-12 gap-4 items-center animate-fade-in">
                    
                    {{-- 1. NAMA KEBUTUHAN (Lebar: 7) --}}
                    <div class="col-span-7">
                        <input type="text" 
                               x-model="row.nama"
                               :disabled="row.saved"
                               placeholder="Contoh: Sabun Cuci Sepatu..." 
                               class="w-full p-3 bg-white border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-blue-400 disabled:bg-gray-100 disabled:text-gray-500 transition-colors">
                    </div>

                    {{-- 2. TANGGAL (Lebar: 2) --}}
                    <div class="col-span-2">
                        <input type="text" 
                               x-model="row.tanggal"
                               readonly
                               class="w-full p-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-600 font-bold cursor-not-allowed text-center text-sm">
                    </div>

                    {{-- 3. STOK TERAKHIR (Lebar: 2) --}}
                    <div class="col-span-2">
                        <div class="bg-white border border-gray-200 rounded-xl p-2 px-3 flex items-center transition-colors" :class="{'bg-gray-100': row.saved}">
                            <input type="text" 
                                   x-model="row.stok"
                                   :disabled="row.saved"
                                   placeholder="0" 
                                   class="w-full bg-transparent border-none p-1 text-gray-700 font-bold focus:ring-0 text-center disabled:text-gray-500">
                        </div>
                    </div>

                    {{-- 4. TOMBOL AKSI (Lebar: 1) --}}
                    <div class="col-span-1 flex justify-center gap-2">
                        
                        {{-- TOMBOL SIMPAN --}}
                        <button type="button" 
                                x-show="!row.saved"
                                @click="saveRow(index)"
                                class="bg-[#3b66ff] hover:bg-blue-700 text-white p-3 rounded-xl shadow-md transition transform hover:scale-110 active:scale-95"
                                title="Simpan">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                        </button>
                        
                        {{-- TOMBOL EDIT --}}
                        <button type="button" 
                                x-show="row.saved"
                                @click="editRow(index)"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white p-3 rounded-xl shadow-md transition transform hover:scale-110 active:scale-95"
                                title="Edit Kembali">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>

                    </div>
                </div>
            </template>
        </div>

        {{-- TOMBOL TAMBAH BARIS --}}
        <div class="mt-8">
            <button type="button" 
                    @click="addRow()"
                    class="flex items-center gap-2 px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-full font-bold hover:bg-gray-50 hover:text-gray-900 transition shadow-sm mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Baris Baru
            </button>
        </div>

    </div>

    {{-- LOGIKA JAVASCRIPT --}}
    <script>
        function kebutuhanHandler() {
            return {
                rows: [],
                
                init() {
    // Memastikan data dari PHP masuk ke variabel JS
    const existingData = @json($kebutuhans);

    if (existingData && existingData.length > 0) {
        this.rows = existingData.map(item => ({
            id: 'db-' + item.id, // ID unik untuk Alpine
            db_id: item.id,      // ID asli database untuk keperluan update/save
            nama: item.nama,     // Mengambil dari kunci 'nama' di Controller
            tanggal: item.tanggal,
            stok: item.stok,     // Mengambil dari kunci 'stok' di Controller
            saved: true          // Menandakan baris ini data lama (readonly)
        }));
    } else {
        this.addRow(); // Jika benar-benar kosong, baru tambah baris baru
    }
},

                getTodayDate() {
                    const today = new Date();
                    const dd = String(today.getDate()).padStart(2, '0');
                    const mm = String(today.getMonth() + 1).padStart(2, '0'); 
                    const yyyy = today.getFullYear();
                    return `${dd}/${mm}/${yyyy}`;
                },

                addRow() {
                    this.rows.push({
                        id: Date.now(), 
                        db_id: null, 
                        nama: '',
                        tanggal: this.getTodayDate(), 
                        stok: '',
                        saved: false 
                    });
                },

                editRow(index) {
                    this.rows[index].saved = false;
                },

                saveRow(index) {
                    let row = this.rows[index];
                    
                    if (!row.nama || !row.stok) {
                        alert("Mohon lengkapi Nama Kebutuhan dan Stok.");
                        return;
                    }

                    let formData = new FormData();
                    formData.append('nama', row.nama);
                    formData.append('stok', row.stok);
                    formData.append('tanggal', row.tanggal);
                    formData.append('_token', '{{ csrf_token() }}');

                    if (row.db_id) {
                        formData.append('id', row.db_id);
                    }

                    fetch("{{ route('kebutuhan.store') }}", {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            row.saved = true;
                            row.db_id = data.data.id; 
                        } else {
                            alert('Gagal menyimpan: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal koneksi ke server.');
                    });
                }
            }
        }
    </script>
</x-app-layout>