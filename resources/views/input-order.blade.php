<x-app-layout>
    {{-- 1. STYLE: Hapus @media print, kita pakai JS untuk cetak --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');
        .invoice-font { font-family: 'Courier Prime', monospace; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    {{-- 2. INISIALISASI DATA ALPINE --}}
    <div id="main-app" 
         x-data="{ 
            showPaymentModal: false, 
            showInvoiceModal: false, 
            paymentMethod: 'Tunai',   
            paymentStatus: 'Lunas',   
            totalPrice: 0, 
            cashAmount: 0,
            discountReward: 0,
            claimType: '',
            
            get finalBill() { return Math.max(0, this.totalPrice - this.discountReward); },
            get change() { return Math.max(0, this.cashAmount - this.finalBill); },
            
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
                if(this.paymentStatus === 'Lunas') { this.cashAmount = this.finalBill; }
            }
        }" 
        @set-reward.window="claimType = $event.detail.type; discountReward = $event.detail.discount; calculateTotal();"
        @open-invoice.window="showPaymentModal = false; showInvoiceModal = true;"
        class="min-h-screen bg-white p-4 md:p-8">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center gap-3 md:gap-4 mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5]">Input Order</h1>
            <span id="badge-status" class="text-sm md:text-xl font-bold px-3 py-1 rounded-full border {{ $color ?? 'text-blue-600 bg-blue-100 border-blue-200' }}">
                {{ $status ?? 'Baru' }}
            </span>
        </div>

        {{-- FORM UTAMA --}}
        <form id="orderForm" method="POST" onsubmit="event.preventDefault();">
            @csrf
            
            <input type="hidden" name="tipe_customer" id="tipe_customer_input" value="{{ $status ?? 'Baru' }}">
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">
            <input type="hidden" name="metode_pembayaran" x-model="paymentMethod">
            <input type="hidden" name="status_pembayaran" x-model="paymentStatus">
            <input type="hidden" name="claim_type" x-model="claimType">

            {{-- INPUT CUSTOMER --}}
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
                    <input type="number" name="no_hp" id="no_hp" onkeyup="window.cekCustomer()"
                           value="{{ $no_hp ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold"
                           placeholder="08...">
                </div>
            </div>

            {{-- ITEM & CS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex justify-between items-center hover:shadow-md transition">
                    <label class="text-sm font-semibold text-gray-600">Jumlah Sepatu</label>
                    <div class="flex items-center gap-6">
                        <button type="button" onclick="window.adjustJumlah(-1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none transition transform hover:scale-110">&minus;</button>
                        <input type="number" id="inputJumlah" name="jumlah_total" value="1" readonly 
                               class="w-12 bg-transparent border-none text-center font-bold text-lg p-0 focus:ring-0">
                        <button type="button" onclick="window.adjustJumlah(1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none transition transform hover:scale-110">&plus;</button>
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

            {{-- CONTAINER ITEM --}}
            <div id="itemsContainer" class="space-y-6 mb-6">
                <div class="item-group bg-[#E0E0E0] p-4 rounded-xl shadow-sm relative group animate-fade-in hover:shadow-md transition">
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

                    <div class="treatments-container space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wide">Daftar Treatment</label>
                        <div class="treatment-row grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-3 rounded-lg border border-gray-300 relative shadow-sm">
                            <input type="hidden" name="item[]" class="hidden-item">
                            <input type="hidden" name="catatan[]" class="hidden-catatan">
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Kategori</label>
                                <select class="category-select w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500" onchange="filterTreatments(this)">
                                    <option value="">- Pilih -</option>
                                    @foreach($treatments->pluck('kategori')->unique()->values() as $kategori)
                                        @if(!empty($kategori))
                                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Layanan</label>
                                <select name="kategori_treatment[]" class="treatment-select w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500">
                                    <option value="">- Pilih Kat. Dulu -</option>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Estimasi</label>
                                <input type="text" name="tanggal_keluar[]" class="w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 focus:ring-blue-500" placeholder="Pilih Tanggal" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-xs font-bold text-gray-500">Rp</span>
                                    <input type="text" name="harga[]" class="harga-input w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 pl-7 text-xs font-bold text-gray-800 focus:ring-blue-500" placeholder="0">
                                </div>
                            </div>
                            <button type="button" onclick="window.removeTreatment(this)" class="btn-remove-treatment absolute -top-2 -right-2 text-red-500 hover:text-red-700 bg-white rounded-full p-0.5 shadow-md hidden group-hover:block z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 text-right">
                        <button type="button" onclick="window.addTreatmentRow(this)" class="inline-flex items-center px-3 py-1 bg-white border border-gray-400 text-gray-700 text-xs font-bold rounded-full hover:bg-gray-100 transition shadow-sm">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Treatment Lain
                        </button>
                    </div>
                </div>
            </div>

            {{-- BOX INFO --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-2 pl-24 h-full w-full flex flex-col justify-center items-center md:col-start-5 {{ ($is_member ?? false) ? '' : 'hidden' }}">
                    <label class=" text-sm font-semibold text-gray-600 mb-1 "> Point </label>
                    <div class="flex items-center gap-3">
                        <span id="poin-text" class="text-gray-800 font-bold text-lg">{{ $poin ?? 0 }}/8</span>
                        <button type="button" id="btn-claim" onclick="window.claimReward()" 
                                class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow hover:bg-blue-700 transition {{ ($poin ?? 0) >= 8 ? '' : 'hidden' }}">
                            Claim
                        </button>
                        <template x-if="claimType">
                            <span class="text-[10px] bg-green-500 text-white px-2 py-1 rounded-full animate-pulse" x-text="claimType === 'diskon' ? 'Reward: Diskon 10rb' : 'Reward: Free Parfum'"></span>
                        </template>
                    </div>
                </div>
                <div id="box-tipe-customer" class="bg-[#E0E0E0] rounded-lg p-3 px-5 {{ ($status ?? 'New Customer') == 'New Customer' ? '' : 'hidden' }}">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" id="display_tipe_customer" value="{{ $status ?? 'Baru' }}" readonly class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                </div>
            </div>

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

            {{-- FOOTER --}}
            <div class="flex justify-end gap-4">
                <button type="button" id="btn-daftar-member" onclick="window.openMemberModal()" class="bg-[#3b66ff] text-white px-10 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition {{ ($is_member ?? false) ? 'hidden' : '' }}">MEMBER</button>
                <button type="button" x-on:click="calculateTotal(); showPaymentModal = true" class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105">PROSES PEMBAYARAN</button>
            </div>

            {{-- 3. MODAL PAYMENT (Diberi ID 'modal-payment' untuk akses manual) --}}
            <div id="modal-payment"
                 x-show="showPaymentModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60" 
                 style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.away="showPaymentModal = false">
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3><button type="button" x-on:click="showPaymentModal = false" class="text-white font-bold text-2xl">&times;</button></div>
                    <div class="p-6">
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" x-text="formatRupiah(finalBill)"></div>
                            <template x-if="discountReward > 0">
                                <p class="text-[10px] text-green-600 font-bold mt-1 italic">* Sudah dipotong Diskon Reward Rp 10.000</p>
                            </template>
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
                                <label class="cursor-pointer"><input type="radio" value="Lunas" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = finalBill"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white font-bold text-sm">Lunas</div></label>
                                <label class="cursor-pointer"><input type="radio" value="DP" x-model="paymentStatus" class="peer sr-only" x-on:click="cashAmount = 0"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-yellow-500 peer-checked:text-white font-bold text-sm">DP</div></label>
                            </div>
                        </div>
                        <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1" x-text="paymentStatus === 'DP' ? 'Nominal DP' : 'Uang Diterima'"></label>
                            <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="number" name="paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" x-model.number="cashAmount" placeholder="0"></div>
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200"><span class="text-sm text-gray-500 font-bold">Kembalian:</span><span class="font-black text-green-600 text-xl" x-text="formatRupiah(change)"></span></div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" x-on:click="showPaymentModal = false" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="button" onclick="window.submitOrder()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">PROSES & CETAK</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. MODAL INVOICE POPUP (Diberi ID 'modal-invoice' untuk akses manual) --}}
            <div id="modal-invoice"
                 x-show="showInvoiceModal" 
                 @click.self="window.location.href = '{{ route('pesanan.index') }}'"
                 class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-80" 
                 style="display: none;">
                
                <div class="bg-white p-0 rounded-lg shadow-2xl overflow-hidden max-w-sm w-full mx-auto relative">
                    
                    {{-- KONTEN NOTA (DIBERI ID UNTUK DIAMBIL JS) --}}
                    <div id="invoice-content" class="bg-white p-5 invoice-font text-xs leading-snug">
                        <div class="text-center mb-3">
                             <h2 class="text-lg font-bold tracking-widest uppercase mb-1">LOUWES CARE</h2>
                             <p class="font-bold text-[10px] text-gray-600">SHOE LAUNDRY & CARE</p>
                             <p class="text-[9px] mt-1 text-gray-500">Jl. Ringroad Timur No 9, Banguntapan, Bantul</p>
                             <p class="text-[9px] text-gray-500">WA: 081390154885</p>
                             <div class="border-b-2 border-dashed border-gray-800 my-2"></div>
                        </div>
                        <div class="flex justify-between items-end mb-2">
                            <div><p>CS: <span id="inv-cs"></span></p><p class="font-bold mt-1">CUSTOMER:</p><p id="inv-cust-name" class="uppercase font-bold text-sm"></p><p id="inv-cust-hp"></p></div>
                            <div class="text-right"><h3 class="text-xl font-bold mb-1">INVOICE</h3><p id="inv-no" class="font-bold"></p><p id="inv-date"></p></div>
                        </div>
                        <div class="border-y-2 border-dashed border-gray-800 py-2 mb-2">
                             <table class="w-full text-left">
                                 <thead>
                                     <tr>
                                         <th class="pb-1 w-4/12">ITEM</th>
                                         <th class="pb-1 w-3/12">LAYANAN</th>
                                         {{-- KOLOM BARU: ESTIMASI --}}
                                         <th class="pb-1 w-2/12 text-center">EST</th>
                                         <th class="pb-1 w-3/12 text-right">HARGA</th>
                                     </tr>
                                 </thead>
                                 <tbody id="inv-items-body"></tbody>
                             </table>
                        </div>
                        <div class="flex justify-end mb-3">
                            <table class="w-full"><tr><td class="text-right pr-4">Subtotal</td><td class="text-right font-bold" id="inv-subtotal"></td></tr><tr><td class="text-right pr-4">Diskon</td><td class="text-right" id="inv-discount"></td></tr><tr class="text-lg font-bold border-t border-dashed border-gray-400"><td class="text-right pr-4 pt-1">TOTAL</td><td class="text-right pt-1" id="inv-total"></td></tr><tr><td class="text-right pr-4 pt-1 text-[10px]" id="inv-status"></td><td class="text-right pt-1 text-[10px]" id="inv-method"></td></tr></table>
                        </div>
                        <div id="inv-claim-msg" class="text-center font-bold border border-black p-1 mb-2 hidden"></div>
                        <div class="text-[9px] text-center mt-4"><p class="mb-1 italic">"Jika deadline tapi belum dihubungi, mohon WA kami"</p><p class="font-bold mb-2">*Simpan nota ini sebagai bukti pengambilan</p><div class="text-left border-t border-gray-300 pt-2"><p class="font-bold underline">NB (Syarat & Ketentuan):</p><ul class="list-disc pl-3"><li>Pengambilan barang wajib menyertakan Nota asli.</li><li>Komplain maks 1x24 jam setelah ambil.</li><li>Barang tak diambil > 30 hari diluar tanggung jawab kami.</li></ul></div><p class="mt-3 font-bold text-sm">-- Terima Kasih --</p></div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="bg-gray-100 p-4 flex gap-2 no-print border-t">
                        <button type="button" onclick="window.shareWhatsapp()" class="flex-1 bg-green-500 text-white py-2 rounded font-bold hover:bg-green-600">Share WA</button>
                        {{-- Tombol Cetak Memanggil fungsi PrintInvoice --}}
                        <button type="button" onclick="window.printInvoice()" class="flex-1 bg-gray-800 text-white py-2 rounded font-bold hover:bg-black">Cetak</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('components.member-modal')
    <div id="modal-claim-reward" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in">
            <div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Pilih Reward</h3><button type="button" onclick="window.closeClaimModal()" class="text-white font-bold text-2xl">&times;</button></div>
            <div class="p-6">
                <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100"><span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span><div class="text-3xl font-black text-[#3b66ff] mt-1"><span id="display-poin-modal">0</span> pts</div></div>
                <div class="space-y-3 mb-6">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"><input type="radio" name="reward_option" value="diskon" class="mr-3 text-[#3b66ff]" checked><div><p class="font-bold text-sm text-gray-800">Diskon Tunai Rp 10.000</p></div></label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition"><input type="radio" name="reward_option" value="parfum" class="mr-3 text-[#3b66ff]"><div><p class="font-bold text-sm text-gray-800">Free Parfum (8 Poin)</p></div></label>
                </div>
                <div class="flex justify-end space-x-3"><button type="button" onclick="window.closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button><button type="button" onclick="window.applyReward()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">Terapkan</button></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const rawTreatments = @json($treatments ?? []);
        const rupiahFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 });

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.item-group').forEach(group => { group.querySelectorAll('.treatment-row').forEach(row => attachEventsToTreatmentRow(row)); });
            if(document.getElementById('no_hp').value.length >= 4) { window.cekCustomer(); }
        });

        // 1. SUBMIT ORDER
        window.submitOrder = function() {
            let formData = $('#orderForm').serialize();
            $.ajax({
                url: "{{ route('orders.store') }}", 
                type: "POST", 
                data: formData, 
                dataType: 'json', 
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    console.log("Success:", response);
                    if(response.status === 'success') {
                        try {
                            populateInvoice(response);
                        } catch (e) {
                            console.error("Error populating invoice:", e);
                        }
                        window.dispatchEvent(new CustomEvent('open-invoice'));
                        document.getElementById('modal-payment').style.display = 'none'; 
                        document.getElementById('modal-invoice').style.display = 'flex';
                    }
                },
                error: function(xhr) { 
                    alert("Gagal menyimpan: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText)); 
                }
            });
        }

        // 2. FUNGSI PRINT INVOICE (WINDOW BARU)
        window.printInvoice = function() {
            // Ambil HTML dari div invoice-content
            var content = document.getElementById('invoice-content').innerHTML;
            
            // Buka jendela baru
            var mywindow = window.open('', 'PRINT', 'height=600,width=400');

            mywindow.document.write('<html><head><title>Invoice</title>');
            // Inject Style Manual agar rapi
            mywindow.document.write(`
                <style>
                    body { font-family: 'Courier Prime', monospace; font-size: 12px; margin: 0; padding: 10px; color: #000; }
                    .text-center { text-align: center; } 
                    .text-right { text-align: right; }
                    .font-bold { font-weight: bold; } 
                    .uppercase { text-transform: uppercase; }
                    .italic { font-style: italic; } 
                    .hidden { display: none; }
                    
                    /* Table Layout */
                    table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
                    td, th { vertical-align: top; padding: 2px 0; }
                    
                    /* Lebar Kolom */
                    .w-4\\/12 { width: 35%; } 
                    .w-3\\/12 { width: 25%; } 
                    .w-2\\/12 { width: 15%; }
                    
                    /* Ukuran Text */
                    .text-\\[10px\\] { font-size: 10px; } 
                    .text-\\[9px\\] { font-size: 9px; }
                    
                    /* Borders */
                    .border-b-2 { border-bottom: 2px dashed #000; }
                    .border-y-2 { border-top: 2px dashed #000; border-bottom: 2px dashed #000; margin: 10px 0; padding: 10px 0; }
                    .border-t { border-top: 1px dashed #000; margin-top: 5px; padding-top: 5px; }
                    
                    ul { padding-left: 15px; margin: 5px 0; }
                </style>
            `);
            mywindow.document.write('</head><body>');
            mywindow.document.write(content);
            mywindow.document.write('</body></html>');

            mywindow.document.close(); 
            mywindow.focus(); 

            setTimeout(function() {
                mywindow.print();
                mywindow.close();
            }, 500);
        }

        // 3. ISI NOTA
        function populateInvoice(data) {
            let order = data.order; let cust = order.customer;
            $('#inv-cs').text(order.kasir || '-'); 
            $('#inv-cust-name').text(cust.nama || 'Guest');
            
            let hp = cust.no_hp || ''; 
            let last4 = hp.length > 4 ? hp.substring(hp.length - 4) : hp;
            $('#inv-cust-hp').text(hp.length > 4 ? hp.substring(0, hp.length - 4) + 'xxxx' + last4 : hp);
            
            $('#inv-no').text(order.no_invoice);
            let date = new Date(order.created_at); 
            $('#inv-date').text('Date: ' + date.getDate() + '/' + (date.getMonth()+1) + '/' + date.getFullYear());

            let rows = ''; let lastItemName = null;
            if(order.details) {
                order.details.forEach(item => {
                    let displayItem = '';
                    if (item.nama_barang !== lastItemName) {
                        let note = item.catatan ? `<br><span class="italic text-[9px] text-gray-500">(${item.catatan})</span>` : '';
                        displayItem = `<span class="font-bold">${item.nama_barang}</span>${note}`;
                        lastItemName = item.nama_barang;
                    } else { displayItem = ''; }

                    // Format Tanggal Estimasi (dd/mm)
                    let estDate = item.estimasi_keluar ? new Date(item.estimasi_keluar) : null;
                    let estStr = estDate ? `${estDate.getDate()}/${estDate.getMonth()+1}` : '-';

                    rows += `
                        <tr>
                            <td class="align-top border-b border-gray-100 py-1 pr-1">${displayItem}</td>
                            <td class="align-top border-b border-gray-100 py-1 text-[10px]">${item.layanan}</td>
                            <td class="align-top border-b border-gray-100 py-1 text-center text-[10px]">${estStr}</td>
                            <td class="align-top border-b border-gray-100 py-1 text-right">${rupiahFormatter.format(item.harga)}</td>
                        </tr>
                    `;
                });
            }
            $('#inv-items-body').html(rows);

            $('#inv-subtotal').text('Rp ' + rupiahFormatter.format(data.original_total));
            $('#inv-discount').text('- Rp ' + rupiahFormatter.format(data.discount_amount));
            $('#inv-total').text('Rp ' + rupiahFormatter.format(order.total_harga));
            $('#inv-status').text(order.status_pembayaran ? order.status_pembayaran.toUpperCase() : '-');
            $('#inv-method').text('via ' + (order.metode_pembayaran || '-'));

            if(data.discount_amount > 0) { $('#inv-claim-msg').text('*** DISKON POINT DIGUNAKAN ***').removeClass('hidden'); }
            else if (order.catatan && order.catatan.includes('FREE PARFUM')) { $('#inv-claim-msg').text('*** FREE PARFUM CLAIMED ***').removeClass('hidden'); }
            else { $('#inv-claim-msg').addClass('hidden'); }
        }

        window.shareWhatsapp = function() {
            let no = $('#inv-no').text(); let total = $('#inv-total').text(); let name = $('#inv-cust-name').text();
            let text = `Halo Kak ${name}, Terima kasih telah mempercayakan sepatu kakak di Louwes Care. \nNo Nota: ${no} \nTotal: ${total}. \n\nSimpan pesan ini sebagai bukti pengambilan ya kak!`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        // 3. LOGIKA LAINNYA
        let timeout = null;
        window.cekCustomer = function() {
            let noHp = document.getElementById('no_hp').value; let btnDaftar = document.getElementById('btn-daftar-member');
            if(noHp.length < 4) { resetToDefault(); return; }
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                $.post("{{ route('check.customer') }}", { _token: "{{ csrf_token() }}", no_hp: noHp }, function(response) {
                    if (response.found) {
                        $('#nama_customer').val(response.nama);
                        let badgeColor = response.tipe === 'Member' ? 'text-pink-600 bg-pink-100 border-pink-200' : 'text-green-600 bg-green-100 border-green-200';
                        $('#badge-status').text(response.tipe).attr('class', `text-xl font-bold px-3 py-1 rounded-full border ${badgeColor}`);
                        $('#tipe_customer_input').val(response.tipe); $('#box-tipe-customer').addClass('hidden'); 
                        if(response.tipe === 'Member') {
                            $('#box-point').removeClass('hidden'); $('#member_id').val(response.member_id); $('#is_registered_member').val(1); $('#box-sumber-info').addClass('hidden'); if(btnDaftar) btnDaftar.classList.add('hidden');
                            let poin = parseInt(response.poin) || 0; $('#poin-text').text(`${poin}/8`);
                            if (poin >= 8) { $('#btn-claim').removeClass('hidden'); } else { $('#btn-claim').addClass('hidden'); }
                        } else { resetMemberUI(); $('#badge-status').text('Repeat Order'); }
                    } else { resetToDefault(); }
                });
            }, 500);
        }
        function resetToDefault() { $('#badge-status').text('Baru').attr('class', 'text-xl font-bold px-3 py-1 rounded-full border text-blue-600 bg-blue-100 border-blue-200'); $('#tipe_customer_input').val('Baru'); $('#box-tipe-customer').removeClass('hidden'); $('#box-sumber-info').removeClass('hidden'); resetMemberUI(); }
        function resetMemberUI() { $('#box-point').addClass('hidden'); $('#btn-claim').addClass('hidden'); $('#member_id').val(''); $('#is_registered_member').val(0); let btnDaftar = document.getElementById('btn-daftar-member'); if(btnDaftar) btnDaftar.classList.remove('hidden'); }

        window.claimReward = function() { let currentPoin = parseInt(document.getElementById('poin-text').innerText.split('/')[0]) || 0; document.getElementById('display-poin-modal').innerText = currentPoin; document.getElementById('modal-claim-reward').classList.remove('hidden'); }
        window.closeClaimModal = function() { document.getElementById('modal-claim-reward').classList.add('hidden'); }
        window.applyReward = function() {
            const radio = document.querySelector('input[name="reward_option"]:checked'); if (!radio) { alert("Pilih reward dulu!"); return; }
            const choice = radio.value; const discount = (choice === 'diskon') ? 10000 : 0;
            window.dispatchEvent(new CustomEvent('set-reward', { detail: { type: choice, discount: discount } })); window.closeClaimModal();
        }

        window.filterTreatments = function(categorySelect) {
            const row = categorySelect.closest('.treatment-row'); const treatmentSelect = row.querySelector('.treatment-select'); treatmentSelect.innerHTML = '<option value="">- Pilih -</option>';
            const selectedCategory = categorySelect.value; if (!selectedCategory) return;
            const filtered = rawTreatments.filter(t => t.kategori && t.kategori.trim().toLowerCase() === selectedCategory.trim().toLowerCase());
            filtered.forEach(t => { const option = document.createElement('option'); option.value = t.nama_treatment; option.textContent = t.nama_treatment; treatmentSelect.appendChild(option); });
        }
        function attachEventsToTreatmentRow(row) {
            const priceInput = row.querySelector('.harga-input'); if(!priceInput) return;
            priceInput.addEventListener('input', function(e) { let raw = this.value.replace(/[^0-9]/g, ''); this.value = raw ? rupiahFormatter.format(raw) : ''; });
        }
        window.syncMainInputs = function(source, targetClass) { const group = source.closest('.item-group'); group.querySelectorAll('.' + targetClass).forEach(input => input.value = source.value); }
        window.addTreatmentRow = function(btn) {
            const group = btn.closest('.item-group'); const container = group.querySelector('.treatments-container');
            const mainItemValue = group.querySelector('.main-item-input').value; const mainCatatanValue = group.querySelector('.main-catatan-input').value;
            const newRow = container.querySelector('.treatment-row').cloneNode(true);
            newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0); newRow.querySelector('.treatment-select').innerHTML = '<option value="">- Pilih Kat. Dulu -</option>';
            newRow.querySelectorAll('input').forEach(i => {
                if (i.classList.contains('hidden-item')) i.value = mainItemValue; else if (i.classList.contains('hidden-catatan')) i.value = mainCatatanValue;
                else { i.value = ''; if(i.name === 'tanggal_keluar[]') i.type = 'text'; }
            });
            newRow.querySelector('.btn-remove-treatment').classList.remove('hidden'); attachEventsToTreatmentRow(newRow); container.appendChild(newRow);
        }
        window.removeTreatment = function(btn) { const row = btn.closest('.treatment-row'); if (row.parentElement.querySelectorAll('.treatment-row').length > 1) row.remove(); }
        window.adjustJumlah = function(delta) {
            const input = document.getElementById('inputJumlah'); const container = document.getElementById('itemsContainer'); let val = parseInt(input.value) || 1;
            if (delta > 0) { val++; const newGroup = container.querySelector('.item-group').cloneNode(true); newGroup.querySelectorAll('input').forEach(i => { i.value = ''; if(i.name === 'tanggal_keluar[]') i.type = 'text'; }); container.appendChild(newGroup); } 
            else if (val > 1) { val--; container.removeChild(container.lastElementChild); } input.value = val;
        }
        window.openMemberModal = function() { document.getElementById('memberModal').classList.remove('hidden'); }
    </script>

    <style> .animate-fade-in { animation: fadeIn 0.3s ease-in-out; } @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } } </style>
</x-app-layout>