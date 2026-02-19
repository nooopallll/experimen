<x-app-layout>
    {{-- CSS KHUSUS NOTA & FORM --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');
        .invoice-font { font-family: 'Courier Prime', monospace; }
        .invoice-area { font-family: 'Helvetica', 'Arial', sans-serif; }
        .dashed-line { border-bottom: 1px dashed #000; }
        .thick-line { border-bottom: 2px solid #000; }
        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- MENGAMBIL DATA KLAIM LAMA DARI DATABASE (JIKA ADA) --}}
    @php
        $existingDiskonQty = 0;
        $existingParfumQty = 0;
        $klaimAsli = $order->klaim ?? '';

        if ($klaimAsli) {
            if (preg_match('/(\d+)\s*x\s*Diskon/i', $klaimAsli, $m)) $existingDiskonQty = (int)$m[1];
            elseif (stripos($klaimAsli, 'Diskon') !== false) $existingDiskonQty = 1;
            
            if (preg_match('/(\d+)\s*x\s*Parfum/i', $klaimAsli, $m)) $existingParfumQty = (int)$m[1];
            elseif (stripos($klaimAsli, 'Parfum') !== false) $existingParfumQty = 1;
        }

        $nominalDiskonPerItem = $nominalDiskon ?? \App\Models\Setting::getDiskonMember();
        $poinTersedia = $order->customer && $order->customer->member ? $order->customer->member->poin : 0;
    @endphp

    {{-- WRAPPER UTAMA DENGAN ALPINE JS --}}
    <div id="main-app" class="py-12"
         x-data="{ 
            claimStatusText: '{{ $klaimAsli }}', 
            
            // Qty Klaim Saat Ini (Dari DB / Yang baru di-edit)
            qtyDiskon: {{ $existingDiskonQty }},
            qtyParfum: {{ $existingParfumQty }},
            
            // Temporary variable untuk Modal
            tempQtyDiskon: 0,
            tempQtyParfum: 0,
            
            isModalOpen: false,   
            
            isPaymentModalOpen: false,
            paymentMethod: '{{ $order->metode_pembayaran ?? 'Tunai' }}',
            newStatusPembayaran: '{{ $order->status_pembayaran }}',
            payInput: '',
            submitPaidAmount: {{ $order->paid_amount ?? 0 }},

            // Data Poin & Hitungan
            poinAsli: {{ $poinTersedia }},
            // Poin yang sedang dipinjam oleh Order ini (Sudah terpotong dari total poin member)
            poinUsedInThisOrder: ({{ $existingDiskonQty }} * 8) + ({{ $existingParfumQty }} * 8),
            
            get totalPoinTersediaModal() {
                // Total poin yang bisa diutak-atik di modal = Sisa poin di dompet + Poin yang dipakai di order ini
                return this.poinAsli + this.poinUsedInThisOrder;
            },

            get poinDibutuhkan() {
                return (this.tempQtyDiskon * 8) + (this.tempQtyParfum * 8);
            },
            
            get isPoinKurang() {
                return this.poinDibutuhkan > this.totalPoinTersediaModal;
            },

            openModal() { 
                this.tempQtyDiskon = this.qtyDiskon;
                this.tempQtyParfum = this.qtyParfum;
                this.isModalOpen = true; 
            },
            closeModal() { this.isModalOpen = false; },
            
            toggleCheckbox(type) {
                if (type === 'diskon') {
                    if (this.tempQtyDiskon === 0) this.tempQtyDiskon = 1;
                    else this.tempQtyDiskon = 0;
                } else {
                    if (this.tempQtyParfum === 0) this.tempQtyParfum = 1;
                    else this.tempQtyParfum = 0;
                }
            },

            applyReward() {
                if (this.isPoinKurang) {
                    alert('Poin tidak mencukupi!');
                    return;
                }
                
                this.qtyDiskon = parseInt(this.tempQtyDiskon) || 0;
                this.qtyParfum = parseInt(this.tempQtyParfum) || 0;
                
                let textParts = [];
                if(this.qtyDiskon > 0) textParts.push(this.qtyDiskon + 'x Diskon');
                if(this.qtyParfum > 0) textParts.push(this.qtyParfum + 'x Parfum');
                this.claimStatusText = textParts.join(' & ');

                this.closeModal();
                this.calculateUI();
            },
            
            openPaymentModal() {
                this.isPaymentModalOpen = true;
                this.paymentMethod = '{{ $order->metode_pembayaran ?? 'Tunai' }}';
                if(this.newStatusPembayaran === 'DP') {
                     this.paymentMethod = 'Tunai'; // Default jika DP agar tidak rancu
                }
                this.payInput = '';
            },
            closePaymentModal() { this.isPaymentModalOpen = false; },
            
            confirmPayment() {
                let inputVal = parseInt(this.payInput.replace(/\./g, '')) || 0;
                let paymentNow = inputVal === 0 ? this.remainingBill : inputVal;
                let totalPaid = this.paidAmount + paymentNow;

                if (totalPaid >= this.finalBill) {
                    this.newStatusPembayaran = 'Lunas';
                    this.submitPaidAmount = this.finalBill;
                } else {
                    this.newStatusPembayaran = 'DP';
                    this.submitPaidAmount = totalPaid;
                }

                this.closePaymentModal();
                setTimeout(() => window.updateOrder(true), 100);
            },
            
            get change() {
                let pay = parseInt(this.payInput.replace(/\./g, '')) || 0;
                return Math.max(0, pay - this.remainingBill);
            },

            totalPrice: 0, 
            paidAmount: {{ $order->paid_amount ?? 0 }},
            get discount() { 
                return this.qtyDiskon * {{ $nominalDiskonPerItem }}; 
            },
            get finalBill() { return Math.max(0, this.totalPrice - this.discount); },
            get remainingBill() { return Math.max(0, this.finalBill - this.paidAmount); },
            
            formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); },
            
            calculateUI() {
                let total = 0;
                document.querySelectorAll('.input-harga').forEach(input => {
                    let val = parseInt(input.value.replace(/\./g, '')) || 0;
                    total += val;
                });
                this.totalPrice = total;
            },
            init() { this.calculateUI(); }
         }">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- HEADER NAVIGATION --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('pesanan.index') }}" class="group inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-sm text-gray-600 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-gray-400 group-hover:text-blue-600 transition-colors group-hover:-translate-x-1 transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali
                </a>
                <div class="text-right">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pesanan #{{ $order->no_invoice }}</h2>
                    <p class="text-sm text-gray-500">Total Akhir: <span class="font-bold text-blue-600 text-lg" x-text="formatRupiah(finalBill)"></span></p>
                </div>
            </div>

            {{-- FORM EDIT (AJAX) --}}
            <form id="editOrderForm" onsubmit="event.preventDefault();">
                @csrf
                @method('PATCH')

                <input type="hidden" name="nama_customer" value="{{ $order->customer->nama ?? $order->nama_customer }}">
                
                {{-- Input Hidden Terikat Alpine Untuk Klaim --}}
                <input type="hidden" name="claim_diskon_qty" x-model="qtyDiskon"> 
                <input type="hidden" name="claim_parfum_qty" x-model="qtyParfum"> 
                
                <input type="hidden" name="status" value="{{ $order->status_order }}">
                <input type="hidden" name="catatan" value="{{ $order->catatan }}">
                <input type="hidden" name="metode_pembayaran" x-model="paymentMethod">
                <input type="hidden" name="status_pembayaran" x-model="newStatusPembayaran">
                <input type="hidden" name="paid_amount" x-model="submitPaidAmount">

                {{-- HEADER 1: Data Pelanggan & Status Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-b border-gray-200 h-full">
                            <div class="flex items-center gap-2 mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Data Pelanggan</h3>
                                @php
                                    $tipe = $order->tipe_customer ?? 'New Customer';
                                    $colors = ['Member' => 'bg-pink-100 text-pink-800 border-pink-200', 'Repeat Order' => 'bg-green-100 text-green-800 border-green-200', 'New Customer' => 'bg-blue-100 text-blue-800 border-blue-200'];
                                    $badgeColor = $colors[$tipe] ?? $colors['New Customer'];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide border {{ $badgeColor }}">{{ $tipe }}</span>
                            </div>
                            <dl class="space-y-4 text-sm">
                                <div><dt class="text-gray-500">Nama Lengkap</dt><dd class="font-semibold text-gray-900">{{ $order->customer->nama ?? '-' }}</dd></div>
                                <div><dt class="text-gray-500">Nomor WhatsApp</dt><dd class="font-semibold text-gray-900">{{ $order->customer->no_hp ?? '-' }}</dd></div>
                                
                                {{-- BOX POIN & CLAIM (Tampilan Sama Dengan Input Order) --}}
                                @if($order->customer && $order->customer->member)
                                <div class="bg-[#E0E0E0] rounded-lg p-2 px-4 flex items-center gap-4 hover:shadow-md transition w-fit border border-gray-300 mt-2">
                                {{-- Bagian Angka Poin dengan garis pembatas di kanan --}}
                                <div class="flex flex-col border-r border-gray-400 pr-4">
                                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-tight">Point</label>
                                    <span class="text-gray-800 font-black text-base leading-none" x-text="poinAsli + '/8'"></span>
                                </div>
                                
                                {{-- Bagian Tombol dan Badge --}}
                                <div class="flex items-center gap-2">
                                    {{-- Tombol Edit Klaim / Claim Reward --}}
                                    <template x-if="totalPoinTersediaModal >= 8 || qtyDiskon > 0 || qtyParfum > 0">
                                        <button type="button" @click="openModal()" class="bg-blue-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-md shadow-sm hover:bg-blue-700 transition">
                                            <span x-text="(qtyDiskon > 0 || qtyParfum > 0) ? 'EDIT KLAIM' : 'CLAIM'"></span>
                                        </button>
                                    </template>
                                    
                                    {{-- Badge Klaim Aktif --}}
                                    <template x-if="claimStatusText">
                                        <span class="text-[9px] bg-green-500 text-white px-2 py-1 rounded-full animate-pulse shadow-sm" x-text="'Reward: ' + claimStatusText"></span>
                                    </template>
                                </div>
                            </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full">
                        <div class="p-6 bg-white border-b border-gray-200 h-full">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Info</h3>
                            <dl class="space-y-3 text-sm">
                                <div><dt class="text-gray-500 font-bold">Tanggal Masuk</dt><dd class="font-semibold text-gray-900 mt-1">{{ $order->created_at->format('d M Y, H:i') }}</dd></div>
                                <div><dt class="text-gray-500 font-bold">CS Masuk</dt><dd class="font-semibold text-gray-900 mt-1">{{ $order->kasir ?? '-' }}</dd></div>
                                <div>
                                    <dt class="text-gray-500 font-bold mb-1">CS Keluar (Penyerah)</dt>
                                    <dd>
                                        <select name="kasir_keluar" id="kasir_keluar" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 cursor-pointer">
                                            @foreach($karyawans as $k)
                                                <option value="{{ $k->nama_karyawan }}" {{ $order->kasir_keluar == $k->nama_karyawan ? 'selected' : '' }}>{{ $k->nama_karyawan }}</option>
                                            @endforeach
                                        </select>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- HEADER 2: Rincian Layanan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Rincian Layanan</h3>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-center font-bold text-gray-600 w-[5%]"><input type="checkbox" onclick="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></th>
                                        <th class="px-3 py-3 text-left font-bold text-gray-600 w-[30%]">Item / Barang</th>
                                        <th class="px-3 py-3 text-left font-bold text-gray-600 w-1/4">Layanan</th>
                                        <th class="px-3 py-3 text-left font-bold text-gray-600 w-[15%]">Est. Keluar</th>
                                        <th class="px-3 py-3 text-center font-bold text-gray-600 w-[10%]">Status</th>
                                        <th class="px-3 py-3 text-right font-bold text-gray-600 w-[15%]">Harga (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @php $groupedDetails = $order->details->groupBy(function($item) { return strtolower(trim($item->nama_barang)); }); @endphp
                                    @foreach($groupedDetails as $groupName => $details)
                                        @php $groupTotalPrice = $details->sum('harga'); $groupIds = $details->pluck('id')->implode(','); @endphp
                                        @foreach($details as $index => $item)
                                        <tr class="hover:bg-blue-50/30 transition-colors">
                                            @if($index === 0)
                                            <td class="p-2 text-center align-top pt-3" rowspan="{{ count($details) }}"><input type="checkbox" name="selected_items[]" value="{{ $groupIds }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></td>
                                            <td class="p-2 align-top" rowspan="{{ count($details) }}">
                                                <input type="text" name="item[]" value="{{ $item->nama_barang }}" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-sm font-bold text-gray-900 group-item-{{ $loop->parent->index }}" oninput="syncInputs('group-item-{{ $loop->parent->index }}', this.value)">
                                                <input type="text" name="catatan_detail[]" value="{{ $item->catatan }}" class="w-full px-2 py-1 mt-1 bg-white border border-gray-200 rounded text-xs text-gray-500 group-catatan-{{ $loop->parent->index }}" placeholder="Catatan item..." oninput="syncInputs('group-catatan-{{ $loop->parent->index }}', this.value)">
                                            </td>
                                            @else
                                                <input type="hidden" name="item[]" value="{{ $item->nama_barang }}" class="group-item-{{ $loop->parent->index }}">
                                                <input type="hidden" name="catatan_detail[]" value="{{ $item->catatan }}" class="group-catatan-{{ $loop->parent->index }}">
                                            @endif
                                            
                                            <td class="p-2 align-top">
                                                <select name="kategori_treatment[]" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-sm cursor-pointer">
                                                    @foreach($treatments as $t) <option value="{{ $t->nama_treatment }}" {{ $item->layanan == $t->nama_treatment ? 'selected' : '' }}>{{ $t->nama_treatment }}</option> @endforeach
                                                </select>
                                            </td>
                                            
                                            @if($index === 0)
                                            <td class="p-2 align-top" rowspan="{{ count($details) }}"><input type="date" name="tanggal_keluar[]" value="{{ $item->estimasi_keluar ? \Carbon\Carbon::parse($item->estimasi_keluar)->format('Y-m-d') : '' }}" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-xs" onchange="syncInputs('group-date-{{ $loop->parent->index }}', this.value)"></td>
                                            <td class="p-2 align-top" rowspan="{{ count($details) }}">
                                                <select name="status_detail[]" class="w-full px-1 py-1 bg-gray-50 border border-gray-200 rounded text-xs font-semibold" onchange="syncInputs('group-status-{{ $loop->parent->index }}', this.value)">
                                                    @foreach(['Proses','Selesai','Diambil'] as $s) <option value="{{ $s }}" {{ $item->status == $s ? 'selected' : '' }}>{{ $s }}</option> @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2 align-top" rowspan="{{ count($details) }}"><input type="text" name="harga[]" value="{{ number_format($groupTotalPrice, 0, ',', '.') }}" class="input-harga w-full px-2 py-1 text-right bg-gray-50 border border-gray-200 rounded text-sm font-bold text-gray-800" oninput="formatRupiahInput(this); document.querySelector('#main-app').__x.$data.calculateUI()"></td>
                                            @else
                                                <input type="hidden" name="tanggal_keluar[]" value="{{ $item->estimasi_keluar ? \Carbon\Carbon::parse($item->estimasi_keluar)->format('Y-m-d') : '' }}" class="group-date-{{ $loop->parent->index }}">
                                                <input type="hidden" name="status_detail[]" value="{{ $item->status }}" class="group-status-{{ $loop->parent->index }}">
                                                <input type="hidden" name="harga[]" value="0">
                                            @endif
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- SUMMARY TOTAL, DP, SISA --}}
                        <div class="flex justify-end mt-4">
                            <div class="w-full sm:w-1/2 md:w-1/3 space-y-1 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div class="flex justify-between items-center"><span class="text-sm font-bold text-gray-600 uppercase">SubTotal</span><span class="text-sm font-bold text-gray-900" x-text="formatRupiah(totalPrice)"></span></div>
                                
                                <template x-if="discount > 0">
                                    <div class="flex justify-between items-center"><span class="text-sm font-bold text-gray-600 uppercase" x-text="'Diskon (' + qtyDiskon + 'x)'"></span><span class="text-sm font-bold text-red-500" x-text="'- ' + formatRupiah(discount)"></span></div>
                                </template>
                                
                                <div class="flex justify-between items-center mt-2 border-t pt-1"><span class="text-sm font-bold text-gray-600 uppercase">Total Tagihan</span><span class="text-sm font-bold text-gray-900" x-text="formatRupiah(finalBill)"></span></div>
                                <div class="flex justify-between items-center"><span class="text-sm font-bold text-gray-600 uppercase">Telah Dibayar</span><span class="text-sm font-bold text-blue-600" x-text="formatRupiah(paidAmount)"></span></div>
                                <div class="border-t border-gray-300 my-1"></div>
                                <div class="flex justify-between items-center"><span class="text-base font-black text-gray-800 uppercase">Sisa Tagihan</span><span class="text-base font-black text-red-600" x-text="formatRupiah(remainingBill)"></span></div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" onclick="deleteSelectedItems()" class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-red-700 transition"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Hapus</button>
                            <button type="button" onclick="window.updateOrder(false)" class="inline-flex items-center px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-gray-700 transition">Simpan</button>
                            <button type="button" onclick="window.updateOrder(true)" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-blue-700 shadow-md transition"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>Cetak</button>
                            {{-- TOMBOL BAYAR LUNAS (Hanya muncul jika ada sisa tagihan) --}}
                            <template x-if="remainingBill > 0">
                                <button type="button" @click="window.autoUpdateStatus(); openPaymentModal()" class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-green-700 shadow-md transition">Bayar</button>
                            </template>
                        </div>
                    </div>
                </div>
            </form>

            {{-- MODAL CLAIM REWARD (MULTIPLE KLAIM SAMA SEPERTI INPUT ORDER) --}}
            <div x-show="isModalOpen" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60" 
                 style="display: none;"
                 x-transition.opacity>
                
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in" @click.away="closeModal()">
                    <div class="bg-[#3b66ff] p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Edit Klaim Reward</h3><button type="button" @click="closeModal()" class="text-white font-bold text-2xl">&times;</button></div>
                    <div class="p-6">
                        <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Poin Tersedia</span>
                            <div class="text-3xl font-black text-[#3b66ff] mt-1"><span x-text="totalPoinTersediaModal"></span> pts</div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            {{-- Baris Diskon --}}
                            <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 transition">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="form-checkbox text-[#3b66ff] rounded w-5 h-5 mr-3 focus:ring-blue-500" 
                                           :checked="tempQtyDiskon > 0" 
                                           @change="toggleCheckbox('diskon')">
                                    <div>
                                        <p class="font-bold text-sm text-gray-800">Diskon Rp {{ number_format($nominalDiskonPerItem, 0, ',', '.') }}</p>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase">8 Poin / Klaim</p>
                                    </div>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-600 font-bold">Qty:</span>
                                    <input type="number" x-model.number="tempQtyDiskon" min="0" class="w-14 px-2 py-1 border-gray-300 rounded-md text-center font-bold text-sm focus:ring-blue-500 shadow-sm">
                                </div>
                            </div>
                            
                            {{-- Baris Parfum --}}
                            <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 transition">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" class="form-checkbox text-[#3b66ff] rounded w-5 h-5 mr-3 focus:ring-blue-500" 
                                           :checked="tempQtyParfum > 0" 
                                           @change="toggleCheckbox('parfum')">
                                    <div>
                                        <p class="font-bold text-sm text-gray-800">Free Parfum</p>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase">8 Poin / Klaim</p>
                                    </div>
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-600 font-bold">Qty:</span>
                                    <input type="number" x-model.number="tempQtyParfum" min="0" class="w-14 px-2 py-1 border-gray-300 rounded-md text-center font-bold text-sm focus:ring-blue-500 shadow-sm">
                                </div>
                            </div>
                            
                            {{-- Peringatan Poin --}}
                            <div x-show="isPoinKurang" class="text-xs text-red-500 font-bold text-center mt-2" style="display: none;">
                                Poin tidak mencukupi untuk jumlah klaim ini!
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="closeModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button>
                            <button type="button" @click="applyReward()" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg" :class="{'opacity-50 cursor-not-allowed': isPoinKurang}" :disabled="isPoinKurang">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL PEMBAYARAN PELUNASAN --}}
            <div x-show="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60" style="display: none;" x-transition.opacity>
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.away="closePaymentModal()">
                    <div class="bg-green-600 p-4 flex justify-between items-center"><h3 class="text-white font-bold text-lg">Pelunasan Tagihan</h3><button type="button" @click="closePaymentModal()" class="text-white font-bold text-2xl">&times;</button></div>
                    <div class="p-6">
                        <div class="mb-6 bg-green-50 p-4 rounded-xl text-center border border-green-100">
                            <span class="text-xs text-green-600 font-bold uppercase">Sisa Tagihan</span>
                            <div class="text-3xl font-black text-green-600 mt-1" x-text="formatRupiah(remainingBill)"></div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="cursor-pointer"><input type="radio" name="pay_method" value="Tunai" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Tunai</div></label>
                                <label class="cursor-pointer"><input type="radio" name="pay_method" value="Transfer" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">Transfer</div></label>
                                <label class="cursor-pointer"><input type="radio" name="pay_method" value="QRIS" x-model="paymentMethod" class="peer sr-only"><div class="p-2 text-center border-2 rounded-lg peer-checked:bg-green-600 peer-checked:text-white hover:bg-gray-50 font-bold text-sm">QRIS</div></label>
                            </div>
                        </div>
                        <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Uang Diterima (Opsional)</label>
                            <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-bold">Rp</span><input type="text" x-model="payInput" class="pl-10 block w-full rounded-lg border-gray-300 font-bold text-lg" placeholder="0" oninput="formatRupiahInput(this)"></div>
                            <div class="mt-3 flex justify-between items-center pt-3 border-t border-gray-200"><span class="text-sm text-gray-500 font-bold">Kembalian:</span><span class="font-black text-green-600 text-xl" x-text="formatRupiah(change)"></span></div>
                        </div>
                        <div class="flex justify-end space-x-3"><button type="button" @click="closePaymentModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold">Batal</button><button type="button" @click="confirmPayment()" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold shadow-lg hover:bg-green-700">PROSES & CETAK</button></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL INVOICE POPUP --}}
        <div id="modal-invoice" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-90" style="display: none;">
            <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full mx-4 relative flex flex-col max-h-[90vh]">
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
                                <tr class="dashed-line" id="inv-discount-row"><td class="py-1 text-gray-600" id="inv-discount-label">Diskon</td><td class="py-1 text-right" id="inv-discount"></td></tr>
                                <tr><td class="py-2 font-bold text-sm">TOTAL</td><td class="py-2 font-bold text-sm text-right" id="inv-total"></td></tr>
                                <tr id="inv-dp-row" class="hidden"><td class="py-1 text-gray-600 font-bold">DP Dibayar <span id="inv-dp-method" class="font-normal italic text-[9px]"></span></td><td class="py-1 text-right font-bold" id="inv-dp-amount"></td></tr>
                                <tr id="inv-sisa-row" class="dashed-line hidden"><td class="py-1 text-gray-800 font-bold italic">SISA TAGIHAN</td><td class="py-1 text-right text-gray-800 font-bold italic" id="inv-sisa-amount"></td></tr>
                                <tr id="inv-status-row"><td class="py-1 font-bold text-green-600 uppercase" id="inv-status"></td><td class="py-1 text-right text-green-600 text-[10px]" id="inv-method"></td></tr>
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
                <div class="bg-gray-100 p-4 flex gap-2 no-print border-t">
                    <button type="button" onclick="window.printInvoice()" class="flex-1 bg-gray-800 text-white py-2 rounded font-bold hover:bg-black">Cetak</button>
                    <button type="button" onclick="window.location.href = '{{ route('pesanan.index') }}'" class="flex-1 bg-red-100 text-red-600 py-2 rounded font-bold">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var rupiahFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 });

        window.autoUpdateStatus = function() {
            let checkboxes = document.querySelectorAll('input[name="selected_items[]"]:checked');
            checkboxes.forEach(cb => {
                let row = cb.closest('tr');
                let select = row.querySelector('select[name="status_detail[]"]');
                if(select) { select.value = 'Diambil'; select.dispatchEvent(new Event('change')); }
            });
        }

        function syncInputs(className, value) { document.querySelectorAll('.' + className).forEach(input => { input.value = value; }); }
        function toggleSelectAll(source) { let checkboxes = document.getElementsByName('selected_items[]'); for(let i=0; i<checkboxes.length; i++) { checkboxes[i].checked = source.checked; } }

        function deleteSelectedItems() {
            let selected = [];
            document.querySelectorAll('input[name="selected_items[]"]:checked').forEach(cb => { cb.value.split(',').forEach(id => selected.push(id)); });
            if(selected.length === 0) { alert('Pilih item yang akan dihapus terlebih dahulu.'); return; }
            if(confirm('Yakin ingin menghapus ' + selected.length + ' item terpilih?')) {
                $.ajax({ url: "{{ route('pesanan.delete-items') }}", type: "POST", data: { _token: "{{ csrf_token() }}", ids: selected }, success: function(response) { window.location.reload(); } });
            }
        }

        window.updateOrder = function(shouldPrint) {
            let csKeluar = document.getElementById('kasir_keluar').value;
            if(shouldPrint && !csKeluar) { alert("Harap pilih CS Keluar sebelum mencetak nota!"); return; }

            let formData = $('#editOrderForm').serialize();
            $.ajax({
                url: "{{ route('pesanan.update', $order->id) }}", type: "POST", data: formData, dataType: 'json', headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if(response.status === 'success') {
                        if(shouldPrint) { populateInvoice(response); document.getElementById('modal-invoice').style.display = 'flex'; } 
                        else { window.location.href = '{{ route("pesanan.index") }}'; }
                    }
                },
                error: function(xhr) { alert("Gagal update: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText)); }
            });
        }

        function populateInvoice(data) {
            let order = data.order; let originalTotal = data.original_total; let discountAmount = data.discount_amount; let claimType = data.claim_type;

            $('#inv-cs-masuk').text(order.kasir || '-'); $('#inv-cs-keluar').text(order.kasir_keluar || '-');
            $('#inv-cust-name').text(order.customer.nama); $('#inv-cust-hp').text(order.customer.no_hp);
            $('#inv-no').text(order.no_invoice);
            $('#inv-date').text(new Date(order.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }));

            let rows = '';
            let groupedItems = {};
            order.details.forEach(item => {
                let key = item.nama_barang.trim().toLowerCase();
                if (!groupedItems[key]) { groupedItems[key] = { nama_barang: item.nama_barang, layanan: [], catatan: [], estimasi_keluar: item.estimasi_keluar, harga: 0 }; }
                groupedItems[key].layanan.push(item.layanan);
                if (item.catatan && item.catatan !== '-' && item.catatan.trim() !== '') groupedItems[key].catatan.push(item.catatan);
                groupedItems[key].harga += parseInt(item.harga);
                if (item.estimasi_keluar && (!groupedItems[key].estimasi_keluar || item.estimasi_keluar > groupedItems[key].estimasi_keluar)) groupedItems[key].estimasi_keluar = item.estimasi_keluar;
            });

            Object.values(groupedItems).forEach(group => {
                let estStr = group.estimasi_keluar ? new Date(group.estimasi_keluar).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-';
                let catatanStr = group.catatan.length > 0 ? group.catatan.join(', ') : '-';
                rows += `<tr>
                    <td class="align-top border-b border-gray-100 py-1 pr-1"><span class="font-bold">${group.nama_barang}</span></td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">${catatanStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">${group.layanan.join(' + ')}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-center text-[10px]">${estStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-right">${rupiahFormatter.format(group.harga)}</td>
                </tr>`;
            });
            
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
            $('#inv-subtotal').text(rupiahFormatter.format(originalTotal));

            if (discountAmount > 0) {
                let qtyDiskon = 1;
                if (claimType) { let matchDiskon = claimType.match(/(\d+)\s*x\s*diskon/i); if (matchDiskon) qtyDiskon = matchDiskon[1]; }
                $('#inv-discount-label').text(qtyDiskon + 'x Diskon Reward');
                $('#inv-discount').text('- ' + rupiahFormatter.format(discountAmount));
                $('#inv-discount-row').removeClass('hidden');
            } else { $('#inv-discount-row').addClass('hidden'); }

            $('#inv-total').text(rupiahFormatter.format(order.total_harga));
            
            if (order.status_pembayaran === 'DP') {
                $('#inv-dp-amount').text('Rp ' + rupiahFormatter.format(order.paid_amount));
                $('#inv-sisa-amount').text('Rp ' + rupiahFormatter.format(order.total_harga - order.paid_amount));
                $('#inv-dp-method').text('(via ' + (order.metode_pembayaran || '-') + ')');
                $('#inv-dp-row').removeClass('hidden'); $('#inv-sisa-row').removeClass('hidden'); $('#inv-status-row').addClass('hidden');
            } else {
                $('#inv-dp-row').addClass('hidden'); $('#inv-sisa-row').addClass('hidden'); $('#inv-status-row').removeClass('hidden');
                $('#inv-status').text(order.status_pembayaran ? order.status_pembayaran.toUpperCase() : '-').removeClass('text-gray-800').addClass('text-green-600');
                $('#inv-method').text(order.metode_pembayaran ? 'via ' + order.metode_pembayaran : '').removeClass('text-gray-800').addClass('text-green-600');
            }

            let msgDiv = $('#inv-claim-msg');
            if (claimType) { msgDiv.text('*** REWARD: ' + claimType.toUpperCase() + ' ***').removeClass('hidden'); } else { msgDiv.addClass('hidden'); }
        }

        window.printInvoice = function() {
            var invNo = $('#inv-no').text().trim(); var custName = $('#inv-cust-name').text().trim();
            document.title = invNo + ' - ' + custName;
            var iframeId = 'invoice-print-frame'; var iframe = document.getElementById(iframeId);
            if (iframe) { document.body.removeChild(iframe); }
            iframe = document.createElement('iframe'); iframe.id = iframeId; iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
            document.body.appendChild(iframe);
            var doc = iframe.contentWindow.document; doc.open();
            doc.write('<html><head><title>' + document.title + '</title>');
            doc.write('<style>body{font-family:"Helvetica","Arial",sans-serif;font-size:12px;margin:0;padding:10px;color:#000}.text-center{text-align:center}.text-right{text-align:right}.font-bold{font-weight:700}.uppercase{text-transform:uppercase}.italic{font-style:italic}.hidden{display:none}table{width:100%;border-collapse:collapse;margin-bottom:5px}td,th{vertical-align:top;padding:2px 0}.w-4\\/12{width:35%}.w-3\\/12{width:25%}.w-2\\/12{width:15%}.text-\\[10px\\]{font-size:10px}.text-\\[9px\\]{font-size:9px}.dashed-line{border-bottom:1px dashed #000}.thick-line{border-bottom:2px solid #000}.border-b{border-bottom:1px solid #000}ul{padding-left:15px;margin:5px 0}.flex{display:flex;justify-content:space-between;align-items:flex-end}</style>');
            doc.write('</head><body>' + document.getElementById('invoice-content').innerHTML + '</body></html>'); doc.close();
            doc.title = document.title;
            setTimeout(function() { iframe.contentWindow.focus(); iframe.contentWindow.print(); }, 500);
        }

        function formatRupiahInput(input) { let value = input.value.replace(/[^0-9]/g, ''); if (value) { input.value = new Intl.NumberFormat('id-ID').format(value); } else { input.value = ''; } }
    </script>
</x-app-layout>