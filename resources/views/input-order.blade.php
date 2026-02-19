<x-app-layout>
    {{-- 1. STYLE: CSS Khusus Nota & Tampilan --}}
    <style>
        .invoice-area { font-family: 'Helvetica', 'Arial', sans-serif; }
        .dashed-line { border-bottom: 1px dashed #000; }
        .thick-line { border-bottom: 2px solid #000; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- 2. WRAPPER UTAMA --}}
    <div id="main-app" class="w-full min-h-screen bg-white p-4 md:p-8">

        {{-- HEADER --}}
        <div class="flex flex-wrap md:flex-nowrap justify-between items-center gap-2 md:gap-4 mb-6 md:mb-10" style="padding-top: 5rem;">
            <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5] leading-none m-0">
                Input Order
            </h1>
            <span id="badge-status" class="text-sm md:text-xl font-bold px-3 py-1 rounded-full border whitespace-nowrap m-0 {{ $color ?? 'text-blue-600 bg-blue-100 border-blue-200' }}">
                {{ $status ?? 'New Customer' }}
            </span>
        </div>

        {{-- FORM UTAMA --}}
        <form id="orderForm" method="POST" onsubmit="event.preventDefault();">
            @csrf
            
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">
            <input type="hidden" name="metode_pembayaran" id="input_metode_pembayaran" value="Tunai">
            <input type="hidden" name="status_pembayaran" id="input_status_pembayaran" value="Lunas">
            <input type="hidden" name="claim_diskon_qty" id="input_claim_diskon_qty" value="0">
            <input type="hidden" name="claim_parfum_qty" id="input_claim_parfum_qty" value="0">
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
                    <select name="cs" style="background-image: none;" class="w-full bg-transparent border-none p-0 pr-8 focus:ring-0 text-gray-800 font-bold cursor-pointer appearance-none">
                        <option value="" disabled selected>Pilih Karyawan</option>
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pb-4 border-b border-gray-400">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Item Name (Sepatu)</label>
                            <input type="text" class="main-item-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-bold text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Nama Barang" oninput="syncMainInputs(this, 'hidden-item')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Catatan</label>
                            <input type="text" class="main-catatan-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-medium text-gray-800 focus:ring-0 focus:border-blue-500 placeholder-gray-500" 
                                   placeholder="Catatan kondisi sepatu" oninput="syncMainInputs(this, 'hidden-catatan')">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Estimasi Selesai</label>
                            <div class="relative w-full">
                                <input type="date" class="main-estimasi-input w-full bg-white/50 border border-gray-400 rounded-md p-2 text-sm font-bold text-gray-800 focus:ring-0 focus:border-blue-500 cursor-pointer"
                                       style="color: transparent;"
                                       onfocus="this.style.color='inherit'; this.nextElementSibling.classList.add('hidden');"
                                       onblur="if(!this.value){ this.style.color='transparent'; this.nextElementSibling.classList.remove('hidden'); }"
                                       onchange="this.style.color='inherit'; this.nextElementSibling.classList.add('hidden'); syncMainInputs(this, 'hidden-estimasi')">
                                <span class="absolute inset-y-0 left-2 flex items-center text-gray-500 text-xs pointer-events-none transition-opacity">
                                    Pilih Tanggal
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="treatments-container space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wide">Daftar Treatment</label>
                        <div class="treatment-row grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-3 rounded-lg border border-gray-300 relative shadow-sm">
                            <input type="hidden" name="item[]" class="hidden-item">
                            <input type="hidden" name="catatan[]" class="hidden-catatan">
                            <input type="hidden" name="tanggal_keluar[]" class="hidden-estimasi">
                            
                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Kategori</label>
                                <div class="relative">
                                    <select class="category-select w-full max-w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 pr-8 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500 appearance-none truncate" style="-webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: none !important;" onchange="filterTreatments(this)">
                                        <option value="">Pilih</option>
                                        @foreach($treatments->pluck('kategori')->unique()->values() as $kategori)
                                            @if(!empty($kategori))<option value="{{ $kategori }}">{{ $kategori }}</option>@endif
                                        @endforeach
                                        <option value="Custom" class="font-bold text-blue-600">+ Custom (Manual)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Layanan</label>
                                
                                <div class="flex gap-2 w-full">
                                    
                                    <div class="relative flex-1 min-w-0">
                                        <select name="kategori_treatment[]" class="treatment-select w-full max-w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 pr-8 text-xs font-medium text-gray-800 cursor-pointer focus:ring-blue-500 appearance-none truncate" style="-webkit-appearance: none; -moz-appearance: none; appearance: none; background-image: none !important;">
                                            <option value="">Pilih Kategori Dulu</option>
                                        </select>
                                        <input type="text" name="kategori_treatment[]" class="treatment-input hidden w-full max-w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 focus:ring-blue-500 placeholder-gray-400" placeholder="Ketik Manual..." disabled>
                                        <div class="chevron-icon pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>

                                    <div class="warna-container hidden flex-1 min-w-0 relative">
                                        <input type="text" class="input-warna w-full max-w-full bg-gray-50 border border-gray-300 rounded-md p-1.5 text-xs font-medium text-gray-800 focus:ring-blue-500 placeholder-gray-400" placeholder="Pilih warna">
                                    </div>

                                </div>
                            </div>
                            
                            <div class="md:col-span-4">
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

            {{-- BOX TIPE CUSTOMER (KIRI) & POIN (KANAN) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 items-center">
                {{-- Tipe Customer (Kiri) --}}
                <div id="box-tipe-customer" class="bg-[#E0E0E0] rounded-lg p-3 px-5 hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" 
                           name="tipe_customer" 
                           id="input_tipe_customer" 
                           value="{{ $tipe_pilihan ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold placeholder-gray-500"
                           placeholder="Isi Tipe Customer (Cth: General)">
                </div>

                {{-- Box Point Mepet Kanan --}}
                <div class="flex justify-end">
                    <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-2 px-4 flex items-center gap-4 hover:shadow-md transition w-fit border border-gray-300 {{ ($is_member ?? false) ? '' : 'hidden' }}">
                        <div class="flex flex-col border-r border-gray-400 pr-4">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-tight">Point</label>
                            <span id="poin-text" class="text-gray-800 font-black text-base leading-none">{{ $poin ?? 0 }}/8</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-claim" onclick="window.openClaimModal()" 
                                    class="bg-blue-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-md shadow-sm hover:bg-blue-700 transition {{ ($poin ?? 0) >= 8 ? '' : 'hidden' }}">
                                CLAIM
                            </button>
                            <span id="reward-badge" class="text-[9px] bg-green-500 text-white px-2 py-1 rounded-full animate-pulse hidden"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- SUMBER INFO: OTOMATIS HIDDEN UNTUK MEMBER/REPEAT ORDER --}}
            <div id="box-sumber-info" class="grid grid-cols-1 mb-12 {{ ($is_member ?? false) || ($status ?? '') == 'Repeat Order' ? 'hidden' : '' }}">
                <div class="w-full bg-[#E0E0E0] rounded-lg p-3 px-5 relative hover:shadow-md transition">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" style="background-image: none;" class="w-full bg-transparent border-none p-0 pr-8 focus:ring-0 text-gray-800 font-medium cursor-pointer appearance-none">
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
            <div class="flex flex-col md:flex-row justify-end gap-3 md:gap-4 mt-2 mb-12 md:mb-0">
                <button type="button" id="btn-daftar-member" onclick="window.openMemberModal()" 
                        class="w-full md:w-auto bg-[#3b66ff] text-white px-6 md:px-10 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition {{ ($is_member ?? false) ? 'hidden' : '' }}">
                    MEMBER
                </button>
                <button type="button" onclick="window.openPaymentModal()" 
                        class="w-full md:w-auto bg-[#3b66ff] text-white px-6 md:px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform md:hover:scale-105">
                    PROSES PEMBAYARAN
                </button>
            </div>

            <div style="height: 50px; width: 100%; display: block;"></div>

            {{-- MODAL PAYMENT --}}
            <div id="modal-payment" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Rincian Pembayaran</h3><button type="button" onclick="window.closePaymentModal()" class="text-white font-bold text-2xl">&times;</button></div>
                    <div class="p-6">
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Tagihan</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1" id="display-total-bill">Rp 0</div>
                            <p id="display-discount-msg" class="text-[10px] text-green-600 font-bold mt-1 italic hidden">* Sudah dipotong Diskon Reward Rp {{ number_format($nominal_diskon ?? \App\Models\Setting::getDiskonMember(), 0, ',', '.') }}</p>
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
                            <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="text" name="paid_amount" id="input_paid_amount" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" placeholder="0" oninput="formatRupiahInput(this); calculateChange()"></div>
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200">
                                <span id="label-change-type" class="text-sm text-gray-500 font-bold">Kembalian:</span>
                                <span id="display-change" class="font-black text-green-600 text-xl">Rp 0</span>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="window.closePaymentModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="button" onclick="window.submitOrder()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">PROSES & CETAK</button>
                        </div>
                    </div>
                </div>
            </div>

{{-- 4. MODAL INVOICE POPUP (SINKRON DENGAN MANAJEMEN PESANAN) --}}
            <div id="modal-invoice" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-90" style="display: none;">
                <div class="bg-white p-0 rounded-lg shadow-2xl overflow-hidden max-w-2xl w-full mx-4 relative flex flex-col max-h-[90vh]">
                    <div id="invoice-content" class="bg-white p-6 invoice-area text-xs leading-snug text-black overflow-y-auto">
                        <div class="text-center mb-2">
                            <div class="flex justify-center mb-2"><div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-xl">LC</div></div>
                            <h2 class="text-xl font-bold tracking-widest uppercase mb-1">LOUWES CARE</h2>
                            <p class="font-bold text-[10px] text-gray-600 uppercase tracking-wide">SHOE LAUNDRY & CARE</p>
                            <p class="text-[9px] mt-1 text-gray-500">Jl. Ringroad Timur No 9, Plumbon, Banguntapan, Bantul, DIY 55196</p>
                            <p class="text-[9px] text-gray-500">Instagram: @Louwes Shoes Care | WA: 081390154885</p>
                        </div>
                        <div class="thick-line mb-3"></div>
                        <div class="flex justify-between items-end mb-4">
                            <div class="text-sm font-bold">
                                CS Masuk: <span id="inv-cs-masuk" class="font-normal"></span><br>
                                CS Keluar: <span id="inv-cs-keluar" class="font-normal"></span>
                            </div>
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
                            <div class="w-full sm:w-1/2">
                                <table class="w-full text-[11px]">
                                    <tr><td class="py-1 text-gray-600">Subtotal</td><td class="py-1 text-right" id="inv-subtotal"></td></tr>
                                    <tr class="dashed-line" id="inv-discount-row"><td class="py-1 text-gray-600" id="inv-discount-label">Diskon</td><td class="py-1 text-right" id="inv-discount"></td></tr>
                                    <tr><td class="py-2 font-bold text-sm">TOTAL</td><td class="py-2 font-bold text-sm text-right" id="inv-total"></td></tr>
                                    
                                    {{-- BARIS KHUSUS DP & SISA --}}
                                    <tr id="inv-dp-row" class="hidden">
                                        <td class="py-1 text-gray-600 font-bold">DP Dibayar <span id="inv-dp-method" class="font-normal italic text-[9px]"></span></td>
                                        <td class="py-1 text-right font-bold" id="inv-dp-amount"></td>
                                    </tr>
                                    <tr id="inv-sisa-row" class="dashed-line hidden">
                                        <td class="py-1 text-gray-800 font-bold italic">SISA TAGIHAN</td>
                                        <td class="py-1 text-right text-gray-800 font-bold italic" id="inv-sisa-amount"></td>
                                    </tr>
    
                                    {{-- BARIS STATUS DEFAULT --}}
                                    <tr id="inv-status-row">
                                        <td class="py-1 font-bold text-green-600 uppercase" id="inv-status"></td>
                                        <td class="py-1 text-right text-green-600 text-[10px]" id="inv-method"></td>
                                    </tr>
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
                                    <li>Barang rusak karena bahan sudah rapuh bukan tanggungjawab kami.</li>
                                    <li>Apabila barang tidak diambil lebih dari 3 Bulan setelah jadi , hilang bukan tanggung jawab kami.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center mt-6 text-[10px] text-gray-500">-- Terima Kasih --</div>
                    </div>
                    <div class="bg-gray-100 p-4 flex gap-2 no-print border-t shrink-0">
                        <button type="button" onclick="window.printInvoice()" class="flex-1 bg-gray-800 text-white py-2 rounded font-bold hover:bg-black">Cetak</button>
                        <button type="button" onclick="window.location.href = '{{ route('pesanan.index') }}'" class="flex-1 bg-red-100 text-red-600 py-2 rounded font-bold">Tutup</button>
                    </div>
                </div>
            </div>

    @include('components.member-modal')

{{-- MODAL CLAIM --}}
<div id="modal-claim-reward" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden" onclick="window.closeClaimModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in" onclick="event.stopPropagation()">
        <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg">Pilih Reward</h3>
            <button type="button" onclick="window.closeClaimModal()" class="text-white font-bold text-2xl">&times;</button>
        </div>
        <div class="p-6">
            <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                <span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span>
                <div class="text-3xl font-black text-[#3b66ff] mt-1"><span id="display-poin-modal">0</span> pts</div>
            </div>
            
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 transition">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="check_diskon" class="form-checkbox text-[#3b66ff] rounded w-5 h-5 mr-3 focus:ring-blue-500" onchange="toggleCheckbox('diskon')">
                        <div>
                            <p class="font-bold text-sm text-gray-800">Diskon Rp {{ number_format($nominal_diskon ?? 10000, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-gray-500 font-bold uppercase">8 Poin / Klaim</p>
                        </div>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 font-bold">Qty:</span>
                        <input type="number" id="modal_qty_diskon" min="0" value="0" oninput="syncCheckbox('diskon')" class="w-14 px-2 py-1 border-gray-300 rounded-md text-center font-bold text-sm focus:ring-blue-500 shadow-sm">
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 transition">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" id="check_parfum" class="form-checkbox text-[#3b66ff] rounded w-5 h-5 mr-3 focus:ring-blue-500" onchange="toggleCheckbox('parfum')">
                        <div>
                            <p class="font-bold text-sm text-gray-800">Free Parfum</p>
                            <p class="text-[10px] text-gray-500 font-bold uppercase">8 Poin / Klaim</p>
                        </div>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 font-bold">Qty:</span>
                        <input type="number" id="modal_qty_parfum" min="0" value="0" oninput="syncCheckbox('parfum')" class="w-14 px-2 py-1 border-gray-300 rounded-md text-center font-bold text-sm focus:ring-blue-500 shadow-sm">
                    </div>
                </div>
                
                <div id="claim-warning" class="text-xs text-red-500 font-bold text-center mt-2 hidden">
                    Poin tidak mencukupi untuk jumlah klaim ini!
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                <button type="button" id="btn-apply-reward" onclick="window.applyReward()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg">Terapkan</button>
            </div>
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

        window.cekCustomer = function() {
            let hp = document.getElementById('no_hp').value;
            if(hp.length < 4) return;

            $.ajax({
                url: "{{ route('check.customer') }}",
                type: "GET",
                data: { no_hp: hp },
                success: function(response) {
                    if(response.found) {
                        $('#nama_customer').val(response.nama);
                        $('#input_tipe_customer').val(response.tipe_form); 
                        
                        if(response.sumber_info) {
                            $('select[name="sumber_info"]').val(response.sumber_info);
                        }

                        $('#box-sumber-info').addClass('hidden');

                        let badgeText = response.badge; 
                        let colorClass = badgeText === 'Member' ? 'text-pink-600 bg-pink-100 border-pink-200' : 'text-green-600 bg-green-100 border-green-200';
                        $('#badge-status').text(badgeText).attr('class', 'text-sm md:text-xl font-bold px-3 py-1 rounded-full border whitespace-nowrap m-0 ' + colorClass);

                        $('#poin-text').text(response.poin + '/8 pts');
                        if(badgeText === 'Member') {
                            $('#box-point').removeClass('hidden');
                            $('#btn-daftar-member').addClass('hidden');
                            $('#member_id').val(response.member_id);
                            $('#is_registered_member').val(1);
                            
                            if(response.poin >= 8) $('#btn-claim').removeClass('hidden');
                            else $('#btn-claim').addClass('hidden');
                        } else {
                            $('#box-point').addClass('hidden');
                            $('#btn-daftar-member').removeClass('hidden');
                            $('#member_id').val('');
                            $('#is_registered_member').val(0);
                        }

                    } else {
                        $('#box-sumber-info').removeClass('hidden');
                        $('select[name="sumber_info"]').prop('selectedIndex', 0);
                        $('#badge-status').text('New Customer').attr('class', 'text-sm md:text-xl font-bold px-3 py-1 rounded-full border whitespace-nowrap m-0 text-blue-600 bg-blue-100 border-blue-200');
                        
                        $('#nama_customer').val('');
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
                $('#input_paid_amount').val(rupiahFormatter.format(gFinalBill));
                calculateChange();
            }
        }

        function calculateChange() {
            let rawPaid = $('#input_paid_amount').val().replace(/\./g, '');
            let paid = parseInt(rawPaid) || 0;
            let status = $('#input_status_pembayaran').val();
            
            if (status === 'DP') {
                let sisa = Math.max(0, gFinalBill - paid);
                $('#label-change-type').text('Sisa Tagihan:');
                $('#display-change').text('Rp ' + rupiahFormatter.format(sisa))
                                    .removeClass('text-green-600').addClass('text-red-500');
            } else {
                let change = Math.max(0, paid - gFinalBill);
                $('#label-change-type').text('Kembalian:');
                $('#display-change').text('Rp ' + rupiahFormatter.format(change))
                                    .removeClass('text-red-500').addClass('text-green-600');
            }
        }

        window.openPaymentModal = function() {
            // --- VALIDASI INPUT SEBELUM LANJUT ---
            let errors = [];

            // 1. Validasi Data Customer & CS
            if(!$('#nama_customer').val().trim()) errors.push("Nama Customer wajib diisi.");
            if(!$('#no_hp').val().trim()) errors.push("Nomor HP wajib diisi.");
            if(!$('select[name="cs"]').val()) errors.push("Silakan pilih CS Masuk.");

            // 2. Validasi Item & Treatment
            document.querySelectorAll('.item-group').forEach((group, index) => {
                let itemName = group.querySelector('.main-item-input').value.trim();
                let estimasi = group.querySelector('.main-estimasi-input').value;
                
                if(!itemName) errors.push(`Nama Item (Sepatu) ke-${index+1} belum diisi.`);
                if(!estimasi) errors.push(`Estimasi Selesai untuk item '${itemName || 'ke-'+(index+1)}' belum dipilih.`);

                group.querySelectorAll('.treatment-row').forEach((row) => {
                    let category = row.querySelector('.category-select').value;
                    
                    // Perbaikan Validasi: Cek input manual jika kategori Custom
                    let serviceSelect = row.querySelector('.treatment-select');
                    let serviceInput = row.querySelector('.treatment-input');
                    let service = (category === 'Custom') ? serviceInput.value.trim() : serviceSelect.value;

                    let price = row.querySelector('.harga-input').value.trim();

                    if(!category || !service) errors.push(`Layanan/Treatment untuk item '${itemName || 'ke-'+(index+1)}' belum lengkap.`);
                    if(!price) errors.push(`Harga untuk item '${itemName || 'ke-'+(index+1)}' belum diisi.`);
                });
            });

            if(errors.length > 0) {
                alert("Mohon lengkapi data berikut sebelum lanjut:\n\n- " + errors.join("\n- "));
                return;
            }

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
            if(val === 'DP') $('#input_paid_amount').val('');
            else $('#input_paid_amount').val(rupiahFormatter.format(gFinalBill));
            calculateChange();
        }

// ======= JAVASCRIPT LOGIKA KLAIM REWARD =======

        window.openClaimModal = function() {
            let currentPoin = parseInt(document.getElementById('poin-text').innerText.split('/')[0]) || 0; 
            document.getElementById('display-poin-modal').innerText = currentPoin; 
            
            // Reset semua input & checkbox saat modal dibuka
            document.getElementById('check_diskon').checked = false;
            document.getElementById('check_parfum').checked = false;
            document.getElementById('modal_qty_diskon').value = 0;
            document.getElementById('modal_qty_parfum').value = 0;
            
            window.validatePointsClaim(); 
            document.getElementById('modal-claim-reward').classList.remove('hidden'); 
        }

        window.closeClaimModal = function() { 
            document.getElementById('modal-claim-reward').classList.add('hidden'); 
        }

        // Fungsi jika user KLiK CHECKBOX
        window.toggleCheckbox = function(type) {
            let checkbox = document.getElementById('check_' + type);
            let input = document.getElementById('modal_qty_' + type);

            if (checkbox.checked) {
                if (input.value == 0) input.value = 1; // Otomatis isi 1 jika dicentang
            } else {
                input.value = 0; // Kembalikan ke 0 jika tidak dicentang
            }
            window.validatePointsClaim();
        }

        // Fungsi jika user KETIK ANGKA QTY
        window.syncCheckbox = function(type) {
            let checkbox = document.getElementById('check_' + type);
            let input = document.getElementById('modal_qty_' + type);

            if (input.value > 0) {
                checkbox.checked = true; // Otomatis centang jika angka lebih dari 0
            } else {
                checkbox.checked = false;
            }
            window.validatePointsClaim();
        }

        // Fungsi Cek Poin Validasi
        window.validatePointsClaim = function() {
            let currentPoin = parseInt(document.getElementById('display-poin-modal').innerText) || 0;
            
            let qtyDiskon = document.getElementById('check_diskon').checked ? (parseInt(document.getElementById('modal_qty_diskon').value) || 0) : 0;
            let qtyParfum = document.getElementById('check_parfum').checked ? (parseInt(document.getElementById('modal_qty_parfum').value) || 0) : 0;
            
            let totalPointsNeeded = (qtyDiskon * 8) + (qtyParfum * 8);
            
            let warningText = document.getElementById('claim-warning');
            let applyBtn = document.getElementById('btn-apply-reward');

            if (totalPointsNeeded > currentPoin) {
                warningText.classList.remove('hidden');
                applyBtn.disabled = true;
                applyBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                warningText.classList.add('hidden');
                applyBtn.disabled = false;
                applyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // Fungsi Terapkan Reward
        // Fungsi Terapkan Reward
        window.applyReward = function() {
            let qtyDiskon = document.getElementById('check_diskon').checked ? (parseInt(document.getElementById('modal_qty_diskon').value) || 0) : 0;
            let qtyParfum = document.getElementById('check_parfum').checked ? (parseInt(document.getElementById('modal_qty_parfum').value) || 0) : 0;
            
            if (qtyDiskon === 0 && qtyParfum === 0) {
                alert("Pilih minimal satu reward dengan quantity lebih dari 0!");
                return;
            }

            const nominalDiskonPerItem = {{ $nominal_diskon ?? 10000 }};
            const totalDiscount = qtyDiskon * nominalDiskonPerItem;
            
            let claimTexts = [];
            if (qtyDiskon > 0) claimTexts.push(`${qtyDiskon}x Diskon`);
            if (qtyParfum > 0) claimTexts.push(`${qtyParfum}x Parfum`);
            let claimString = claimTexts.join(' & ');

            // INI PENTING: Menyimpan qty ke input hidden form agar tersimpan di Database
            $('#input_claim_diskon_qty').val(qtyDiskon);
            $('#input_claim_parfum_qty').val(qtyParfum);
            $('#input_discount_amount').val(totalDiscount);
            
            $('#reward-badge').text('Reward: ' + claimString).removeClass('hidden');
            
            window.closeClaimModal();
            calculateGlobalTotal();
        }
        
window.submitOrder = function() {
    // --- 1. LOGIKA GABUNG TEKS (KATEGORI + LAYANAN + WARNA) ---
    // Kita looping semua baris treatment, bukan cuma yang ada warnanya
    $('.treatment-row').each(function() {
        let row = $(this);
        
        // Ambil nama Kategori
        let namaKategori = row.find('.category-select').val() || '';
        
        // Cari Layanan mana yang dipakai (Dropdown Select atau Input Custom)
        let selectLayanan = row.find('select.treatment-select');
        let inputLayanan = row.find('input.treatment-input');
        let layananAktif = selectLayanan.is(':not(.hidden)') ? selectLayanan : inputLayanan;
        let namaLayanan = layananAktif.val() || '';
        
        // Cari Warna (Jika input warna sedang tampil dan ada isinya)
        let containerWarna = row.find('.warna-container');
        let inputWarna = containerWarna.find('.input-warna').val() || '';
        let teksWarna = (!containerWarna.hasClass('hidden') && inputWarna.trim() !== '') ? ' - Warna: ' + inputWarna : '';
        
        // Jika kategori dan layanan sudah diisi, kita gabungkan teksnya
        if (namaKategori !== '' && namaLayanan !== '') {
            let teksGabungan = namaKategori + ' - ' + namaLayanan + teksWarna;
            
            // Hapus input rahasia lama jika ada (mencegah data ganda kalau tombol diklik 2x)
            row.find('.hidden-gabungan-layanan').remove();
            
            // Buat input rahasia baru untuk dikirim ke Laravel (database)
            row.append('<input type="hidden" class="hidden-gabungan-layanan" name="kategori_treatment[]" value="' + teksGabungan + '">');
            
            // Matikan atribut name pada input asli agar tidak ikut terkirim ke database
            layananAktif.removeAttr('name');
        }
    });
    // --- AKHIR LOGIKA GABUNG TEKS ---

    // 2. PROSES AJAX KE BACKEND
    let formData = $('#orderForm').serialize();
    $.ajax({
        url: "{{ route('orders.store') }}", 
        type: "POST", 
        data: formData, 
        dataType: 'json', 
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if(response.status === 'success') {
                populateInvoice(response); // Invoice otomatis baca teks yang sudah digabung!
                window.closePaymentModal();
                document.getElementById('modal-invoice').style.display = 'flex';
                document.getElementById('modal-invoice').classList.remove('hidden');
            }
        },
        error: function(xhr) { 
            alert("Gagal menyimpan: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText)); 
        }
    });
}

function populateInvoice(data) {
            let order = data.order; 
            let cust = order.customer || {};
            
            $('#inv-cs-masuk').text(order.kasir || '-'); 
            $('#inv-cs-keluar').text(order.kasir_keluar || '-');
            $('#inv-cust-name').text(cust.nama || order.nama_customer || 'Guest');
            $('#inv-cust-hp').text(cust.no_hp || order.no_hp || '-');
            $('#inv-no').text(order.no_invoice);
            
            let date = new Date(order.created_at);
            let dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            $('#inv-date').text(dateStr);

            let rows = '';
            
            // GROUPING LOGIC ITEM AGAR RAPI (TIDAK DOUBLE)
            let groupedItems = {};
            if(order.details) {
                order.details.forEach(item => {
                    let key = item.nama_barang.trim().toLowerCase();
                    if (!groupedItems[key]) {
                        groupedItems[key] = {
                            nama_barang: item.nama_barang,
                            layanan: [],
                            catatan: [],
                            estimasi_keluar: item.estimasi_keluar,
                            harga: 0
                        };
                    }
                    groupedItems[key].layanan.push(item.layanan);
                    if (item.catatan && item.catatan !== '-' && item.catatan.trim() !== '') {
                        groupedItems[key].catatan.push(item.catatan);
                    }
                    groupedItems[key].harga += parseInt(item.harga);
                    if (item.estimasi_keluar && (!groupedItems[key].estimasi_keluar || item.estimasi_keluar > groupedItems[key].estimasi_keluar)) {
                        groupedItems[key].estimasi_keluar = item.estimasi_keluar;
                    }
                });
            }

            Object.values(groupedItems).forEach(group => {
                let estStr = '-';
                if(group.estimasi_keluar) {
                    let estDate = new Date(group.estimasi_keluar);
                    estStr = estDate.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
                }
                
                let layananStr = group.layanan.join(' + ');
                let catatanStr = group.catatan.length > 0 ? group.catatan.join(', ') : '-';

                rows += `<tr>
                    <td class="align-top border-b border-gray-100 py-1 pr-1"><span class="font-bold">${group.nama_barang}</span></td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">${catatanStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">${layananStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-center text-[10px]">${estStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-right">${rupiahFormatter.format(group.harga)}</td>
                </tr>`;
            });
            
            // LOGIKA PARFUM DINAMIS
            let claimType = order.klaim || data.claim_type;
            if (claimType && claimType.toLowerCase().includes('parfum')) {
                let match = claimType.match(/(\d+)\s*x\s*parfum/i);
                let qtyParfum = match ? match[1] : 1;
                rows += `<tr>
                    <td class="align-top border-b border-gray-100 py-1 pr-1"><span class="font-bold">${qtyParfum}x Free Parfum</span></td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">Klaim Reward</td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">-</td>
                    <td class="align-top border-b border-gray-100 py-1 text-center text-[10px]">-</td>
                    <td class="align-top border-b border-gray-100 py-1 text-right">0</td>
                </tr>`;
            }

            $('#inv-items-body').html(rows);
            $('#inv-subtotal').text(rupiahFormatter.format(data.original_total || order.total_harga));
            
            // LOGIKA DISKON DINAMIS
            let discountAmount = data.discount_amount || 0;
            if (discountAmount > 0) {
                let qtyDiskon = 1;
                if (claimType) {
                    let matchDiskon = claimType.match(/(\d+)\s*x\s*diskon/i);
                    if (matchDiskon) qtyDiskon = matchDiskon[1];
                }
                $('#inv-discount-label').text(qtyDiskon + 'x Diskon Reward');
                $('#inv-discount').text('- ' + rupiahFormatter.format(discountAmount));
                $('#inv-discount-row').removeClass('hidden');
            } else {
                $('#inv-discount-row').addClass('hidden');
            }

            $('#inv-total').text(rupiahFormatter.format(order.total_harga));

            // LOGIKA DP & SISA TAGIHAN
            if (order.status_pembayaran === 'DP') {
                $('#inv-dp-amount').text('Rp ' + rupiahFormatter.format(order.paid_amount));
                $('#inv-sisa-amount').text('Rp ' + rupiahFormatter.format(order.total_harga - order.paid_amount));
                $('#inv-dp-method').text('(via ' + (order.metode_pembayaran || '-') + ')');
                
                $('#inv-dp-row').removeClass('hidden');
                $('#inv-sisa-row').removeClass('hidden');
                $('#inv-status-row').addClass('hidden');
            } else {
                $('#inv-dp-row').addClass('hidden');
                $('#inv-sisa-row').addClass('hidden');
                $('#inv-status-row').removeClass('hidden');
                $('#inv-status').text(order.status_pembayaran ? order.status_pembayaran.toUpperCase() : '-')
                                .removeClass('text-gray-800').addClass('text-green-600');
                $('#inv-method').text(order.metode_pembayaran ? 'via ' + order.metode_pembayaran : '')
                                .removeClass('text-gray-800').addClass('text-green-600');
            }

            // TEKS PESAN KLAIM REWARD DI BAWAH T&C
            let msgDiv = $('#inv-claim-msg');
            if (claimType) { 
                msgDiv.text('*** REWARD: ' + claimType.toUpperCase() + ' ***').removeClass('hidden'); 
            } else { 
                msgDiv.addClass('hidden'); 
            }
        }

        window.printInvoice = function() {
            var invoiceNo = $('#inv-no').text() || 'Invoice';
            var content = document.getElementById('invoice-content').innerHTML;
            
            // Simpan judul asli dan ubah judul dokumen agar nama file PDF sesuai nomor invoice
            // Browser modern (Chrome/Edge) mengambil nama file dari title Tab/Window utama
            document.title = invoiceNo;
            
            // Gunakan iframe agar tidak membuka tab baru dan lebih stabil di HP/Tablet
            var iframeId = 'invoice-print-frame';
            var iframe = document.getElementById(iframeId);
            if (iframe) { document.body.removeChild(iframe); }
            
            iframe = document.createElement('iframe');
            iframe.id = iframeId;
            iframe.style.cssText = 'position:fixed; right:0; bottom:0; width:0; height:0; border:0;';
            document.body.appendChild(iframe);
            
            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write('<html><head><title>' + invoiceNo + '</title>');
            doc.write('<style>body{font-family:"Helvetica","Arial",sans-serif;font-size:12px;margin:0;padding:10px;color:#000}.text-center{text-align:center}.text-right{text-align:right}.font-bold{font-weight:700}.uppercase{text-transform:uppercase}.italic{font-style:italic}.hidden{display:none}table{width:100%;border-collapse:collapse;margin-bottom:5px}td,th{vertical-align:top;padding:2px 0}.w-4\\/12{width:35%}.w-3\\/12{width:25%}.w-2\\/12{width:15%}.text-\\[10px\\]{font-size:10px}.text-\\[9px\\]{font-size:9px}.dashed-line{border-bottom:1px dashed #000}.thick-line{border-bottom:2px solid #000}.border-b{border-bottom:1px solid #000}ul{padding-left:15px;margin:5px 0}.flex{display:flex;justify-content:space-between;align-items:flex-end}</style>');
            doc.write('</head><body>' + content + '</body></html>');
            doc.close();
            
            // Paksa set title dokumen iframe juga (untuk browser yang baca title iframe)
            doc.title = invoiceNo;

            setTimeout(function() { 
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 500);
        }

        window.shareWhatsapp = function() {
            let no = $('#inv-no').text(); let total = $('#inv-total').text(); let name = $('#inv-cust-name').text();
            let text = `Halo Kak ${name}, Terima kasih telah mempercayakan sepatu kakak di Louwes Care. \nNo Nota: ${no} \nTotal: ${total}. \n\nSimpan pesan ini sebagai bukti pengambilan ya kak!`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

window.filterTreatments = function(categorySelect) {
    const row = categorySelect.closest('.treatment-row'); 
    const treatmentSelect = row.querySelector('.treatment-select'); 
    const treatmentInput = row.querySelector('.treatment-input');
    const chevron = row.querySelector('.chevron-icon');
    
    // 1. Ambil elemen kontainer warna
    const warnaContainer = row.querySelector('.warna-container');
    const inputWarna = warnaContainer.querySelector('.input-warna');

    treatmentSelect.innerHTML = '<option value="">Pilih</option>';
    const selectedCategory = categorySelect.value; 

    // 2. LOGIKA MEMUNCULKAN KOLOM WARNA
    // Kita pakai .includes() agar teksnya tidak harus 100% sama persis.
    // Selama ada kata "REPAINT" atau "CAT", input akan muncul.
    const namaKategori = selectedCategory ? selectedCategory.toUpperCase() : '';
    
    if (namaKategori.includes('REPAINT') || namaKategori.includes('CAT')) {
        warnaContainer.classList.remove('hidden');
    } else {
        warnaContainer.classList.add('hidden');
        inputWarna.value = ''; // Bersihkan input jika kategori diubah lagi
    }

    // 3. Logika bawaan untuk Dropdown/Custom
    if (selectedCategory === 'Custom') {
        treatmentSelect.classList.add('hidden'); treatmentSelect.disabled = true;
        treatmentInput.classList.remove('hidden'); treatmentInput.disabled = false; treatmentInput.focus();
        if(chevron) chevron.classList.add('hidden');
    } else {
        treatmentSelect.classList.remove('hidden'); treatmentSelect.disabled = false;
        treatmentInput.classList.add('hidden'); treatmentInput.disabled = true; treatmentInput.value = '';
        if(chevron) chevron.classList.remove('hidden');
        
        if (!selectedCategory) return;
        const filtered = rawTreatments.filter(t => t.kategori && t.kategori.trim().toLowerCase() === selectedCategory.trim().toLowerCase());
        filtered.forEach(t => { 
            const option = document.createElement('option'); 
            option.value = t.nama_treatment; 
            option.textContent = t.nama_treatment; 
            treatmentSelect.appendChild(option); 
        });
    }
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
            const mainEstimasiValue = group.querySelector('.main-estimasi-input').value;
            const newRow = container.querySelector('.treatment-row').cloneNode(true);
            newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0); newRow.querySelector('.treatment-select').innerHTML = '<option value="">Pilih Kat. Dulu</option>';
            
            // Reset Custom Input State (jika baris yang dicopy sedang mode custom)
            const tSelect = newRow.querySelector('.treatment-select');
            const tInput = newRow.querySelector('.treatment-input');
            const chevron = newRow.querySelector('.chevron-icon');
            tSelect.classList.remove('hidden'); tSelect.disabled = false;
            tInput.classList.add('hidden'); tInput.disabled = true; tInput.value = '';
            if(chevron) chevron.classList.remove('hidden');
            
            newRow.querySelectorAll('input').forEach(i => {
                if (i.classList.contains('hidden-item')) i.value = mainItemValue; 
                else if (i.classList.contains('hidden-catatan')) i.value = mainCatatanValue;
                else if (i.classList.contains('hidden-estimasi')) i.value = mainEstimasiValue;
                else { 
                    i.value = ''; 
                }
            });
            
            newRow.querySelector('.btn-remove-treatment').classList.remove('hidden'); attachEventsToTreatmentRow(newRow); container.appendChild(newRow);
        }

        window.removeTreatment = function(btn) { const row = btn.closest('.treatment-row'); if (row.parentElement.querySelectorAll('.treatment-row').length > 1) row.remove(); calculateGlobalTotal(); }
        
        window.adjustJumlah = function(delta) {
            const input = document.getElementById('inputJumlah'); const container = document.getElementById('itemsContainer'); let val = parseInt(input.value) || 1;
            if (delta > 0) { 
                val++; 
                const newGroup = container.querySelector('.item-group').cloneNode(true); 
                newGroup.querySelectorAll('input').forEach(i => { 
                    i.value = ''; 
                    if(i.classList.contains('main-estimasi-input')) {
                        i.style.color = 'transparent';
                        i.nextElementSibling.classList.remove('hidden');
                    }
                }); 
                container.appendChild(newGroup); 
            } 
            else if (val > 1) { val--; container.removeChild(container.lastElementChild); } input.value = val;
            calculateGlobalTotal();
        }

        window.openMemberModal = function() { 
            let curName = $('#nama_customer').val();
            let curHp = $('#no_hp').val();
            
            calculateGlobalTotal();
            let currentTotal = gTotal || 0;
            let currentPoin = Math.floor(currentTotal / 50000);

            let $modal = $('#memberModal');
            
            $modal.find('input[name="nama"]').val(curName);
            $modal.find('input[name="no_hp"]').val(curHp);
            
            // Isi Total Belanja (Visible & Hidden)
            $('#modalTotalDisplay').val(rupiahFormatter.format(currentTotal));
            $('#modalTotalValue').val(currentTotal);

            // Isi Poin
            $modal.find('input[name="initial_poin"]').val(currentPoin);

            document.getElementById('memberModal').classList.remove('hidden'); 
        }

        window.submitMemberAjax = function(event) {
            event.preventDefault();
            
            let form = document.getElementById('formMemberAjax');
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('members.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                success: function(response) {
                    if(response.status === 'success') {
                        // 1. Update Tampilan Badge & UI
                        $('#badge-status').text('MEMBER')
                            .removeClass('text-blue-600 bg-blue-100 border-blue-200 text-green-600 bg-green-100 border-green-200')
                            .addClass('text-pink-600 bg-pink-100 border-pink-200');

                        $('#box-point').removeClass('hidden');
                        $('#btn-daftar-member').addClass('hidden');
                        $('#box-sumber-info').addClass('hidden');

                        // 2. Update Data Poin & Hidden ID
                        $('#poin-text').text((response.poin || 0) + '/8 pts');
                        $('#is_registered_member').val(1);
                        if(response.member && response.member.id) {
                            $('#member_id').val(response.member.id);
                        }

                        // 3. Tutup Modal
                        closeMemberModal();
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyimpan member.');
                }
            });
        }

        function formatRupiahInput(input) { let val = input.value.replace(/[^0-9]/g, ''); input.value = val ? rupiahFormatter.format(val) : ''; }
    </script>
    
    <script>


    // 2. LOGIKA TRIK 2 (MENGGABUNGKAN DATA SAAT DISIMPAN)
    // Script ini berjalan otomatis saat kamu klik tombol submit / proses pembayaran
    $('form').on('submit', function() {
        
        $('.warna-container').each(function() {
            // Cek apakah kolom warna ini sedang aktif (kategori repaint sedang dipilih)
            if (!$(this).hasClass('hidden')) {
                let inputWarna = $(this).find('.input-warna').val();
                
                // Jika user mengetikkan sesuatu di kolom warna
                if (inputWarna.trim() !== '') {
                    let baris = $(this).closest('.grid');
                    
                    // Ambil elemen Layanan (baik yang select dropdown maupun input manual)
                    let selectLayanan = baris.find('select[name="kategori_treatment[]"]');
                    let inputLayanan = baris.find('input[name="kategori_treatment[]"]');
                    
                    // Cek mana yang sedang dipakai user (dropdown atau manual)
                    let layananAktif = selectLayanan.is(':not(.hidden)') ? selectLayanan : inputLayanan;
                    let nilaiLayanan = layananAktif.val();
                    
                    // GABUNGKAN TEKSNYA DISINI
                    let nilaiGabungan = nilaiLayanan + ' - Warna: ' + inputWarna;
                    
                    // Buat inputan rahasia dengan data yang sudah digabung agar masuk ke database
                    $(this).append('<input type="hidden" name="kategori_treatment[]" value="' + nilaiGabungan + '">');
                    
                    // Matikan nama input layanan yang asli agar datanya tidak ganda saat dikirim
                    layananAktif.removeAttr('name');
                }
            }
        });
    });

});
</script>
</x-app-layout>