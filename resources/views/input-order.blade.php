<x-app-layout>
    {{-- Inisialisasi Alpine.js --}}
    <div x-data="{ 
        showPaymentModal: false, 
        paymentMethod: 'Tunai',   
        paymentStatus: 'Lunas',   
        totalPrice: 0, 
        cashAmount: 0,
        
        get change() { return Math.max(0, this.cashAmount - this.totalPrice); },
        
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        },

        calculateTotal() {
            let total = 0;
            document.querySelectorAll('.harga-input').forEach(input => {
                let val = parseInt(input.value.replace(/\./g, '')) || 0;
                total += val;
            });
            this.totalPrice = total;
            if(this.paymentStatus === 'Lunas') { this.cashAmount = total; }
        }
    }" class="min-h-screen bg-white p-4 md:p-8">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center gap-3 md:gap-4 mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5]">Input Order</h1>
            <span id="badge-status" class="text-sm md:text-xl font-bold px-3 py-1 rounded-full border {{ $color ?? 'text-blue-600 bg-blue-100 border-blue-200' }}">
                {{ $status ?? 'Baru' }}
            </span>
        </div>

        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf
            
            {{-- HIDDEN INPUTS --}}
            <input type="hidden" name="tipe_customer" id="tipe_customer_input" value="{{ $status ?? 'Baru' }}">
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">
            <input type="hidden" name="metode_pembayaran" x-model="paymentMethod">
            <input type="hidden" name="status_pembayaran" x-model="paymentStatus">

            {{-- BARIS 1: NAMA & NO HP --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Customer</label>
                    <input type="text" name="nama_customer" id="nama_customer"
                           value="{{ $customer->nama ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold placeholder-gray-400" 
                           placeholder="Masukkan nama">
                </div>
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex items-center relative hover:shadow-md transition">
                    <div class="border-r border-gray-400 pr-4 mr-4 h-full flex items-center">
                        <label class="text-sm font-semibold text-gray-600 whitespace-nowrap">No HP</label>
                    </div>
                    <input type="number" name="no_hp" id="no_hp" onkeyup="cekCustomer()"
                           value="{{ $no_hp ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold"
                           placeholder="08...">
                </div>
            </div>

            {{-- BARIS 2: JUMLAH ITEM (SEPATU) & CS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex justify-between items-center hover:shadow-md transition">
                    <label class="text-sm font-semibold text-gray-600">Jumlah Sepatu</label>
                    <div class="flex items-center gap-6">
                        <button type="button" onclick="adjustJumlah(-1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none transition transform hover:scale-110">&minus;</button>
                        <input type="number" id="inputJumlah" name="jumlah_total" value="1" readonly 
                               class="w-12 bg-transparent border-none text-center font-bold text-lg p-0 focus:ring-0">
                        <button type="button" onclick="adjustJumlah(1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none transition transform hover:scale-110">&plus;</button>
                    </div>
                </div>
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex flex-col justify-center hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Cs</label>
                    <select name="cs" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium cursor-pointer">
                        <option value="Admin 1">Admin 1</option>
                        <option value="Admin 2">Admin 2</option>
                    </select>
                </div>
            </div>

            {{-- BARIS 3: ITEM CONTAINER (LOOPING GROUP ITEM) --}}
            <div id="itemsContainer" class="space-y-6 mb-6">
                
                {{-- ITEM GROUP: SATU SEPATU --}}
                <div class="item-group bg-[#E0E0E0] p-4 rounded-xl shadow-sm relative group animate-fade-in hover:shadow-md transition">
                    
                    {{-- HEADER ITEM: NAMA & CATATAN --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-400">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Item Name (Sepatu)</label>
                            <input type="text" 
                                   class="main-item-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-bold text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Nama Barang (Cth: Nike Air Jordan)..."
                                   oninput="syncMainInputs(this, 'hidden-item')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Catatan Umum</label>
                            <input type="text" 
                                   class="main-catatan-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-medium text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Catatan kondisi sepatu..."
                                   oninput="syncMainInputs(this, 'hidden-catatan')">
                        </div>
                    </div>

                    {{-- TREATMENT LIST --}}
                    <div class="treatments-container space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wide">Daftar Treatment</label>
                        
                        {{-- ROW TREATMENT --}}
                        <div class="treatment-row grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-3 rounded-lg border border-gray-300 relative shadow-sm">
                            
                            <input type="hidden" name="item[]" class="hidden-item">
                            <input type="hidden" name="catatan[]" class="hidden-catatan">

                            {{-- 1. KATEGORI --}}
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Kategori</label>
                                <select class="category-select w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500" 
                                        onchange="filterTreatments(this)">
                                    <option value="">- Pilih -</option>
                                    @foreach($treatments->pluck('kategori')->unique()->values() as $kategori)
                                        @if(!empty($kategori))
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2. LAYANAN --}}
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Layanan</label>
                                <select name="kategori_treatment[]" 
                                        class="treatment-select w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500">
                                    <option value="">- Pilih Kat. Dulu -</option>
                                </select>
                            </div>

                            {{-- 3. ESTIMASI (PERBAIKAN ONE CLICK) --}}
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Estimasi</label>
                                {{-- 
                                    Trik: type="date" secara default (agar 1 klik).
                                    Class "date-placeholder" akan memanipulasi CSS agar terlihat seperti teks biasa saat kosong.
                                --}}
                                <input type="date" 
                                       name="tanggal_keluar[]" 
                                       class="date-placeholder w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 focus:ring-blue-500"
                                       onchange="this.setAttribute('data-date', this.value)">
                            </div>

                            {{-- 4. HARGA --}}
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-xs font-bold text-gray-500">Rp</span>
                                    <input type="text" name="harga[]" 
                                           class="harga-input w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 pl-7 text-xs font-bold text-gray-800 focus:ring-blue-500" 
                                           placeholder="0">
                                </div>
                            </div>

                            {{-- TOMBOL HAPUS --}}
                            <button type="button" onclick="removeTreatment(this)" class="btn-remove-treatment absolute -top-2 -right-2 text-red-500 hover:text-red-700 bg-white rounded-full p-0.5 shadow-md hidden group-hover:block z-10" title="Hapus Treatment ini">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>

                        </div>
                    </div>

                    {{-- TOMBOL TAMBAH TREATMENT --}}
                    <div class="mt-3 text-right">
                        <button type="button" onclick="addTreatmentRow(this)" 
                                class="inline-flex items-center px-3 py-1 bg-white border border-gray-400 text-gray-700 text-xs font-bold rounded-full hover:bg-gray-100 transition shadow-sm">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Treatment Lain
                        </button>
                    </div>
                </div>
            </div>

            {{-- BARIS 4: POINT & TIPE CUSTOMER --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-2 pl-24 h-full w-full flex flex-col justify-center items-center md:col-start-5 {{ ($is_member ?? false) ? '' : 'hidden' }}">
                    <label class=" text-sm font-semibold text-gray-600 mb-1 "> Point </label>
                    <div class="flex items-center gap-3">
                        <span id="poin-text" class="text-gray-800 font-bold text-lg">{{ $poin ?? 0 }}/8</span>
                        @php $poinSekarang = $customer->member->poin ?? 0; $targetPoin = 8; @endphp
                        <button type="button" id="btn-claim" onclick="claimReward()" 
                                class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow hover:bg-blue-700 transition {{ $poinSekarang >= $targetPoin ? '' : 'hidden' }}">
                            Claim
                        </button>
                    </div>
                </div>
                <div id="box-tipe-customer" class="bg-[#E0E0E0] rounded-lg p-3 px-5 {{ ($status ?? 'New Customer') == 'New Customer' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" id="display_tipe_customer" value="{{ $status ?? 'Baru' }}" readonly 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                </div>
            </div>

            {{-- BARIS 5: SUMBER INFO --}}
            <div id="box-sumber-info" class="grid grid-cols-1 mb-12 {{ ($status ?? 'New Customer') == 'New Customer' ? '' : 'hidden' }}">
                <div class="md:w-1/2 bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium cursor-pointer">
                        <option value="Instagram">Instagram</option>
                        <option value="Teman">Teman</option>
                        <option value="Google Maps">Google Maps</option>
                        <option value="Lewat">Lewat Depan Toko</option>
                    </select>
                </div>
            </div>

            {{-- FOOTER BUTTONS --}}
            <div class="flex justify-end gap-4">
                <button type="button" id="btn-daftar-member" onclick="openMemberModal()" 
                        class="bg-[#3b66ff] text-white px-10 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition {{ ($is_member ?? false) ? 'hidden' : '' }}">
                    MEMBER
                </button>
                <button type="button" x-on:click="calculateTotal(); showPaymentModal = true"
                        class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105">
                    PROSES PEMBAYARAN
                </button>
            </div>

            {{-- MODAL PAYMENT --}}
            <div x-show="showPaymentModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60"
                 x-transition:enter="ease-out duration-300" style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.away="showPaymentModal = false">
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3>
                        <button type="button" x-on:click="showPaymentModal = false" class="text-white font-bold text-2xl">&times;</button>
                    </div>
                    <div class="p-6">
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" x-text="formatRupiah(totalPrice)"></div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Metode</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer"><input type="radio" value="Tunai" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Tunai</div></label>
                                <label class="cursor-pointer"><input type="radio" value="Transfer" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Transfer</div></label>
                                <label class="cursor-pointer"><input type="radio" value="QRIS" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">QRIS</div></label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Status Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer"><input type="radio" value="Lunas" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = totalPrice"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Lunas</div></label>
                                <label class="cursor-pointer"><input type="radio" value="DP" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = 0"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-yellow-500 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">DP</div></label>
                            </div>
                        </div>
                        <div x-show="paymentStatus !== 'Lunas'" class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1" x-text="paymentStatus === 'DP' ? 'Nominal DP' : 'Uang Diterima'"></label>
                            <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="number" name="paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" x-model.number="cashAmount" placeholder="0"></div>
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200"><span class="text-sm text-gray-500 font-bold">Kekurangan:</span><span class="font-black text-red-600 text-xl" x-text="formatRupiah(change)"></span></div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" x-on:click="showPaymentModal = false" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- MODAL & SCRIPT --}}
    @include('components.member-modal')
    <div id="modal-claim-reward" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden"><div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in"><div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Klaim Reward</h3><button type="button" onclick="closeClaimModal()" class="text-white font-bold text-2xl hover:text-gray-200">&times;</button></div><div class="p-6"><div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100"><span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span><div class="text-3xl font-black text-[#3b66ff] mt-1"><span id="display-poin-modal">0</span> pts</div><p class="text-xs text-gray-500 mt-1 font-bold">Tukar 8 Poin dengan Reward</p></div><form id="formClaimReward"><div class="flex justify-end space-x-3"><button type="button" onclick="closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button><button type="button" onclick="submitClaimReward()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">Klaim</button></div></form></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const rawTreatments = @json($treatments ?? []);
        const rupiahFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.item-group').forEach(group => {
                const mainItem = group.querySelector('.main-item-input');
                const mainCatatan = group.querySelector('.main-catatan-input');
                if(mainItem) mainItem.addEventListener('input', (e) => syncMainInputs(e.target, 'hidden-item'));
                if(mainCatatan) mainCatatan.addEventListener('input', (e) => syncMainInputs(e.target, 'hidden-catatan'));
                group.querySelectorAll('.treatment-row').forEach(row => attachEventsToTreatmentRow(row));
            });
            const hpInput = document.getElementById('no_hp');
            if(hpInput) hpInput.addEventListener('keyup', cekCustomer);
        });

        function filterTreatments(categorySelect) {
            const row = categorySelect.closest('.treatment-row');
            const treatmentSelect = row.querySelector('.treatment-select');
            treatmentSelect.innerHTML = '<option value="">- Pilih Kat. Dulu -</option>';
            const selectedCategory = categorySelect.value;
            if (!selectedCategory) return;
            const catLower = selectedCategory.trim().toLowerCase();
            const filtered = rawTreatments.filter(t => t.kategori && t.kategori.trim().toLowerCase() === catLower);
            const fragment = document.createDocumentFragment();
            filtered.forEach(t => {
                const option = document.createElement('option');
                option.value = t.nama_treatment; option.textContent = t.nama_treatment;
                fragment.appendChild(option);
            });
            treatmentSelect.appendChild(fragment);
        }

        function attachEventsToTreatmentRow(row) {
            const priceInput = row.querySelector('.harga-input');
            if(!priceInput) return;
            const newPriceInput = priceInput.cloneNode(true);
            priceInput.parentNode.replaceChild(newPriceInput, priceInput);
            newPriceInput.addEventListener('input', function(e) {
                let raw = this.value.replace(/[^0-9]/g, '');
                this.value = raw ? rupiahFormatter.format(raw) : '';
            });
            newPriceInput.addEventListener('keydown', function(e) {
                if ([46, 8, 9, 27, 13, 116].includes(e.keyCode) || (e.ctrlKey === true || e.metaKey === true)) return;
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) e.preventDefault();
            });
        }

        function syncMainInputs(source, targetClass) {
            const group = source.closest('.item-group');
            const hiddenInputs = group.querySelectorAll('.' + targetClass);
            hiddenInputs.forEach(input => input.value = source.value);
        }

        // --- TAMBAH TREATMENT ---
        window.addTreatmentRow = function(btn) {
            const group = btn.closest('.item-group');
            const container = group.querySelector('.treatments-container');
            const template = container.querySelector('.treatment-row'); 
            const newRow = template.cloneNode(true);
            
            newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            newRow.querySelector('.treatment-select').innerHTML = '<option value="">- Pilih Kat. Dulu -</option>';
            newRow.querySelectorAll('input').forEach(i => {
                if(i.classList.contains('hidden-item')) {
                    i.value = group.querySelector('.main-item-input').value;
                } else if(i.classList.contains('hidden-catatan')) {
                    i.value = group.querySelector('.main-catatan-input').value;
                } else {
                    i.value = ''; 
                    // RESET Date ke tampilan Placeholder (attribute data-date kosong)
                    if(i.name === 'tanggal_keluar[]') i.setAttribute('data-date', '');
                }
            });

            const btnRemove = newRow.querySelector('.btn-remove-treatment');
            if(btnRemove) btnRemove.classList.remove('hidden');
            attachEventsToTreatmentRow(newRow);
            newRow.classList.add('animate-fade-in');
            container.appendChild(newRow);
        }

        window.removeTreatment = function(btn) {
            const row = btn.closest('.treatment-row');
            const container = row.parentElement;
            if (container.querySelectorAll('.treatment-row').length > 1) { row.remove(); } 
            else { alert("Minimal satu treatment harus ada."); }
        }

        // --- TAMBAH JUMLAH SEPATU ---
        window.adjustJumlah = function(delta) {
            const input = document.getElementById('inputJumlah');
            const container = document.getElementById('itemsContainer');
            let val = parseInt(input.value) || 1;
            if (delta > 0) {
                val++;
                const templateGroup = container.querySelector('.item-group');
                const newGroup = templateGroup.cloneNode(true);
                
                const mainItem = newGroup.querySelector('.main-item-input');
                const mainCatatan = newGroup.querySelector('.main-catatan-input');
                mainItem.value = ''; mainCatatan.value = '';
                mainItem.addEventListener('input', (e) => syncMainInputs(e.target, 'hidden-item'));
                mainCatatan.addEventListener('input', (e) => syncMainInputs(e.target, 'hidden-catatan'));
                
                const tContainer = newGroup.querySelector('.treatments-container');
                const tRows = tContainer.querySelectorAll('.treatment-row');
                for(let i = 1; i < tRows.length; i++) { tRows[i].remove(); }
                
                const firstRow = tContainer.querySelector('.treatment-row');
                firstRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                firstRow.querySelector('.treatment-select').innerHTML = '<option value="">- Pilih Kat. Dulu -</option>';
                firstRow.querySelectorAll('input').forEach(i => {
                    i.value = '';
                    // RESET Date ke tampilan Placeholder
                    if(i.name === 'tanggal_keluar[]') i.setAttribute('data-date', '');
                });
                
                const btnRemove = firstRow.querySelector('.btn-remove-treatment');
                if(btnRemove) btnRemove.classList.add('hidden');
                
                attachEventsToTreatmentRow(firstRow);
                newGroup.classList.add('animate-fade-in');
                container.appendChild(newGroup);
            } else {
                if (val > 1) {
                    val--;
                    if(container.lastElementChild) container.removeChild(container.lastElementChild);
                }
            }
            input.value = val;
        }

        let timeout = null;
        window.cekCustomer = function() {
            let noHp = document.getElementById('no_hp').value;
            const btnDaftar = document.getElementById('btn-daftar-member');
            if(btnDaftar) btnDaftar.classList.remove('hidden');
            document.getElementById('box-tipe-customer').classList.remove('hidden');
            document.getElementById('box-sumber-info').classList.remove('hidden');
            document.getElementById('box-point').classList.add('hidden');
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                if(noHp.length >= 4) { 
                    $.ajax({
                        url: "{{ route('check.customer') }}", type: "POST", data: { _token: "{{ csrf_token() }}", no_hp: noHp },
                        success: function (response) {
                            if (response.found) {
                                document.getElementById('nama_customer').value = response.nama;
                                document.getElementById('display_tipe_customer').value = response.tipe;
                                document.getElementById('tipe_customer_input').value = response.tipe;
                                document.getElementById('box-tipe-customer').classList.add('hidden');
                                document.getElementById('box-sumber-info').classList.add('hidden');
                                if(response.tipe === 'Member') {
                                    document.getElementById('box-point').classList.remove('hidden'); 
                                    document.getElementById('member_id').value = response.member_id;
                                    document.getElementById('is_registered_member').value = 1;
                                    if(btnDaftar) btnDaftar.classList.add('hidden');
                                    document.getElementById('badge-status').innerText = 'Member';
                                    document.getElementById('badge-status').className = 'text-xl font-bold text-pink-500';
                                    document.getElementById('poin-text').innerText = (response.poin || 0) + '/' + (response.target || 8);
                                    const btnClaim = document.getElementById('btn-claim');
                                    if (response.bisa_claim) btnClaim.classList.remove('hidden');
                                    else btnClaim.classList.add('hidden');
                                } else {
                                    document.getElementById('is_registered_member').value = 0;
                                    document.getElementById('badge-status').innerText = 'Repeat Order';
                                    document.getElementById('badge-status').className = 'text-xl font-bold text-green-600';
                                }
                            } else {
                                document.getElementById('nama_customer').value = '';
                                document.getElementById('display_tipe_customer').value = 'New Customer';
                                document.getElementById('tipe_customer_input').value = 'New Customer';
                                const badge = document.getElementById('badge-status');
                                badge.innerText = 'Baru';
                                badge.className = 'text-xl font-bold text-blue-500';
                            }
                        }
                    });
                }
            }, 500);
        }

        window.openMemberModal = function() { document.getElementById('memberModal').classList.remove('hidden'); }
        window.closeMemberModal = function() { document.getElementById('memberModal').classList.add('hidden'); }
        
        window.claimReward = function() {
            let memberId = document.getElementById('member_id').value;
            let currentPoin = document.getElementById('poin-text').innerText.split('/')[0];
            document.getElementById('display-poin-modal').innerText = currentPoin;
            document.getElementById('modal-claim-reward').classList.remove('hidden');
        }
        window.closeClaimModal = function() { document.getElementById('modal-claim-reward').classList.add('hidden'); }

        window.submitClaimReward = function() {
            let memberId = document.getElementById('member_id').value;
            let selectedItem = document.querySelector('input[name="reward_item"]:checked').value;
            if(!confirm("Klaim reward?")) return;
            $.ajax({
                url: "{{ route('members.claim') }}", type: "POST",
                data: { _token: "{{ csrf_token() }}", member_id: memberId, reward_item: selectedItem },
                success: function(response) {
                    alert(response.message);
                    if(response.status === 'success') {
                        closeClaimModal();
                        document.getElementById('poin-text').innerText = response.sisa_poin + '/' + response.target;
                        if(response.sisa_poin < response.target) document.getElementById('btn-claim').classList.add('hidden');
                    }
                }
            });
        }

        window.submitMemberAjax = function(event) {
            event.preventDefault();
            let formData = new FormData(document.getElementById('formMemberAjax'));
            formData.append('_token', "{{ csrf_token() }}");
            $.ajax({
                url: "{{ route('members.store') }}", type: "POST", data: formData,
                contentType: false, processData: false,
                success: function(response) {
                    alert(response.message);
                    if(response.status === 'success') { closeMemberModal(); cekCustomer(); }
                }
            });
        }
    </script>

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* CSS MAGIC: Ubah tampilan default input date saat kosong (placeholder) */
        input[type="date"].date-placeholder {
            position: relative;
        }
        /* Jika belum ada data (data-date kosong), text aslinya di transparentkan */
        input[type="date"].date-placeholder:not([data-date]),
        input[type="date"].date-placeholder[data-date=""] {
            color: transparent;
        }
        /* Tampilkan placeholder buatan */
        input[type="date"].date-placeholder:not([data-date])::before,
        input[type="date"].date-placeholder[data-date=""]::before {
            content: "Tanggal";
            color: #9ca3af; /* Gray 400 */
            position: absolute;
            left: 0.5rem; /* Sesuai padding p-1.5 */
        }
    </style>
</x-app-layout>