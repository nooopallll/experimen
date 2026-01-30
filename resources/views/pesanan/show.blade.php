<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Tombol Kembali --}}
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('pesanan.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    &larr; Kembali ke Daftar
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Pesanan #{{ $order->no_invoice }}
                </h2>
            </div>

            {{-- FORM UTAMA --}}
            <form action="{{ route('pesanan.update', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Input Tersembunyi --}}
                <input type="hidden" name="nama_customer" value="{{ $order->customer->nama ?? $order->nama_customer }}">
                <input type="hidden" name="status" value="{{ $order->status_order }}">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- KARTU KIRI: Informasi Pelanggan & Status --}}
                    <div class="col-span-1 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Pelanggan</h3>
                                
                                <dl class="space-y-4 text-sm">
                                    <div>
                                        <dt class="text-gray-500">Nama Lengkap</dt>
                                        <dd class="font-semibold text-gray-900">{{ $order->customer->nama ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Nomor WhatsApp</dt>
                                        <dd class="font-semibold text-gray-900 flex items-center gap-2">
                                            {{ $order->customer->no_hp ?? '-' }}
                                            @if($order->customer)
                                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $order->customer->no_hp) }}" target="_blank" class="text-green-600 hover:text-green-800" title="Hubungi via WhatsApp">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                                </a>
                                            @endif
                                        </dd>
                                    </div>
                                    
                                    {{-- KOTAK TIPE PELANGGAN & POIN --}}
                                    <div class="grid grid-cols-2 gap-4 bg-gray-50/50 p-3 rounded-lg border border-gray-100">
                                        <div>
                                            <dt class="text-gray-500 text-xs uppercase tracking-wider font-bold mb-1">Tipe Pelanggan</dt>
                                            <dd>
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full {{ $order->tipe_customer == 'Member' ? 'bg-pink-100 text-pink-800 border border-pink-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                                    {{ $order->tipe_customer }}
                                                </span>
                                            </dd>
                                        </div>

                                        @if($order->customer && $order->customer->member)
                                        <div>
                                            <dt class="text-gray-500 text-xs uppercase tracking-wider font-bold mb-1">Poin</dt>
                                            <dd class="flex items-center gap-2">
                                                <span class="text-sm font-black text-gray-800">{{ $order->customer->member->poin }}/8</span>
                                                
                                                {{-- Tombol Claim Memanggil Modal --}}
                                                @if($order->customer->member->poin >= 8)
                                                    <button type="button" onclick="openClaimModal()" 
                                                        class="bg-green-600 hover:bg-green-700 text-white text-[10px] font-black uppercase tracking-wide px-2 py-0.5 rounded shadow-sm transition">
                                                        Claim
                                                    </button>
                                                @endif
                                            </dd>
                                        </div>
                                        @endif
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Info</h3>
                                <dl class="space-y-3 text-sm">
                                    {{-- INFO KASIR / CS MASUK --}}
                                    <div>
                                        <dt class="text-gray-500">Kasir / CS</dt>
                                        <dd class="font-semibold text-gray-900 flex items-center gap-1.5 mt-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $order->kasir ?? 'Admin / Tidak Diketahui' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Tanggal Masuk</dt>
                                        <dd class="font-semibold text-gray-900">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                                    </div>
                                    
                                    {{-- STATUS ORDER (DIPERBAIKI LOGIKA WARNANYA) --}}
                                    <div>
                                        <dt class="text-gray-500">Status Order</dt>
                                        <dd>
                                            @php
                                                $badgeClass = match($order->status_order) {
                                                    'Selesai' => 'bg-green-100 text-green-800', // Hijau
                                                    'Proses' => 'bg-yellow-100 text-yellow-800', // Kuning
                                                    'Diambil' => 'bg-blue-100 text-blue-800',   // [FIX] Biru
                                                    'Batal' => 'bg-red-100 text-red-800',       // Merah
                                                    default => 'bg-gray-100 text-gray-800'      // Abu-abu
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                {{ $order->status_order }}
                                            </span>
                                        </dd>
                                    </div>
                                    
                                    {{-- CATATAN SEKARANG BISA DI-EDIT --}}
                                    <div class="pt-2">
                                        <dt class="text-gray-500 font-bold mb-1">Catatan</dt>
                                        <dd>
                                            <textarea name="catatan" rows="3" 
                                                class="w-full text-sm bg-gray-50 border border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm transition-all p-2.5" 
                                                placeholder="Tulis catatan di sini...">{{ $order->catatan ?? '' }}</textarea>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU KANAN: Detail Item Barang (Bisa Diedit) --}}
                    <div class="col-span-1 md:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                
                                {{-- HEADER RINCIAN & DROPDOWN CS KELUAR --}}
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                    <h3 class="text-lg font-medium text-gray-900">Rincian Layanan</h3>
                                    
                                    {{-- Dropdown Pilih CS Keluar --}}
                                    <div class="flex items-center space-x-2 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                                        <label for="kasir_keluar" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            CS Keluar:
                                        </label>
                                        <select name="kasir_keluar" id="kasir_keluar" 
                                            class="text-sm border-transparent bg-transparent font-semibold text-gray-900 focus:ring-0 cursor-pointer py-1 pl-1 pr-6">
                                            <option value="" disabled @selected(is_null($order->kasir_keluar))>Pilih CS...</option>
                                            <option value="Admin 1" @selected($order->kasir_keluar == 'Admin 1')>Admin 1</option>
                                            <option value="Admin 2" @selected($order->kasir_keluar == 'Admin 2')>Admin 2</option>
                                            <option value="CS Naufal" @selected($order->kasir_keluar == 'CS Naufal')>CS Naufal</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 uppercase tracking-wider w-1/4">Item / Barang</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 uppercase tracking-wider w-1/4">Layanan</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-600 uppercase tracking-wider w-[15%]">Est. Keluar</th>
                                                <th class="px-3 py-3 text-center font-bold text-gray-600 uppercase tracking-wider w-[15%]">Status</th>
                                                <th class="px-3 py-3 text-right font-bold text-gray-600 uppercase tracking-wider w-[20%]">Harga (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @foreach($order->details as $item)
                                            <tr class="hover:bg-blue-50/30 transition-colors">
                                                
                                                {{-- KOTAK NAMA BARANG --}}
                                                <td class="p-2">
                                                    <input type="text" name="details[{{ $item->id }}][nama_barang]" value="{{ $item->nama_barang }}" 
                                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </td>

                                                {{-- KOTAK LAYANAN --}}
                                                <td class="p-2">
                                                    <input type="text" name="details[{{ $item->id }}][layanan]" value="{{ $item->layanan }}" 
                                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </td>

                                                {{-- KOTAK ESTIMASI KELUAR --}}
                                                <td class="p-2">
                                                    <input type="date" name="details[{{ $item->id }}][estimasi_keluar]" value="{{ $item->estimasi_keluar ? \Carbon\Carbon::parse($item->estimasi_keluar)->format('Y-m-d') : '' }}" 
                                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </td>

                                                {{-- KOTAK STATUS --}}
                                                <td class="p-2">
                                                    <select name="details[{{ $item->id }}][status]" 
                                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all 
                                                        @if($item->status == 'Proses') text-gray-700
                                                        @elseif($item->status == 'Selesai') text-gray-700
                                                        @elseif($item->status == 'Diambil') text-gray-700
                                                        @else text-gray-700 @endif">
                                                        <option value="Proses" {{ $item->status == 'Proses' ? 'selected' : '' }}>Proses</option>
                                                        <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                        <option value="Diambil" {{ $item->status == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                                    </select>
                                                </td>

                                                {{-- KOTAK HARGA --}}
                                                <td class="p-2">
                                                    <input type="number" name="details[{{ $item->id }}][harga]" value="{{ $item->harga }}" 
                                                        class="w-full px-3 py-2 text-right bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50/80 border-t border-gray-200">
                                            <tr>
                                                <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-600 uppercase tracking-wider">Total Tagihan</td>
                                                <td class="px-4 py-3 text-right text-lg font-black text-blue-600">
                                                    Rp{{ number_format($order->total_harga, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                {{-- FOOTER ACTION: CETAK & SIMPAN --}}
                                <div class="mt-6 flex justify-end items-center gap-3 border-t border-gray-100 pt-6">
                                    
                                    <button type="button" onclick="openModal('invoiceModal')" class="inline-flex items-center px-6 py-3 bg-gray-800 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak Invoice
                                    </button>

                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form> {{-- END FORM --}}
            
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- MODAL KLAIM REWARD                            --}}
    {{-- ============================================= --}}
    @if($order->customer && $order->customer->member)
    <div id="modal-claim-reward" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden"
         aria-labelledby="modal-title" role="dialog" aria-modal="true" onclick="closeClaimModal()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Klaim Reward</h3>
                <button type="button" onclick="closeClaimModal()" class="text-white font-bold text-2xl hover:text-gray-200">&times;</button>
            </div>
            <div class="p-6">
                <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                    <span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span>
                    <div class="text-3xl font-black text-[#3b66ff] mt-1">
                        <span id="display-poin-modal">{{ $order->customer->member->poin }}</span> pts
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-bold">Tukar 8 Poin dengan:</p>
                </div>
                <form id="formClaimReward" action="{{ route('members.claim') }}" method="POST">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $order->customer->member->id }}">
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Reward</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="diskon" class="peer sr-only" checked>
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Diskon 10k</span>
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
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg hover:bg-blue-700">Klaim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- MODAL INVOICE (DIPANGGIL TOMBOL CETAK)        --}}
    {{-- ============================================= --}}
    <div id="invoiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden">
        
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden transform transition-all scale-100 flex flex-col max-h-[90vh]">
            
            <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Pratinjau Invoice
                </h2>
                <button onclick="closeModal('invoiceModal')" class="text-gray-400 hover:text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div id="printableArea" class="p-6 text-sm text-gray-800 font-mono leading-relaxed overflow-y-auto">
                
                <div class="text-center mb-6 border-b-2 border-black pb-4">
                    <div class="flex justify-center mb-2">
                         <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-12 object-cover rounded-full">
                    </div>
                    
                    <h1 class="text-2xl font-extrabold uppercase tracking-widest">LOUWES CARE</h1>
                    <p class="text-xs text-gray-600 font-bold uppercase tracking-wide">Shoe Laundry & Care</p>
                    <p class="text-[10px] text-gray-500 mt-1">JL. Ringroad Timur No 9, Plumbon , Banguntapan , Bantul , DIY 55196</p>
                    <p class="text-[10px] text-gray-500">Instagram: @Louwes Shoes Care | WA: 081390154885</p>
                </div>

                <div class="flex justify-between items-end border-b border-black pb-2 mb-2">
                    <div class="text-xs w-full">
                        {{-- MENAMPILKAN CS MASUK & CS KELUAR --}}
                        <div class="flex justify-between">
                            <div><span class="font-bold">CS Masuk:</span> {{ $order->kasir ?? 'Admin' }}</div>
                            <div class="text-xl font-black tracking-widest">INVOICE</div>
                        </div>
                        
                        {{-- CS Keluar ditampilkan jika ada --}}
                        @if($order->kasir_keluar)
                            <div class="mt-1"><span class="font-bold">CS Keluar:</span> {{ $order->kasir_keluar }}</div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-start mb-4 text-xs">
                    <div class="w-1/2 pr-2">
                        <div class="font-bold mb-1">CUSTOMER:</div>
                        <div class="uppercase">{{ $order->customer->nama ?? 'Guest' }}</div>
                        <div>{{ $order->customer->no_hp ?? '-' }}</div>
                    </div>
                    <div class="w-1/2 pl-2 text-right">
                        <div class="font-bold mb-1">DETAILS:</div>
                        <div>No: <strong>{{ $order->no_invoice }}</strong></div>
                        <div>Date: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</div>
                    </div>
                </div>

                <table class="w-full text-[10px] border-t border-b border-black mb-4">
                    <thead>
                        <tr class="text-left font-bold border-b border-dashed border-gray-400">
                            <th class="py-2 w-1/4">ITEM</th>
                            <th class="py-2 w-1/4">CATATAN</th>
                            <th class="py-2 w-1/6">TREATMENT</th>
                            <th class="py-2 w-1/6 text-center">KELUAR</th>
                            <th class="py-2 w-1/6 text-right">HARGA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashed divide-gray-200">
                        @foreach($order->details as $detail)
                        <tr>
                            <td class="py-2 align-top font-bold">
                                {{ $detail->nama_barang }}
                                {{-- Jika item ini adalah reward/klaim --}}
                                @if($detail->klaim)
                                    <span class="text-[8px] bg-gray-200 px-1 rounded ml-1 font-bold text-blue-600">FREE</span>
                                @endif
                            </td>
                            <td class="py-2 align-top italic text-gray-600">
                                {{ $detail->catatan ?? '-' }}
                            </td>
                            <td class="py-2 align-top">
                                {{ $detail->layanan }}
                            </td>
                            <td class="py-2 align-top text-center">
                                {{ $detail->estimasi_keluar ? \Carbon\Carbon::parse($detail->estimasi_keluar)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2 align-top text-right font-bold">
                                {{ number_format($detail->harga, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end mb-6">
                    <div class="w-2/3 text-right text-xs">
                        
                        {{-- Hitung Subtotal Asli (Total Bayar + Nominal Diskon) --}}
                        @php
                            $subtotal = $order->total_harga + ($order->discount ?? 0);
                        @endphp

                        <div class="flex justify-between mb-1">
                            <span class="text-gray-600">Subtotal</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        {{-- Baris Diskon --}}
                        <div class="flex justify-between mb-1 {{ ($order->discount > 0) ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                            <span>Diskon {{ ($order->discount > 0) ? 'Member' : '' }}</span>
                            <span>- Rp {{ number_format($order->discount ?? 0, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between font-bold text-sm border-t border-black pt-1 mt-1">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        @if($order->status_pembayaran == 'DP')
                            <div class="flex justify-between text-gray-600 mt-1 italic">
                                <span>Bayar (DP)</span>
                                <span>Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-red-600 font-bold mt-1">
                                <span>Sisa Tagihan</span>
                                <span>Rp {{ number_format($order->total_harga - $order->paid_amount, 0, ',', '.') }}</span>
                            </div>
                        @elseif($order->status_pembayaran == 'Lunas')
                            <div class="flex justify-between text-green-600 mt-1 font-bold">
                                <span>LUNAS</span>
                                <span>via {{ $order->metode_pembayaran }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex justify-between items-start pt-3 border-t-2 border-dashed border-gray-400 text-[10px] mt-4">
                    <div class="w-[45%] pr-2 text-left">
                        <p class="font-bold leading-snug">
                            "Jika sudah tanggal deadline tetapi belum kami hubungi, mohon WA kami"
                        </p>
                        <div class="mt-2 text-[8px] text-gray-500">
                            <i>*Simpan nota ini sebagai bukti pengambilan</i>
                        </div>
                    </div>

                    <div class="w-[55%] pl-2 text-left border-l border-dashed border-gray-300">
                        <p class="font-bold mb-1 underline">NB (Syarat & Ketentuan):</p>
                        <ul class="list-disc list-outside ml-3 space-y-0.5 text-[9px] text-gray-700 leading-tight">
                            <li>Pengambilan barang wajib menyertakan Nota asli.</li>
                            <li>Komplain maksimal 1x24 jam setelah barang diambil.</li>
                            <li>Barang yang tidak diambil lebih dari 30 hari, kerusakan/kehilangan di luar tanggung jawab kami.</li>
                            <li>Segala resiko luntur/susut karena sifat bahan sepatu, di luar tanggung jawab kami.</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center mt-6 text-[8px] text-gray-400">
                    -- Terima Kasih --
                </div>
            </div>

            <div class="bg-gray-100 px-6 py-4 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="closeModal('invoiceModal')" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition">
                    Tutup
                </button>
                <button type="button" onclick="printInvoice()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak
                </button>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        // Script untuk Modal Claim Reward
        function openClaimModal() {
            document.getElementById('modal-claim-reward').classList.remove('hidden');
        }

        function closeClaimModal() {
            document.getElementById('modal-claim-reward').classList.add('hidden');
        }

        // Script untuk Modal & Print Invoice
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function printInvoice() {
            var printContents = document.getElementById('printableArea').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload(); // Reload untuk mengembalikan event listener
        }
    </script>
</x-app-layout>