<x-app-layout>
    {{-- 1. STYLE: CSS Khusus Nota & Tampilan --}}
    <style>
        /* Font Nota mirip gambar referensi */
        .invoice-area { font-family: 'Helvetica', 'Arial', sans-serif; }
        .dashed-line { border-bottom: 1px dashed #000; }
        .thick-line { border-bottom: 2px solid #000; }

        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- 2. WRAPPER UTAMA --}}
    <div id="main-app" class="min-h-screen bg-white p-4 md:p-8">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center gap-3 md:gap-4 mb-6 md:mb-10">
            <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5]">Input Order</h1>
            {{-- Badge Status (Visual: New / Repeat / Member) --}}
            <span id="badge-status" class="text-sm md:text-xl font-bold px-3 py-1 rounded-full border {{ $color ?? 'text-blue-600 bg-blue-100 border-blue-200' }}">
                {{ $status ?? 'New' }}
            </span>
        </div>

        {{-- FORM UTAMA --}}
        <form id="orderForm" method="POST" onsubmit="event.preventDefault();">
            @csrf
            
            {{-- Hidden Inputs Global --}}
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">
            
            {{-- Input yang diatur via JS --}}
            <input type="hidden" name="metode_pembayaran" id="input_metode_pembayaran" value="Tunai">
            <input type="hidden" name="status_pembayaran" id="input_status_pembayaran" value="Lunas">
            <input type="hidden" name="claim_type" id="input_claim_type" value="">
            <input type="hidden" id="input_discount_amount" value="0">

            {{-- DATA PELANGGAN --}}
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

            {{-- JUMLAH ITEM & CS --}}
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
                
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex flex-col justify-center hover:shadow-md transition relative">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Cs Masuk</label>
                    <select name="cs" class="w-full bg-transparent border-none p-0 pr-8 focus:ring-0 text-gray-800 font-bold cursor-pointer appearance-none">
                        <option value="" disabled selected>- Pilih Karyawan -</option>
                        @foreach($karyawans as $k)
                            <option value="{{ $k->nama_karyawan }}">{{ $k->nama_karyawan }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center pt-4">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>

            {{-- ITEMS CONTAINER --}}
            <div id="itemsContainer" class="space-y-6 mb-6">
                <div class="item-group bg-[#E0E0E0] p-4 rounded-xl shadow-sm relative group animate-fade-in hover:shadow-md transition">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-400">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Item Name (Sepatu)</label>
                            <input type="text" class="main-item-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-bold text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Nama Barang (Cth: Nike Air Jordan)..." oninput="syncMainInputs(this, 'hidden-item')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Catatan Umum</label>
                            <input type="text" class="main-catatan-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-medium text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Catatan kondisi sepatu..." oninput="syncMainInputs(this, 'hidden-catatan')">
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
                                        @if(!empty($kategori))<option value="{{ $kategori }}">{{ $kategori }}</option>@endif
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
                                    <input type="text" name="harga[]" 
                                           class="harga-input w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 pl-7 text-xs font-bold text-gray-800 focus:ring-blue-500" 
                                           placeholder="0"
                                           oninput="formatRupiahInput(this)">
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

            {{-- BOX INFO POIN & TIPE CUSTOMER --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-2 pl-24 h-full w-full flex flex-col justify-center items-center md:col-start-5 {{ ($is_member ?? false) ? '' : 'hidden' }}">
                    <label class=" text-sm font-semibold text-gray-600 mb-1 "> Point </label>
                    <div class="flex items-center gap-3">
                        <span id="poin-text" class="text-gray-800 font-bold text-lg">{{ $poin ?? 0 }}/8</span>
                        <button type="button" id="btn-claim" onclick="window.openClaimModal()" class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow hover:bg-blue-700 transition {{ ($poin ?? 0) >= 8 ? '' : 'hidden' }}">Claim</button>
                        <span id="reward-badge" class="text-[10px] bg-green-500 text-white px-2 py-1 rounded-full animate-pulse hidden"></span>
                    </div>
                </div>
                
                <div id="box-tipe-customer" class="bg-[#E0E0E0] rounded-lg p-3 px-5 hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" 
                           name="tipe_customer" 
                           id="input_tipe_customer" 
                           value="{{ ($status ?? '') == 'New Customer' ? '' : ($status ?? '') }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold placeholder-gray-500"
                           placeholder="Isi Tipe Customer (Cth: General)">
                </div>
            </div>

            {{-- SUMBER INFO --}}
            <div id="box-sumber-info" class="grid grid-cols-1 mb-12">
                <div class="md:w-1/2 bg-[#E0E0E0] rounded-lg p-3 px-5 relative hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" class="w-full bg-transparent border-none p-0 pr-8 focus:ring-0 text-gray-800 font-medium cursor-pointer appearance-none">
                        <option value="Instagram">Instagram</option>
                        <option value="Teman">Teman</option>
                        <option value="Google Maps">Google Maps</option>
                        <option value="Lewat">Lewat Depan Toko</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center pt-4">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </div>
            </div>

            {{-- FOOTER BUTTONS --}}
            <div class="flex justify-end gap-4">
                <button type="button" id="btn-daftar-member" onclick="window.openMemberModal()" class="bg-[#3b66ff] text-white px-10 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition {{ ($is_member ?? false) ? 'hidden' : '' }}">MEMBER</button>
                <button type="button" onclick="window.openPaymentModal()" class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105">PROSES PEMBAYARAN</button>
            </div>

            {{-- MODAL PAYMENT --}}
            <div id="modal-payment" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3><button type="button" onclick="window.closePaymentModal()" class="text-white font-bold text-2xl">&times;</button></div>
                    <div class="p-6">
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" id="display-total-bill">Rp 0</div>
                            <p id="display-discount-msg" class="text-[10px] text-green-600 font-bold mt-1 italic hidden">* Sudah dipotong Diskon Reward Rp 10.000</p>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Metode</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer"><input type="radio" name="ui_payment_method" value="Tunai" checked onclick="setPaymentMethod('Tunai')" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Tunai</div></label>
                                <label class="cursor-pointer"><input type="radio" name="ui_payment_method" value="Transfer" onclick="setPaymentMethod('Transfer')" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Transfer</div></label>
                                <label class="cursor-pointer"><input type="radio" name="ui_payment_method" value="QRIS" onclick="setPaymentMethod('QRIS')" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">QRIS</div></label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Status Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer"><input type="radio" name="ui_payment_status" value="Lunas" checked onclick="setPaymentStatus('Lunas')" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white font-bold text-sm">Lunas</div></label>
                                <label class="cursor-pointer"><input type="radio" name="ui_payment_status" value="DP" onclick="setPaymentStatus('DP')" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-yellow-500 peer-checked:text-white font-bold text-sm">DP</div></label>
                            </div>
                        </div>
                        <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1" id="label-pay-amount">Uang Diterima</label>
                            <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="number" name="paid_amount" id="input_paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" placeholder="0" oninput="calculateChange()"></div>
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200"><span class="text-sm text-gray-500 font-bold">Kembalian:</span><span id="display-change" class="font-black text-green-600 text-xl">Rp 0</span></div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="window.closePaymentModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="button" onclick="window.submitOrder()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">PROSES & CETAK</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. MODAL INVOICE POPUP --}}
            <div id="modal-invoice" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-90" style="display: none;">
                <div class="bg-white p-0 rounded-lg shadow-2xl overflow-hidden max-w-2xl w-full mx-4 relative">
                    <div id="invoice-content" class="bg-white p-6 invoice-area text-xs leading-snug text-black">
                        <div class="text-center mb-2">
                            <div class="flex justify-center mb-2"><div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-xl">LC</div></div>
                            <h2 class="text-xl font-bold tracking-widest uppercase mb-1">LOUWES CARE</h2>
                            <p class="font-bold text-[10px] text-gray-600 uppercase tracking-wide">SHOE LAUNDRY & CARE</p>
                            <p class="text-[9px] mt-1 text-gray-500">Jl. Ringroad Timur No 9, Plumbon, Banguntapan, Bantul, DIY 55196</p>
                            <p class="text-[9px] text-gray-500">Instagram: @Louwes Shoes Care | WA: 081390154885</p>
                        </div>
                        <div class="thick-line mb-3"></div>
                        <div class="flex justify-between items-end mb-4">
                            <div class="text-sm font-bold">CS Masuk: <span id="inv-cs-masuk" class="font-normal"></span><br>CS Keluar: <span id="inv-cs-keluar" class="font-normal"></span></div>
                            <div class="text-2xl font-bold tracking-widest">INVOICE</div>
                        </div>
                        <div class="border-b border-black mb-2"></div>
                        <div class="flex justify-between items-start mb-4 text-[11px]">
                            <div class="w-1/2">
                                <div class="font-bold mb-1">CUSTOMER:</div>
                                <div id="inv-cust-name" class="uppercase font-bold text-sm"></div>
                                <div id="inv-cust-hp"></div>
                            </div>
                            <div class="w-1/2 text-right">
                                <div class="font-bold mb-1">DETAILS:</div>
                                <div>No: <span id="inv-no" class="font-bold"></span></div>
                                <div>Date: <span id="inv-date"></span></div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <table class="w-full text-left text-[10px]">
                                <thead>
                                    <tr class="dashed-line text-gray-600 uppercase">
                                        <th class="py-2 w-3/12">ITEM</th>
                                        <th class="py-2 w-2/12">CATATAN</th>
                                        <th class="py-2 w-3/12">TREATMENT</th>
                                        <th class="py-2 w-2/12 text-center">KELUAR</th>
                                        <th class="py-2 w-2/12 text-right">HARGA</th>
                                    </tr>
                                </thead>
                                <tbody id="inv-items-body" class="dashed-line"></tbody>
                            </table>
                        </div>
                        <div class="flex justify-end mb-6">
                            <div class="w-1/2">
                                <table class="w-full text-[11px]">
                                    <tr><td class="py-1 text-gray-600">Subtotal</td><td class="py-1 text-right" id="inv-subtotal"></td></tr>
                                    <tr class="dashed-line"><td class="py-1 text-gray-600">Diskon</td><td class="py-1 text-right" id="inv-discount"></td></tr>
                                    <tr><td class="py-2 font-bold text-sm">TOTAL</td><td class="py-2 font-bold text-sm text-right" id="inv-total"></td></tr>
                                    <tr><td class="py-1 font-bold text-green-600 uppercase" id="inv-status"></td><td class="py-1 text-right text-green-600 text-[10px]" id="inv-method"></td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="dashed-line mb-3"></div>
                        <div class="flex justify-between items-start gap-4 text-[9px] text-gray-700">
                            <div class="w-1/2">
                                <p class="font-bold mb-1">"Jika sudah tanggal deadline tetapi belum kami hubungi, mohon WA kami"</p>
                                <p class="italic">*Simpan nota ini sebagai bukti pengambilan</p>
                                <div id="inv-claim-msg" class="mt-2 font-bold border border-black p-1 text-center hidden"></div>
                            </div>
                            <div class="w-1/2">
                                <p class="font-bold underline mb-1">NB (Syarat & Ketentuan):</p>
                                <ul class="list-disc pl-3 leading-tight space-y-0.5">
                                    <li>Pengambilan barang wajib menyertakan Nota asli.</li>
                                    <li>Komplain maksimal 1x24 jam setelah barang diambil.</li>
                                    <li>Barang yang tidak diambil lebih dari 30 hari, kerusakan/kehilangan di luar tanggung jawab kami.</li>
                                    <li>Segala resiko luntur/susut karena sifat bahan sepatu, di luar tanggung jawab kami.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center mt-6 text-[10px] text-gray-500">-- Terima Kasih --</div>
                    </div>
                    <div class="bg-gray-100 p-4 flex gap-2 no-print border-t">
                        <button type="button" onclick="window.shareWhatsapp()" class="flex-1 bg-green-500 text-white py-2 rounded font-bold hover:bg-green-600">Share WA</button>
                        <button type="button" onclick="window.printInvoice()" class="flex-1 bg-gray-800 text-white py-2 rounded font-bold hover:bg-black">Cetak</button>
                        <button type="button" onclick="window.location.href = '{{ route('pesanan.index') }}'" class="flex-1 bg-red-100 text-red-600 py-2 rounded font-bold">Tutup</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- MODAL MEMBER --}}
    @include('components.member-modal')

    {{-- MODAL CLAIM --}}
    <div id="modal-claim-reward" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden" onclick="window.closeClaimModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in" onclick="event.stopPropagation()">
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

        let gTotal = 0;
        let gDiscount = 0;
        let gFinalBill = 0;

        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.item-group').forEach(group => { group.querySelectorAll('.treatment-row').forEach(row => attachEventsToTreatmentRow(row)); });
            if(document.getElementById('no_hp').value.length >= 4) { window.cekCustomer(); }
        });

        // ==========================================
        // FUNCTION CEK CUSTOMER (SINKRON DENGAN TIPE_FORM)
        // ==========================================
        window.cekCustomer = function() {
            let hp = document.getElementById('no_hp').value;
            if(hp.length < 4) return;

            $.ajax({
                url: "{{ route('check.customer') }}",
                type: "GET",
                data: { no_hp: hp },
                success: function(response) {
                    if(response.found) {
                        // 1. Isi Data Customer & Tipe Profil (General/Detail)
                        $('#nama_customer').val(response.nama);
                        
                        // PERBAIKAN: Gunakan tipe_form agar murni dari DB (bukan status Repeat Order)
                        $('#input_tipe_customer').val(response.tipe_form); 
                        
                        // 2. Isi Sumber Info (Dropdown)
                        if(response.sumber_info) {
                            $('select[name="sumber_info"]').val(response.sumber_info);
                        }

                        // 3. Hide box sumber info untuk pelanggan lama
                        $('#box-sumber-info').addClass('hidden');

                        // 4. Update Badge Visual (New / Repeat / Member)
                        let badgeText = response.badge; 
                        let colorClass = badgeText === 'Member' ? 'text-pink-600 bg-pink-100 border-pink-200' : 'text-green-600 bg-green-100 border-green-200';
                        $('#badge-status').text(badgeText).attr('class', 'text-sm md:text-xl font-bold px-3 py-1 rounded-full border ' + colorClass);

                        // 5. Logika Poin
                        $('#poin-text').text(response.poin + '/8');
                        if(badgeText === 'Member') {
                            $('#box-point').removeClass('hidden');
                            $('#btn-daftar-member').addClass('hidden');
                            $('#member_id').val(response.member_id);
                            $('#is_registered_member').val(1);
                        } else {
                            $('#box-point').addClass('hidden');
                            $('#btn-daftar-member').removeClass('hidden');
                            $('#member_id').val('');
                            $('#is_registered_member').val(0);
                        }

                    } else {
                        // KASUS PELANGGAN BARU
                        $('#box-sumber-info').removeClass('hidden');
                        $('select[name="sumber_info"]').prop('selectedIndex', 0);
                        $('#badge-status').text('New Customer').attr('class', 'text-sm md:text-xl font-bold px-3 py-1 rounded-full border text-blue-600 bg-blue-100 border-blue-200');
                        
                        // Kosongkan form Tipe Customer
                        $('#input_tipe_customer').val(''); 

                        $('#box-point').addClass('hidden');
                        $('#btn-daftar-member').removeClass('hidden');
                        $('#member_id').val('');
                        $('#is_registered_member').val(0);
                    }
                }
            });
        }

        function calculateGlobalTotal() {
            let total = 0;
            document.querySelectorAll('.harga-input').forEach(input => {
                let val = parseInt(input.value.replace(/\./g, '')) || 0;
                total += val;
            });
            gTotal = total;
            gDiscount = parseInt(document.getElementById('input_discount_amount').value) || 0;
            gFinalBill = Math.max(0, gTotal - gDiscount);

            $('#display-total-bill').text('Rp ' + rupiahFormatter.format(gFinalBill));
            if(gDiscount > 0) $('#display-discount-msg').removeClass('hidden'); else $('#display-discount-msg').addClass('hidden');
            
            if($('#input_status_pembayaran').val() === 'Lunas') {
                $('#input_paid_amount').val(gFinalBill);
                calculateChange();
            }
        }

        function calculateChange() {
            let paid = parseInt($('#input_paid_amount').val()) || 0;
            let change = Math.max(0, paid - gFinalBill);
            $('#display-change').text('Rp ' + rupiahFormatter.format(change));
        }

        window.openPaymentModal = function() {
            calculateGlobalTotal();
            document.getElementById('modal-payment').style.display = 'flex';
            document.getElementById('modal-payment').classList.remove('hidden');
        }
        window.closePaymentModal = function() {
            document.getElementById('modal-payment').style.display = 'none';
            document.getElementById('modal-payment').classList.add('hidden');
        }
        window.setPaymentMethod = function(val) { $('#input_metode_pembayaran').val(val); }
        window.setPaymentStatus = function(val) { 
            $('#input_status_pembayaran').val(val); 
            $('#label-pay-amount').text(val === 'DP' ? 'Nominal DP' : 'Uang Diterima');
            if(val === 'DP') $('#input_paid_amount').val(0);
            else $('#input_paid_amount').val(gFinalBill);
            calculateChange();
        }

        window.openClaimModal = function() {
            let currentPoin = parseInt(document.getElementById('poin-text').innerText.split('/')[0]) || 0; 
            document.getElementById('display-poin-modal').innerText = currentPoin; 
            document.getElementById('modal-claim-reward').classList.remove('hidden'); 
        }
        window.closeClaimModal = function() { document.getElementById('modal-claim-reward').classList.add('hidden'); }
        window.applyReward = function() {
            const radio = document.querySelector('input[name="reward_option"]:checked'); 
            if (!radio) { alert("Pilih reward dulu!"); return; }
            const choice = radio.value; 
            const discount = (choice === 'diskon') ? 10000 : 0;
            $('#input_claim_type').val(choice);
            $('#input_discount_amount').val(discount);
            let badgeText = choice === 'diskon' ? 'Reward: Diskon 10rb' : 'Reward: Free Parfum';
            $('#reward-badge').text(badgeText).removeClass('hidden');
            window.closeClaimModal();
            calculateGlobalTotal();
        }

        window.submitOrder = function() {
            let formData = $('#orderForm').serialize();
            $.ajax({
                url: "{{ route('orders.store') }}", type: "POST", data: formData, dataType: 'json', headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if(response.status === 'success') {
                        populateInvoice(response);
                        window.closePaymentModal();
                        document.getElementById('modal-invoice').style.display = 'flex';
                        document.getElementById('modal-invoice').classList.remove('hidden');
                    }
                },
                error: function(xhr) { alert("Gagal menyimpan: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText)); }
            });
        }

        function populateInvoice(data) {
            let order = data.order; let cust = order.customer;
            $('#inv-cs-masuk').text(order.kasir || '-'); 
            $('#inv-cs-keluar').text(order.kasir_keluar || '-');
            $('#inv-cust-name').text(cust.nama || 'Guest');
            $('#inv-cust-hp').text(cust.no_hp || '-');
            $('#inv-no').text(order.no_invoice);
            let date = new Date(order.created_at);
            let dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            $('#inv-date').text(dateStr);

            let rows = '';
            order.details.forEach(item => {
                let estStr = '-';
                if(item.estimasi_keluar) {
                    let estDate = new Date(item.estimasi_keluar);
                    estStr = estDate.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                rows += `
                    <tr class="align-top">
                        <td class="py-2 font-bold pr-2">${item.nama_barang}</td>
                        <td class="py-2 pr-2 text-gray-600 italic">${item.catatan || '-'}</td>
                        <td class="py-2 pr-2">${item.layanan}</td>
                        <td class="py-2 text-center whitespace-nowrap">${estStr}</td>
                        <td class="py-2 text-right font-bold">${rupiahFormatter.format(item.harga)}</td>
                    </tr>
                `;
            });
            $('#inv-items-body').html(rows);
            $('#inv-subtotal').text(rupiahFormatter.format(data.original_total));
            $('#inv-discount').text('- ' + rupiahFormatter.format(data.discount_amount));
            $('#inv-total').text(rupiahFormatter.format(order.total_harga));
            $('#inv-status').text(order.status_pembayaran ? order.status_pembayaran.toUpperCase() : '-');
            $('#inv-method').text(order.metode_pembayaran ? 'via ' + order.metode_pembayaran : '');

            let msgDiv = $('#inv-claim-msg');
            if (data.claim_type === 'Diskon') { msgDiv.text('*** DISKON POIN DIGUNAKAN ***').removeClass('hidden'); }
            else if (data.claim_type === 'Parfum') { msgDiv.text('*** FREE PARFUM CLAIMED ***').removeClass('hidden'); }
            else { msgDiv.addClass('hidden'); }
        }

        window.printInvoice = function() {
            var content = document.getElementById('invoice-content').innerHTML;
            var mywindow = window.open('', 'PRINT', 'height=600,width=400');
            mywindow.document.write('<html><head><title>Invoice</title><style>body{font-family:"Helvetica","Arial",sans-serif;font-size:12px;margin:0;padding:10px;color:#000}.text-center{text-align:center}.text-right{text-align:right}.font-bold{font-weight:700}.uppercase{text-transform:uppercase}.italic{font-style:italic}.hidden{display:none}table{width:100%;border-collapse:collapse;margin-bottom:5px}td,th{vertical-align:top;padding:2px 0}.w-4\\/12{width:35%}.w-3\\/12{width:25%}.w-2\\/12{width:15%}.text-\\[10px\\]{font-size:10px}.text-\\[9px\\]{font-size:9px}.dashed-line{border-bottom:1px dashed #000}.thick-line{border-bottom:2px solid #000}.border-b{border-bottom:1px solid #000}ul{padding-left:15px;margin:5px 0}.flex{display:flex;justify-content:space-between;align-items:flex-end}</style></head><body>' + content + '</body></html>');
            mywindow.document.close(); mywindow.focus(); setTimeout(function() { mywindow.print(); mywindow.close(); }, 500);
        }

        window.shareWhatsapp = function() {
            let no = $('#inv-no').text(); let total = $('#inv-total').text(); let name = $('#inv-cust-name').text();
            let text = `Halo Kak ${name}, Terima kasih telah mempercayakan sepatu kakak di Louwes Care. \nNo Nota: ${no} \nTotal: ${total}. \n\nSimpan pesan ini sebagai bukti pengambilan ya kak!`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        window.filterTreatments = function(categorySelect) {
            const row = categorySelect.closest('.treatment-row'); const treatmentSelect = row.querySelector('.treatment-select'); treatmentSelect.innerHTML = '<option value="">- Pilih -</option>';
            const selectedCategory = categorySelect.value; if (!selectedCategory) return;
            const filtered = rawTreatments.filter(t => t.kategori && t.kategori.trim().toLowerCase() === selectedCategory.trim().toLowerCase());
            filtered.forEach(t => { const option = document.createElement('option'); option.value = t.nama_treatment; option.textContent = t.nama_treatment; treatmentSelect.appendChild(option); });
        }
        function attachEventsToTreatmentRow(row) {
            const priceInput = row.querySelector('.harga-input'); if(!priceInput) return;
            priceInput.addEventListener('input', function(e) { let raw = this.value.replace(/[^0-9]/g, ''); this.value = raw ? rupiahFormatter.format(raw) : ''; calculateGlobalTotal(); });
        }
        window.syncMainInputs = function(source, targetClass) { const group = source.closest('.item-group'); group.querySelectorAll('.' + targetClass).forEach(input => input.value = source.value); }
        window.addTreatmentRow = function(btn) {
            const group = btn.closest('.item-group'); const container = group.querySelector('.treatments-container');
            const mainItemValue = group.querySelector('.main-item-input').value;
            const mainCatatanValue = group.querySelector('.main-catatan-input').value;
            const newRow = container.querySelector('.treatment-row').cloneNode(true);
            newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0); newRow.querySelector('.treatment-select').innerHTML = '<option value="">- Pilih Kat. Dulu -</option>';
            newRow.querySelectorAll('input').forEach(i => {
                if (i.classList.contains('hidden-item')) i.value = mainItemValue; else if (i.classList.contains('hidden-catatan')) i.value = mainCatatanValue;
                else { i.value = ''; if(i.name === 'tanggal_keluar[]') i.type = 'text'; }
            });
            newRow.querySelector('.btn-remove-treatment').classList.remove('hidden'); attachEventsToTreatmentRow(newRow); container.appendChild(newRow);
        }
        window.removeTreatment = function(btn) { const row = btn.closest('.treatment-row'); if (row.parentElement.querySelectorAll('.treatment-row').length > 1) row.remove(); calculateGlobalTotal(); }
        window.adjustJumlah = function(delta) {
            const input = document.getElementById('inputJumlah'); const container = document.getElementById('itemsContainer'); let val = parseInt(input.value) || 1;
            if (delta > 0) { val++; const newGroup = container.querySelector('.item-group').cloneNode(true); newGroup.querySelectorAll('input').forEach(i => { i.value = ''; if(i.name === 'tanggal_keluar[]') i.type = 'text'; }); container.appendChild(newGroup); } 
            else if (val > 1) { val--; container.removeChild(container.lastElementChild); } input.value = val;
            calculateGlobalTotal();
        }
        window.openMemberModal = function() { document.getElementById('memberModal').classList.remove('hidden'); }
        function formatRupiahInput(input) { let val = input.value.replace(/[^0-9]/g, ''); input.value = val ? rupiahFormatter.format(val) : ''; }
    </script>
</x-app-layout>