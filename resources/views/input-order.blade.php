<x-app-layout>
    {{-- Inisialisasi Alpine.js --}}
    <div x-data="{ 
        showPaymentModal: false, 
        paymentMethod: 'Tunai',   {{-- Default Metode --}}
        paymentStatus: 'Lunas',   {{-- Default Status --}}
        totalPrice: 0, 
        cashAmount: 0,
        
        // Hitung Kembalian
        get change() {
            return Math.max(0, this.totalPrice - this.cashAmount);
        },
        
        // Format Rupiah
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        },

        // Hitung Total Harga
        calculateTotal() {
            let total = 0;
            document.querySelectorAll('input[name=\'harga[]\']').forEach(input => {
                let val = parseInt(input.value) || 0;
                total += val;
            });
            this.totalPrice = total;
            
            // Jika status Lunas, otomatis isi cashAmount dengan total (opsional, biar cepat)
            if(this.paymentStatus === 'Lunas') {
                this.cashAmount = total;
            }
        }
    }" class="min-h-screen bg-white p-4 md:p-8">

        {{-- HEADER --}}
        <div class="flex items-center gap-4 mb-10">
            <h1 class="text-4xl font-bold text-[#7FB3D5]">Input Order</h1>
            <span id="badge-status" class="text-xl font-bold {{ $color ?? 'text-blue-500' }}">
                {{ $status ?? 'Baru' }}
            </span>
        </div>

        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf
            
            {{-- HIDDEN INPUTS --}}
            <input type="hidden" name="tipe_customer" id="tipe_customer_input" value="{{ $status ?? 'Baru' }}">
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">

            {{-- INPUT HIDDEN UNTUK PEMBAYARAN (Dikirim ke Controller) --}}
            <input type="hidden" name="metode_pembayaran" x-model="paymentMethod">
            <input type="hidden" name="status_pembayaran" x-model="paymentStatus">

            {{-- BARIS 1: NAMA & NO HP --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Customer</label>
                    <input type="text" name="nama_customer" id="nama_customer"
                           value="{{ $customer->nama ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold placeholder-gray-400" 
                           placeholder="Masukkan nama">
                </div>
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex items-center relative">
                    <div class="border-r border-gray-400 pr-4 mr-4 h-full flex items-center">
                        <label class="text-sm font-semibold text-gray-600 whitespace-nowrap">No HP</label>
                    </div>
                    <input type="number" name="no_hp" id="no_hp" onkeyup="cekCustomer()"
                           value="{{ $no_hp ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold"
                           placeholder="08...">
                </div>
            </div>

            {{-- BARIS 2: JUMLAH & CS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex justify-between items-center">
                    <label class="text-sm font-semibold text-gray-600">Jumlah</label>
                    <div class="flex items-center gap-6">
                        <button type="button" onclick="adjustJumlah(-1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none">&minus;</button>
                        <input type="number" id="inputJumlah" name="jumlah_total" value="1" readonly 
                               class="w-12 bg-transparent border-none text-center font-bold text-lg p-0 focus:ring-0">
                        <button type="button" onclick="adjustJumlah(1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none">&plus;</button>
                    </div>
                </div>
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex flex-col justify-center">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Cs</label>
                    <select name="cs" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                        <option value="Admin 1">Admin 1</option>
                        <option value="Admin 2">Admin 2</option>
                    </select>
                </div>
            </div>

            {{-- BARIS 3: ITEM BARANG --}}
            <div id="itemsContainer" class="space-y-4 mb-6">
                <div class="item-row grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Item Name --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Item</label>
                        <input type="text" name="item[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="Nama Barang">
                    </div>
                    
                    {{-- Kategori Treatment (UPDATED: Click to Open Modal) --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Kategori Treatment</label>
                        <input type="text" 
                               name="kategori_treatment[]" 
                               class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-700 cursor-pointer placeholder-gray-500" 
                               placeholder="Pilih..."
                               readonly 
                               onclick="openTreatmentModal(this)">
                    </div>

                    {{-- Tanggal Keluar --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-700">
                    </div>

                    {{-- Harga --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Harga</label>
                        <input type="number" name="harga[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="0">
                    </div>

                    {{-- Catatan --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="catatan[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="-">
                    </div>
                </div>
            </div>

            {{-- ROW 4: PEMBAYARAN, POINT, TIPE --}}
            {{-- BARIS 4: PEMBAYARAN, POINT, TIPE --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                
                {{-- 1. PEMBAYARAN (BUTTON PEMICU POPUP) --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 cursor-pointer hover:bg-gray-300 transition"
                     x-on:click="calculateTotal(); showPaymentModal = true">
                    <label class="block text-sm font-semibold text-gray-600 mb-1 cursor-pointer">Pembayaran</label>
                    <div class="flex justify-between items-center w-full">
                        {{-- Tampilkan Metode & Status (contoh: Tunai - Lunas) --}}
                        <span class="text-gray-800 font-bold" x-text="paymentMethod + ' (' + paymentStatus + ')'"></span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                {{-- POINT --}}
                {{-- 2. POINT --}}
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex items-center justify-between {{ ($is_member ?? false) ? '' : 'hidden' }}">
                    <label class="text-sm font-semibold text-gray-600">Point</label>
                    <div class="flex items-center gap-3">
                        <span id="poin-text" class="text-gray-800 font-bold text-lg">{{ $poin ?? 0 }}/8</span>
                        @php
                            $poinSekarang = $customer->member->poin ?? 0;
                            $targetPoin = 8;
                        @endphp
                        <button type="button" id="btn-claim" onclick="claimReward()" 
                                class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow hover:bg-blue-700 transition {{ $poinSekarang >= $targetPoin ? '' : 'hidden' }}">
                            Claim
                        </button>
                    </div>
                </div>

                {{-- 3. TIPE CUSTOMER --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" id="display_tipe_customer" value="{{ $status ?? 'Baru' }}" readonly 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                </div>
            </div>

            {{-- BARIS 5: SUMBER INFO --}}
            <div class="grid grid-cols-1 mb-12">
                <div class="md:w-1/2 bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
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

                <button type="button" 
                        x-on:click="calculateTotal(); showPaymentModal = true"
                        class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105">
                    PROSES PEMBAYARAN
                </button>
            </div>

            {{-- MODAL POPUP (LAYOUT BARU) --}}
            <div x-show="showPaymentModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60"
                 x-transition:enter="ease-out duration-300"
                 style="display: none;">

                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                     @click.away="showPaymentModal = false">
                    
                    {{-- Header --}}
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3>
                        <button type="button" x-on:click="showPaymentModal = false" class="text-white font-bold text-2xl">&times;</button>
                    </div>

                    <div class="p-6">
                        {{-- Total Tagihan --}}
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" x-text="formatRupiah(totalPrice)"></div>
                        </div>

                        {{-- 1. PILIH METODE (Tunai/Transfer/QRIS) --}}
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Metode</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" value="Tunai" x-model="paymentMethod" class="peer sr-only">
                                    <div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 font-bold text-sm">Tunai</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" value="Transfer" x-model="paymentMethod" class="peer sr-only">
                                    <div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 font-bold text-sm">Transfer</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" value="QRIS" x-model="paymentMethod" class="peer sr-only">
                                    <div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 font-bold text-sm">QRIS</div>
                                </label>
                            </div>
                        </div>

                        {{-- 2. PILIH STATUS (Lunas/DP/Belum Lunas) --}}
                        <div class="mb-4">
    <label class="block text-sm font-bold text-gray-700 mb-2">Status Pembayaran</label>
    
    {{-- UBAH grid-cols-3 MENJADI grid-cols-2 --}}
    <div class="grid grid-cols-2 gap-2">
        
        <label class="cursor-pointer">
            <input type="radio" value="Lunas" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = totalPrice">
            <div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 hover:bg-gray-50 font-bold text-sm">Lunas</div>
        </label>
        
        <label class="cursor-pointer">
            <input type="radio" value="DP" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = 0">
            <div class="p-2 text-center border-2 rounded-lg peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:border-yellow-500 hover:bg-gray-50 font-bold text-sm">DP</div>
        </label>

    </div>
</div>

                        {{-- 3. INPUT NOMINAL (Muncul jika Lunas atau DP) --}}
                        <div x-show="paymentStatus !== 'Lunas'" x-transition class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1" x-text="paymentStatus === 'DP' ? 'Nominal DP' : 'Uang Diterima'"></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span>
                                <input type="number" name="paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" x-model.number="cashAmount" placeholder="0">
                            </div>
                            
                            {{-- Kembalian (Hanya relevan jika Lunas & Tunai, tapi kita tampilkan saja untuk info) --}}
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200">
                                <span class="text-sm text-gray-500 font-bold">Kekurangan:</span>
                                <span class="font-black text-red-600 text-xl" x-text="formatRupiah(change)"></span>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex justify-end space-x-3">
                            <button type="button" x-on:click="showPaymentModal = false" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    {{-- MODAL TREATMENT CATEGORY (NEW) --}}
    <div id="modal-treatment" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                {{-- Header Modal --}}
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border-b">
                    <div class="sm:flex sm:items-start justify-between">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Pilih Kategori Treatment</h3>
                        <button type="button" onclick="closeTreatmentModal()" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    {{-- Search Bar --}}
                    <div class="mt-4">
                        <input type="text" id="search-treatment" onkeyup="filterTreatments()" placeholder="Cari layanan (misal: Deep Clean)..." 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>
                </div>
                {{-- Body Modal (Grid List) --}}
                <div class="bg-gray-50 px-4 py-3 sm:px-6 max-h-96 overflow-y-auto">
                    <div id="treatment-list" class="grid grid-cols-2 md:grid-cols-3 gap-3 w-full">
                        {{-- Items injected by JS --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.member-modal')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // --- 1. SETUP DATA TREATMENT ---
    const rawTreatments = @json($treatments ?? []);
    
    // Mapping data agar sesuai format JS
    const treatments = rawTreatments.map(item => {
        return {
            name: item.nama_treatment, 
            price: item.harga
        };
    });

    let activeTreatmentInput = null; 

    // Saat halaman selesai di-load
    document.addEventListener("DOMContentLoaded", () => {
        renderTreatments(treatments);
    });

    // --- 2. LOGIKA MODAL TREATMENT (Vanilla JS) ---
    function renderTreatments(data) {
        const listContainer = document.getElementById('treatment-list');
        listContainer.innerHTML = ''; 

        if (data.length === 0) {
            listContainer.innerHTML = '<p class="p-4 text-gray-500 italic col-span-2 text-center">Tidak ada data treatment.</p>';
            return;
        }

        data.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'flex justify-between items-start w-full px-4 py-3 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition shadow-sm text-sm';
            
            let formattedPrice = new Intl.NumberFormat('id-ID').format(item.price);

            btn.innerHTML = `
                <span class="font-medium text-gray-800 whitespace-normal text-left flex-1 pr-2">${item.name}</span>
                <span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-1 rounded whitespace-nowrap">Rp ${formattedPrice}</span>
            `;
            
            btn.onclick = function() {
                selectTreatment(item);
            };
            listContainer.appendChild(btn);
        });
    }

    // Fungsi Buka Modal Treatment
    window.openTreatmentModal = function(element) {
        activeTreatmentInput = element; 
        document.getElementById('modal-treatment').classList.remove('hidden');
        document.getElementById('search-treatment').value = ''; 
        renderTreatments(treatments); 
        setTimeout(() => {
            document.getElementById('search-treatment').focus();
        }, 100);
    }

    // Fungsi Tutup Modal Treatment
    window.closeTreatmentModal = function() {
        document.getElementById('modal-treatment').classList.add('hidden');
        activeTreatmentInput = null;
    }

    // Fungsi Pilih Treatment
    function selectTreatment(item) {
        if (activeTreatmentInput) {
            activeTreatmentInput.value = item.name; 
            
            // Otomatis isi Harga di baris yang sama
            const row = activeTreatmentInput.closest('.item-row');
            const priceInput = row.querySelector('input[name="harga[]"]');
            
            if(priceInput) {
                priceInput.value = item.price;
                // Trigger event change agar AlpineJS (jika ada) atau hitungan total terupdate
                priceInput.dispatchEvent(new Event('input')); 
            }
        }
        closeTreatmentModal();
    }

    // Fungsi Search Treatment
    window.filterTreatments = function() {
        const keyword = document.getElementById('search-treatment').value.toLowerCase();
        const filtered = treatments.filter(t => t.name.toLowerCase().includes(keyword));
        renderTreatments(filtered);
    }

    // --- 3. LOGIKA CUSTOMER & MEMBER (jQuery) ---
    let timeout = null;

    window.cekCustomer = function() {
        let noHp = document.getElementById('no_hp').value;
        document.getElementById('btn-daftar-member').classList.remove('hidden');

        clearTimeout(timeout);
        timeout = setTimeout(function () {
            if(noHp.length >= 4) { 
                $.ajax({
                    url: "{{ route('check.customer') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", no_hp: noHp },
                    success: function (response) {
                        if (response.found) {
                            document.getElementById('nama_customer').value = response.nama;
                            const badge = document.getElementById('badge-status');

                            // Set Hidden Inputs
                            document.getElementById('display_tipe_customer').value = response.tipe;
                            document.getElementById('tipe_customer_input').value = response.tipe;

                            if(response.tipe === 'Member') {
                                // Logic Member
                                document.getElementById('box-point').classList.remove('hidden'); 
                                document.getElementById('member_id').value = response.member_id;
                                document.getElementById('is_registered_member').value = 1;
                                document.getElementById('btn-daftar-member').classList.add('hidden');
                                
                                badge.innerText = 'Member';
                                badge.className = 'text-xl font-bold text-pink-500';
                                
                                document.getElementById('poin-text').innerText = (response.poin || 0) + '/' + (response.target || 8);
                                
                                const btnClaim = document.getElementById('btn-claim');
                                if (response.bisa_claim) btnClaim.classList.remove('hidden');
                                else btnClaim.classList.add('hidden');

                            } else {
                                // Logic Regular / Repeat
                                document.getElementById('box-point').classList.add('hidden'); 
                                document.getElementById('is_registered_member').value = 0;
                                document.getElementById('btn-daftar-member').classList.remove('hidden');
                                
                                badge.innerText = 'Repeat Order';
                                badge.className = 'text-xl font-bold text-green-600';
                            }
                        } else {
                            // Logic New Customer
                            document.getElementById('box-point').classList.add('hidden'); 
                            document.getElementById('nama_customer').value = '';
                            document.getElementById('display_tipe_customer').value = 'New Customer';
                            document.getElementById('tipe_customer_input').value = 'New Customer';
                            
                            const badge = document.getElementById('badge-status');
                            badge.innerText = 'Baru';
                            badge.className = 'text-xl font-bold text-blue-500';
                            
                            document.getElementById('is_registered_member').value = 0;
                            document.getElementById('btn-claim').classList.add('hidden');
                            document.getElementById('btn-daftar-member').classList.remove('hidden');
                        }
                    }
                });
            }
        }, 500);
    }

    // Fungsi Tambah/Kurang Baris Item
    window.adjustJumlah = function(delta) {
        const input = document.getElementById('inputJumlah');
        const container = document.getElementById('itemsContainer');
        
        let currentValue = parseInt(input.value);
        let newValue = currentValue + delta;

        if (newValue < 1) return;

        input.value = newValue;

        if (delta > 0) {
            const firstRow = container.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.classList.add('animate-fade-in'); 
            container.appendChild(newRow);
        } else {
            const rows = container.querySelectorAll('.item-row');
            if (rows.length > 1) {
                container.removeChild(rows[rows.length - 1]);
            }
        }
    }

    // --- 4. LOGIKA MODAL MEMBER ---
    window.openMemberModal = function() {
        const mainNama = document.querySelector('input[name="nama_customer"]').value;
        const mainNoHp = document.getElementById('no_hp').value;

        const modalNama = document.getElementById('modalNama');
        const modalNoHp = document.querySelector('#formMemberAjax input[name="no_hp"]'); 

        if(modalNama) modalNama.value = mainNama;
        if(modalNoHp) modalNoHp.value = mainNoHp; 

        // Hitung total belanja sementara untuk preview poin
        let totalBelanja = 0;
        document.querySelectorAll('input[name="harga[]"]').forEach(input => {
            let val = parseInt(input.value) || 0;
            totalBelanja += val;
        });

        let poinDidapat = Math.floor(totalBelanja / 50000);

        if(document.getElementById('modalTotalDisplay')) {
            document.getElementById('modalTotalDisplay').value = "Rp " + new Intl.NumberFormat('id-ID').format(totalBelanja);
            document.getElementById('modalTotalValue').value = totalBelanja;
            document.getElementById('modalPoin').value = poinDidapat;
        }

        const modal = document.getElementById('memberModal');
        const content = document.getElementById('modalContent');
        if(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }
    }

    window.closeMemberModal = function() {
        const modal = document.getElementById('memberModal');
        const content = document.getElementById('modalContent');
        
        if(content) {
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
        }
        setTimeout(() => { 
            if(modal) modal.classList.add('hidden'); 
        }, 300);
    }

    window.submitMemberAjax = function(event) {
        event.preventDefault(); 
        let form = document.getElementById('formMemberAjax');
        let formData = new FormData(form);
        formData.append('_token', "{{ csrf_token() }}");

        $.ajax({
            url: "{{ route('members.store') }}", 
            type: "POST",
            data: formData,
            contentType: false, 
            processData: false, 
            success: function(response) {
                if (response.status === 'success') {
                    closeMemberModal();
                    alert(response.message);
                    cekCustomer(); // Refresh data customer
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan sistem.');
            }
        });
    }

    window.claimReward = function() {
        let memberId = document.getElementById('member_id').value;
        if (!memberId) return alert("ID Member tidak ditemukan.");
        if (!confirm("Klaim reward? Poin akan dikurangi.")) return;

        $.ajax({
            url: "{{ route('members.claim') }}", 
            type: "POST",
            data: { _token: "{{ csrf_token() }}", member_id: memberId },
            success: function(response) {
                alert(response.message);
                if (response.status === 'success') {
                    document.getElementById('poin-text').innerText = response.sisa_poin + '/' + response.target;
                    // Hide button if points not enough
                    if(response.sisa_poin < response.target) {
                        document.getElementById('btn-claim').classList.add('hidden');
                    }
                }
            }
        });
    }
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>
    <style>.animate-fade-in { animation: fadeIn 0.3s ease-in-out; } @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }</style>
</x-app-layout>