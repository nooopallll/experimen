<x-app-layout>
    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- HEADER --}}
    <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5] mb-6 md:mb-8">Input Kebutuhan Toko</h1>

    {{-- MAIN WRAPPER (ALPINE JS) --}}
    <div x-data="kebutuhanHandler()" class="max-w-6xl">
        
        {{-- HEADER TABEL (Hanya tampil di Desktop/Tablet) --}}
        <div class="hidden md:grid grid-cols-12 gap-4 mb-2 px-2">
            <div class="col-span-7 text-sm font-bold text-gray-500 uppercase">Nama Kebutuhan</div>
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Tanggal</div>
            <div class="col-span-2 text-sm font-bold text-gray-500 uppercase text-center">Stok Terakhir</div>
            <div class="col-span-1 text-sm font-bold text-gray-500 uppercase text-center">Aksi</div>
        </div>

        {{-- LIST BARIS INPUT --}}
        <div class="space-y-4 md:space-y-3" id="kebutuhan-list">
            <template x-for="(row, index) in rows" :key="row.id">
                {{-- CARD DI MOBILE / GRID DI DESKTOP --}}
                <div class="flex flex-col md:grid md:grid-cols-12 gap-3 md:gap-4 items-start md:items-center animate-fade-in bg-gray-50 md:bg-transparent p-4 md:p-0 border border-gray-200 md:border-none rounded-2xl md:rounded-none shadow-sm md:shadow-none">
                    
                    {{-- 1. NAMA KEBUTUHAN --}}
                    <div class="w-full md:col-span-7">
                        <label class="block md:hidden text-[10px] font-bold text-gray-500 uppercase mb-1">Nama Kebutuhan</label>
                        <input type="text" 
                               x-model="row.nama"
                               :disabled="row.saved"
                               placeholder="Contoh: Sabun Cuci Sepatu..." 
                               class="w-full p-3 bg-white border border-gray-200 rounded-xl text-gray-700 focus:ring-2 focus:ring-blue-400 disabled:bg-gray-100 disabled:text-gray-500 transition-colors">
                    </div>

                    {{-- BUNGKUS TANGGAL & STOK (Bersebelahan di Mobile) --}}
                    <div class="w-full flex md:contents gap-3">
                        {{-- 2. TANGGAL --}}
                        <div class="w-1/2 md:w-auto md:col-span-2">
                            <label class="block md:hidden text-[10px] font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                            <input type="text" 
                                   x-model="row.tanggal"
                                   readonly
                                   class="w-full p-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-600 font-bold cursor-not-allowed text-center text-sm">
                        </div>

                        {{-- 3. STOK TERAKHIR --}}
                        <div class="w-1/2 md:w-auto md:col-span-2">
                            <label class="block md:hidden text-[10px] font-bold text-gray-500 uppercase mb-1">Stok</label>
                            <div class="bg-white border border-gray-200 rounded-xl p-2 px-3 flex items-center transition-colors h-[46px]" :class="{'bg-gray-100': row.saved}">
                                <input type="number" 
                                       x-model="row.stok"
                                       :disabled="row.saved"
                                       placeholder="0" 
                                       class="w-full bg-transparent border-none p-0 text-gray-700 font-bold focus:ring-0 text-center disabled:text-gray-500">
                            </div>
                        </div>
                    </div>

                    {{-- 4. TOMBOL AKSI --}}
                    <div class="w-full md:w-auto md:col-span-1 flex justify-end md:justify-center gap-2 mt-2 md:mt-0 pt-3 md:pt-0 border-t md:border-none border-gray-200">
                        {{-- TOMBOL SIMPAN --}}
                        <button type="button" 
                                x-show="!row.saved"
                                @click="saveRow(index)"
                                class="w-full md:w-auto bg-[#3b66ff] hover:bg-blue-700 text-white p-3 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95 flex justify-center items-center"
                                title="Simpan">
                            <span class="md:hidden font-bold mr-2">Simpan</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                        </button>
                        
                        {{-- TOMBOL EDIT --}}
                        <button type="button" 
                                x-show="row.saved"
                                @click="editRow(index)"
                                class="w-full md:w-auto bg-yellow-500 hover:bg-yellow-600 text-white p-3 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95 flex justify-center items-center"
                                title="Edit Kembali">
                            <span class="md:hidden font-bold mr-2">Edit Data</span>
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
                    class="flex justify-center items-center w-full md:w-auto gap-2 px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-full font-bold hover:bg-gray-50 hover:text-gray-900 transition shadow-sm mx-auto">
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
                    const existingData = @json($kebutuhans);
                    if (existingData && existingData.length > 0) {
                        this.rows = existingData.map(item => ({
                            id: 'db-' + item.id,
                            db_id: item.id,
                            nama: item.nama,
                            tanggal: item.tanggal,
                            stok: item.stok,
                            saved: true
                        }));
                    } else {
                        this.addRow();
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