<x-app-layout>
    {{-- Inisialisasi Alpine.js --}}
    <div x-data="{ 
        showPaymentModal: false, 
        paymentMethod: 'Tunai',   
        paymentStatus: 'Lunas',   
        totalPrice: 0, 
        cashAmount: 0,
        
        // Hitung Kembalian
        get change() {
            return Math.max(0, this.cashAmount - this.totalPrice);
        },
        
        // Format Rupiah
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        },

        // Hitung Total Harga (Logic Berjalan di Background)
        calculateTotal() {
            let total = 0;
            // 1. Hitung total item dari input harga
            document.querySelectorAll('input[name=\'harga[]\']').forEach(input => {
                let val = parseInt(input.value) || 0;
                total += val;
            });

            // 2. Cek apakah ada Diskon Reward (Dari Input Hidden)
            // Meskipun tidak tampil di layar, logic ini wajib ada agar nominal bayar benar
            let isClaim = document.getElementById('input_is_claim').value;
            let rewardType = document.getElementById('input_reward_type').value;
            let discount = 0;

            if (isClaim === '1' && rewardType.toLowerCase().includes('diskon')) {
                discount = 35000;
            }

            // 3. Update Total Akhir (Net)
            this.totalPrice = Math.max(0, total - discount);
            
            // Jika status Lunas, otomatis isi cashAmount dengan total
            if(this.paymentStatus === 'Lunas') {
                this.cashAmount = this.totalPrice;
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
            
            {{-- === HIDDEN INPUTS (LOGIKA UTAMA) === --}}
            <input type="hidden" name="tipe_customer" id="tipe_customer_input" value="{{ $status ?? 'Baru' }}">
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">
            
            {{-- Hidden Input untuk Reward (Diisi Javascript Modal) --}}
            <input type="hidden" name="is_claim" id="input_is_claim" value="0">
            <input type="hidden" name="reward_type" id="input_reward_type" value="">

            {{-- Hidden Input untuk Pembayaran (AlpineJS Bridge) --}}
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
                    
                    {{-- Kategori Treatment --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Kategori Treatment</label>
                        <input type="text" 
                               name="kategori_treatment[]" 
                               class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-800 placeholder-gray-500" 
                               placeholder="Ketik layanan...">
                    </div>

                    {{-- Tanggal Keluar --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-700">
                    </div>

                    {{-- Harga --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Harga</label>
                        {{-- oninput dihapus hitungTotalManual() agar tidak ada interaksi visual di index --}}
                        <input type="number" name="harga[]" class="input-harga w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="0">
                    </div>

                    {{-- Catatan --}}
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="catatan[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="-">
                    </div>
                </div>
            </div>

            {{-- BARIS 4: POINT & TIPE CUSTOMER --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                {{-- 1. POINT (Hanya Member) --}}
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-3 px-5 h-full w-full flex flex-col justify-center items-end md:col-start-5  {{ ($is_member ?? false) ? '' : 'hidden' }}">
                    
                    {{-- SAYA MENGHAPUS SUBTOTAL, DISKON, DAN REWARD DISINI AGAR BERSIH --}}
                    
                    <div class="text-right">
                        <label class="text-sm font-semibold text-gray-600 mb-1 block">Point Member</label>
                        <div class="flex items-center justify-end gap-3">
                            <span id="poin-text" class="text-gray-800 font-bold text-xl">{{ $poin ?? 0 }}/8</span>
                            @php
                                $poinSekarang = $customer->member->poin ?? 0;
                                $targetPoin = 8;
                            @endphp
                            <button type="button" id="btn-claim" onclick="bukaModalReward()" 
                                    class="bg-blue-600 text-white text-xs font-bold px-4 py-1.5 rounded-lg shadow hover:bg-blue-700 transition {{ $poinSekarang >= $targetPoin ? '' : 'hidden' }}">
                                Claim
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. TIPE CUSTOMER (Hidden untuk Member/Repeat) --}}
                <div id="box-tipe-customer" class="bg-[#E0E0E0] rounded-lg p-3 px-5 {{ ($status ?? 'New Customer') == 'New Customer' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" id="display_tipe_customer" value="{{ $status ?? 'Baru' }}" readonly 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                </div>
            </div>

            {{-- BARIS 5: SUMBER INFO (Hidden untuk Member/Repeat) --}}
            <div id="box-sumber-info" class="grid grid-cols-1 mb-12 {{ ($status ?? 'New Customer') == 'New Customer' ? '' : 'hidden' }}">
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

                {{-- Trigger AlpineJS calculateTotal() saat klik PROSES --}}
                <button type="button" 
                        x-on:click="calculateTotal(); showPaymentModal = true"
                        class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105">
                    PROSES PEMBAYARAN
                </button>
            </div>

            {{-- MODAL PAYMENT (ALPINE JS) --}}
            <div x-show="showPaymentModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60"
                 x-transition:enter="ease-out duration-300"
                 style="display: none;">

                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                     @click.away="showPaymentModal = false">
                    
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                        <h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3>
                        <button type="button" x-on:click="showPaymentModal = false" class="text-white font-bold text-2xl">&times;</button>
                    </div>

                    <div class="p-6">
                        {{-- Total Tagihan (INI AKAN MENAMPILKAN TOTAL BERSIH SETELAH DISKON) --}}
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan (Net)</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" x-text="formatRupiah(totalPrice)"></div>
                        </div>

                        {{-- 1. PILIH METODE --}}
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

                        {{-- 2. PILIH STATUS --}}
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Status Pembayaran</label>
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

                        {{-- 3. INPUT NOMINAL --}}
                        <div x-show="paymentStatus !== 'Lunas'" x-transition class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1" x-text="paymentStatus === 'DP' ? 'Nominal DP' : 'Uang Diterima'"></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span>
                                <input type="number" name="paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" x-model.number="cashAmount" placeholder="0">
                            </div>
                            
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200">
                                <span class="text-sm text-gray-500 font-bold">Kekurangan:</span>
                                <span class="font-black text-red-600 text-xl" x-text="formatRupiah(change)"></span>
                            </div>
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

    {{-- MODAL CLAIM REWARD (OFFLINE JS - TIDAK PAKAI FORM TAG) --}}
    <div id="modal-claim-reward" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in" onclick="event.stopPropagation()">
            
            {{-- Header --}}
            <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Klaim Reward</h3>
                <button type="button" onclick="closeClaimModal()" class="text-white font-bold text-2xl hover:text-gray-200">&times;</button>
            </div>

            <div class="p-6">
                {{-- Info Poin --}}
                <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                    <span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span>
                    <div class="text-3xl font-black text-[#3b66ff] mt-1">
                        <span id="display-poin-modal">0</span> pts
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-bold">Tukar 8 Poin dengan:</p>
                </div>

                {{-- DIV PENGGANTI FORM (Hanya Wrapper Biasa) --}}
                <div id="divClaimReward">
                    {{-- Pilihan Reward --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Reward</label>
                        <div class="grid grid-cols-2 gap-3">
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="diskon" class="peer sr-only" checked>
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Diskon</span>
                                    <span class="text-xs opacity-80 block mt-1">Potongan Harga</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="Gratis Parfum" class="peer sr-only">
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Gratis Parfum</span>
                                    <span class="text-xs opacity-80 block mt-1">Merchandise</span>
                                </div>
                            </label>

                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                        {{-- Tombol ini menjalankan JS submitClaimReward --}}
                        <button type="button" onclick="submitClaimReward()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg hover:bg-blue-700">Klaim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.member-modal')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // --- 1. LOGIKA MODAL CLAIM REWARD (PERBAIKAN UTAMA: TANPA VISUAL DI INDEX) ---
        window.bukaModalReward = function() {
            let textPoin = document.getElementById('poin-text').innerText; 
            let currentPoin = parseInt(textPoin.split('/')[0]) || 0;
            
            document.getElementById('display-poin-modal').innerText = currentPoin;
            document.getElementById('modal-claim-reward').classList.remove('hidden');
        }

        window.closeClaimModal = function() {
            document.getElementById('modal-claim-reward').classList.add('hidden');
        }

        window.submitClaimReward = function() {
            // Ambil pilihan reward
            let selectedRadio = document.querySelector('input[name="reward_item"]:checked');
            if(!selectedRadio) {
                alert("Pilih reward terlebih dahulu");
                return;
            }
            let selectedItem = selectedRadio.value;

            // 1. Simpan ke input hidden agar dikirim saat form utama disubmit
            document.getElementById('input_is_claim').value = "1";
            document.getElementById('input_reward_type').value = selectedItem;

            // 2. Tutup Modal
            closeClaimModal();

            // 3. Beri Pesan Sukses (Tanpa merubah tampilan Index)
            alert("Reward '" + selectedItem + "' berhasil dipilih!\nDiskon/Item akan otomatis tercetak di Invoice.");
        }

        // --- 2. LOGIKA CUSTOMER & MEMBER ---
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

                                document.getElementById('display_tipe_customer').value = response.tipe;
                                document.getElementById('tipe_customer_input').value = response.tipe;

                                document.getElementById('box-tipe-customer').classList.add('hidden');
                                document.getElementById('box-sumber-info').classList.add('hidden');

                                if(response.tipe === 'Member') {
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
                                    document.getElementById('box-point').classList.add('hidden'); 
                                    document.getElementById('is_registered_member').value = 0;
                                    document.getElementById('btn-daftar-member').classList.remove('hidden');
                                    
                                    badge.innerText = 'Repeat Order';
                                    badge.className = 'text-xl font-bold text-green-600';
                                }
                            } else {
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

                                document.getElementById('box-tipe-customer').classList.remove('hidden');
                                document.getElementById('box-sumber-info').classList.remove('hidden');
                            }
                        }
                    });
                }
            }, 500);
        }

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

        // --- 3. LOGIKA MODAL DAFTAR MEMBER ---
        window.openMemberModal = function() {
            const mainNama = document.querySelector('input[name="nama_customer"]').value;
            const mainNoHp = document.getElementById('no_hp').value;

            const modalNama = document.getElementById('modalNama');
            const modalNoHp = document.querySelector('#formMemberAjax input[name="no_hp"]'); 

            if(modalNama) modalNama.value = mainNama;
            if(modalNoHp) modalNoHp.value = mainNoHp; 

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
    </script>

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>