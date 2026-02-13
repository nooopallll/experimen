<x-app-layout>
    {{-- CSS KHUSUS NOTA & FORM --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap');
        .invoice-font { font-family: 'Courier Prime', monospace; }
        
        /* Font Nota mirip gambar referensi */
        .invoice-area { font-family: 'Helvetica', 'Arial', sans-serif; }
        .dashed-line { border-bottom: 1px dashed #000; }
        .thick-line { border-bottom: 2px solid #000; }

        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        /* Hide scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    {{-- WRAPPER UTAMA DENGAN ALPINE JS --}}
    <div id="main-app" class="py-12"
         x-data="{ 
            // DATA STATE
            claimStatus: '{{ $order->klaim }}', // Data dari DB
            claimType: '',      // Pilihan user saat ini
            tempReward: 'diskon', // Pilihan sementara modal
            isModalOpen: false,   // Kontrol modal

            // --- LOGIKA MODAL ---
            openModal() { this.isModalOpen = true; },
            closeModal() { this.isModalOpen = false; },
            applyReward() {
                this.claimType = this.tempReward;
                this.closeModal();
            },

            // --- LOGIKA HARGA ---
            totalPrice: 0, 
            get discount() { 
                if (this.claimType === 'diskon') return 10000; 
                if (this.claimStatus === 'Diskon' && this.claimType === '') return 10000; 
                return 0; 
            },
            get finalBill() { return Math.max(0, this.totalPrice - this.discount); },
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
            
            {{-- HEADER --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('pesanan.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    &larr; Kembali ke Daftar
                </a>
                <div class="text-right">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Detail Pesanan #{{ $order->no_invoice }}
                    </h2>
                    <p class="text-sm text-gray-500">Total Akhir: <span class="font-bold text-blue-600 text-lg" x-text="formatRupiah(finalBill)"></span></p>
                </div>
            </div>

            {{-- FORM EDIT (AJAX) --}}
            <form id="editOrderForm" onsubmit="event.preventDefault();">
                @csrf
                @method('PATCH')

                <input type="hidden" name="nama_customer" value="{{ $order->customer->nama ?? $order->nama_customer }}">
                {{-- Input Hidden Terikat Alpine --}}
                <input type="hidden" name="claim_type" x-model="claimType"> 

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- KARTU KIRI --}}
                    <div class="col-span-1 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Pelanggan</h3>
                                <dl class="space-y-4 text-sm">
                                    <div><dt class="text-gray-500">Nama Lengkap</dt><dd class="font-semibold text-gray-900">{{ $order->customer->nama ?? '-' }}</dd></div>
                                    <div><dt class="text-gray-500">Nomor WhatsApp</dt><dd class="font-semibold text-gray-900">{{ $order->customer->no_hp ?? '-' }}</dd></div>
                                    
                                    {{-- BOX POIN & CLAIM --}}
                                    @if($order->customer && $order->customer->member)
                                    <div class="bg-blue-50 p-3 rounded border border-blue-100 flex justify-between items-center">
                                        <div><dt class="text-blue-600 text-xs font-bold uppercase">Poin Member</dt><dd class="text-lg font-black text-gray-800">{{ $order->customer->member->poin }}/8</dd></div>
                                        
                                        {{-- Tombol Muncul via Alpine --}}
                                        <template x-if="!claimStatus && !claimType && {{ $order->customer->member->poin }} >= 8">
                                            <button type="button" @click="openModal()" class="bg-green-600 hover:bg-green-700 text-white text-[10px] font-black uppercase tracking-wide px-2 py-1 rounded shadow-sm transition">Claim Reward</button>
                                        </template>
                                        
                                        {{-- Badge Status --}}
                                        <template x-if="claimStatus || claimType">
                                            <span class="text-[10px] bg-yellow-100 text-yellow-800 px-2 py-1 rounded border border-yellow-200 font-bold" x-text="'Klaim: ' + (claimType ? (claimType == 'diskon' ? 'Diskon (Baru)' : 'Parfum (Baru)') : claimStatus)"></span>
                                        </template>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        {{-- Status & Info --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Info</h3>
                                <dl class="space-y-3 text-sm">
                                    <div><dt class="text-gray-500 font-bold">CS Masuk</dt><dd class="font-semibold text-gray-900 mt-1">{{ $order->kasir ?? '-' }}</dd></div>
                                    <div>
                                        <dt class="text-gray-500 font-bold mb-1">CS Keluar (Penyerah)</dt>
                                        <dd>
                                            <select name="kasir_keluar" id="kasir_keluar" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 cursor-pointer">
                                                @foreach($karyawans as $k)
        <option value="{{ $k->nama_karyawan }}">{{ $k->nama_karyawan }}</option>
    @endforeach
                                            </select>
                                        </dd>
                                    </div>
                                    
                                    {{-- STATUS ORDER (READ ONLY - OTOMATIS) --}}
                                    <div>
                                        <dt class="text-gray-500 font-bold mb-1">Status Order</dt>
                                        <dd>
                                            @php
                                                $statusColors = [
                                                    'Baru' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                    'Proses' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                    'Selesai' => 'bg-green-100 text-green-800 border-green-200',
                                                    'Diambil' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'Batal' => 'bg-red-100 text-red-800 border-red-200',
                                                ];
                                                $currentColor = $statusColors[$order->status_order] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            
                                            {{-- Tampilan Badge --}}
                                            <div class="w-full text-sm font-bold rounded-full border {{ $currentColor }} py-2 px-3 text-center uppercase tracking-wide">
                                                {{ $order->status_order }}
                                            </div>
                                            
                                            {{-- Input Hidden agar controller tetap menerima field ini (sebagai fallback) --}}
                                            <input type="hidden" name="status" value="{{ $order->status_order }}">
                                            <p class="text-[10px] text-gray-400 mt-1 italic text-center">*Status berubah otomatis mengikuti item</p>
                                        </dd>
                                    </div>

                                    <div><dt class="text-gray-500 font-bold">Tanggal Masuk</dt><dd class="font-semibold text-gray-900 mt-1">{{ $order->created_at->format('d M Y, H:i') }}</dd></div>
                                    <div class="pt-2"><dt class="text-gray-500 font-bold mb-1">Catatan Order</dt><dd><textarea name="catatan" rows="3" class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg p-2.5">{{ $order->catatan ?? '' }}</textarea></dd></div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU KANAN: ITEM DETAILS --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Rincian Layanan</h3>
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 w-1/3">Item / Barang</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 w-1/4">Layanan</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 w-[15%]">Est. Keluar</th>
                                                <th class="px-3 py-3 text-center font-bold text-gray-600 w-[10%]">Status</th>
                                                <th class="px-3 py-3 text-right font-bold text-gray-600 w-[15%]">Harga (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @foreach($order->details as $item)
                                            <tr class="hover:bg-blue-50/30 transition-colors">
                                                <td class="p-2">
                                                    <input type="text" name="item[]" value="{{ $item->nama_barang }}" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-sm font-bold text-gray-900">
                                                    <input type="text" name="catatan_detail[]" value="{{ $item->catatan }}" class="w-full px-2 py-1 mt-1 bg-white border border-gray-200 rounded text-xs text-gray-500" placeholder="Catatan item...">
                                                </td>
                                                <td class="p-2 align-top"><input type="text" name="kategori_treatment[]" value="{{ $item->layanan }}" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-sm"></td>
                                                <td class="p-2 align-top"><input type="date" name="tanggal_keluar[]" value="{{ $item->estimasi_keluar ? \Carbon\Carbon::parse($item->estimasi_keluar)->format('Y-m-d') : '' }}" class="w-full px-2 py-1 bg-gray-50 border border-gray-200 rounded text-xs"></td>
                                                <td class="p-2 align-top">
                                                    <select name="status_detail[]" class="w-full px-1 py-1 bg-gray-50 border border-gray-200 rounded text-xs font-semibold">
                                                        @foreach(['Proses','Selesai','Diambil'] as $s)
                                                            <option value="{{ $s }}" {{ $item->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="p-2 align-top">
                                                    <input type="text" name="harga[]" value="{{ number_format($item->harga, 0, ',', '.') }}" class="input-harga w-full px-2 py-1 text-right bg-gray-50 border border-gray-200 rounded text-sm font-bold text-gray-800" oninput="formatRupiahInput(this); document.querySelector('#main-app').__x.$data.calculateUI()">
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                                    <button type="button" onclick="window.updateOrder(false)" class="inline-flex items-center px-6 py-3 bg-gray-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-gray-700 transition">Simpan</button>
                                    <button type="button" onclick="window.updateOrder(true)" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-blue-700 shadow-md transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>Simpan & Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- MODAL CLAIM REWARD (Di dalam x-data scope) --}}
            <div x-show="isModalOpen" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60" 
                 style="display: none;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                 
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 relative" @click.away="closeModal()">
                    <h3 class="text-lg font-bold mb-4">Pilih Reward</h3>
                    <div class="space-y-3 mb-6">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" value="diskon" x-model="tempReward" class="mr-3 text-[#3b66ff]">
                            <div><p class="font-bold text-sm text-gray-800">Diskon Tunai Rp 10.000</p></div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" value="parfum" x-model="tempReward" class="mr-3 text-[#3b66ff]">
                            <div><p class="font-bold text-sm text-gray-800">Free Parfum (8 Poin)</p></div>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 rounded font-bold text-gray-700">Batal</button>
                        <button type="button" @click="applyReward()" class="px-4 py-2 bg-blue-600 text-white rounded font-bold">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL INVOICE POPUP (Manual JS) --}}
        <div id="modal-invoice" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-90" style="display: none;">
            <div class="bg-white p-0 rounded-lg shadow-2xl overflow-hidden max-w-2xl w-full mx-4 relative">
                <div id="invoice-content" class="bg-white p-6 invoice-area text-xs leading-snug text-black">
                    <div class="text-center mb-2">
                        <div class="flex justify-center mb-2"><img src="https://via.placeholder.com/50" alt="Logo" class="h-12 w-12 rounded-full"></div>
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
                    {{-- TOMBOL TUTUP REDIRECT --}}
                    <button type="button" onclick="window.location.href = '{{ route('pesanan.index') }}'" class="flex-1 bg-red-100 text-red-600 py-2 rounded font-bold">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var rupiahFormatter = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 });

        // 1. UPDATE ORDER (AJAX)
        window.updateOrder = function(shouldPrint) {
            let csKeluar = document.getElementById('kasir_keluar').value;
            if(shouldPrint && !csKeluar) {
                alert("Harap pilih CS Keluar sebelum mencetak nota!");
                return;
            }

            let formData = $('#editOrderForm').serialize();
            
            $.ajax({
                url: "{{ route('pesanan.update', $order->id) }}",
                type: "POST", 
                data: formData,
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if(response.status === 'success') {
                        if(shouldPrint) {
                            populateInvoice(response);
                            document.getElementById('modal-invoice').style.display = 'flex'; // Manual Show
                        } else {
                            alert('Perubahan berhasil disimpan!');
                            window.location.href = '{{ route("pesanan.index") }}'; // Redirect jika cuma simpan
                        }
                    }
                },
                error: function(xhr) {
                    alert("Gagal update: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                }
            });
        }

        // 2. POPULATE INVOICE
        function populateInvoice(data) {
            let order = data.order; 
            let originalTotal = data.original_total; 
            let discountAmount = data.discount_amount;
            let claimType = data.claim_type;

            $('#inv-cs-masuk').text(order.kasir || '-');
            $('#inv-cs-keluar').text(order.kasir_keluar || '-');
            $('#inv-cust-name').text(order.customer.nama);
            $('#inv-cust-hp').text(order.customer.no_hp);
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
                let catatan = item.catatan ? item.catatan : '-';

                rows += `<tr>
                    <td class="align-top border-b border-gray-100 py-1 pr-1"><span class="font-bold">${item.nama_barang}</span></td>
                    <td class="align-top border-b border-gray-100 py-1 text-[10px]">${item.layanan}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-center text-[10px]">${estStr}</td>
                    <td class="align-top border-b border-gray-100 py-1 text-right">${rupiahFormatter.format(item.harga)}</td>
                </tr>`;
            });
            $('#inv-items-body').html(rows);

            $('#inv-subtotal').text(rupiahFormatter.format(originalTotal));
            $('#inv-discount').text('- ' + rupiahFormatter.format(discountAmount));
            $('#inv-total').text(rupiahFormatter.format(order.total_harga));
            $('#inv-status').text(order.status_pembayaran ? order.status_pembayaran.toUpperCase() : '-');
            
            let method = order.metode_pembayaran ? 'via ' + order.metode_pembayaran : '';
            $('#inv-method').text(method);

            let msgDiv = $('#inv-claim-msg');
            if (claimType === 'Diskon') { msgDiv.text('*** DISKON POIN DIGUNAKAN ***').removeClass('hidden'); }
            else if (claimType === 'Parfum') { msgDiv.text('*** FREE PARFUM CLAIMED ***').removeClass('hidden'); }
            else { msgDiv.addClass('hidden'); }
        }

        // 3. PRINT WINDOW
        window.printInvoice = function() {
            var content = document.getElementById('invoice-content').innerHTML;
            var mywindow = window.open('', 'PRINT', 'height=600,width=400');
            mywindow.document.write('<html><head><title>Invoice</title>');
            mywindow.document.write(`
                <style>
                    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; margin: 0; padding: 10px; color: #000; }
                    .text-center { text-align: center; } .text-right { text-align: right; }
                    .font-bold { font-weight: bold; } .uppercase { text-transform: uppercase; }
                    .italic { font-style: italic; } .hidden { display: none; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
                    td, th { vertical-align: top; padding: 2px 0; }
                    .w-4\\/12 { width: 35%; } .w-3\\/12 { width: 25%; } .w-2\\/12 { width: 15%; }
                    .text-\\[10px\\] { font-size: 10px; } .text-\\[9px\\] { font-size: 9px; }
                    .dashed-line { border-bottom: 1px dashed #000; }
                    .thick-line { border-bottom: 2px solid #000; }
                    .border-b { border-bottom: 1px solid #000; }
                    ul { padding-left: 15px; margin: 5px 0; }
                    .flex { display: flex; justify-content: space-between; align-items: flex-end; }
                </style>
            `);
            mywindow.document.write('</head><body>');
            mywindow.document.write(content);
            mywindow.document.write('</body></html>');
            mywindow.document.close(); mywindow.focus(); 
            setTimeout(function() { mywindow.print(); mywindow.close(); }, 500);
        }

        function formatRupiahInput(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) {
                input.value = new Intl.NumberFormat('id-ID').format(value);
            } else {
                input.value = '';
            }
        }
    </script>
</x-app-layout>