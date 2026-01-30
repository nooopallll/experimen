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

            {{-- 
                PENTING: Tag FORM sekarang dipindah ke sini agar membungkus 
                KARTU KIRI (Catatan) dan KARTU KANAN (Rincian) sekaligus.
            --}}
            <form action="{{ route('pesanan.update', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')

                {{-- Input Tersembunyi untuk data yang tidak diedit di halaman ini --}}
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
                                    
                                    {{-- CATATAN SEKARANG BISA DI-EDIT (Textarea) --}}
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
                                            <option value="">Pilih CS...</option>
                                            <option value="Admin 1" {{ ($order->kasir_keluar ?? '') == 'Admin 1' ? 'selected' : '' }}>Admin 1</option>
                                            <option value="Admin 2" {{ ($order->kasir_keluar ?? '') == 'Admin 2' ? 'selected' : '' }}>Admin 2</option>
                                            <option value="CS Naufal" {{ ($order->kasir_keluar ?? '') == 'CS Naufal' ? 'selected' : '' }}>CS Naufal</option>
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
                                {{-- POSISI BARU TOMBOL SIMPAN (Kanan Bawah) --}}
                                <div class="mt-6 flex justify-end border-t border-gray-100 pt-6">
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
    {{-- MODAL CLAIM REWARD                            --}}
    {{-- ============================================= --}}
    @if($order->customer && $order->customer->member)
    <div id="modal-claim-reward" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden"
         aria-labelledby="modal-title" role="dialog" aria-modal="true" onclick="closeClaimModal()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
            
            {{-- Header --}}
            <div class="bg-[#3b66ff] p-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Klaim Reward</h3>
                <button type="button" onclick="closeClaimModal()" class="text-white font-bold text-2xl hover:text-gray-200">&times;</button>
            </div>

            <div class="p-6">
                {{-- Info Poin (Otomatis dari Database) --}}
                <div class="mb-6 bg-blue-50 p-4 rounded-xl text-center border border-blue-100">
                    <span class="text-xs text-blue-600 font-bold uppercase">Poin Kamu</span>
                    <div class="text-3xl font-black text-[#3b66ff] mt-1">
                        <span id="display-poin-modal">{{ $order->customer->member->poin }}</span> pts
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-bold">Tukar 8 Poin dengan:</p>
                </div>

                {{-- FORM CLAIM --}}
                <form id="formClaimReward" action="{{ route('members.claim') }}" method="POST">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $order->customer->member->id }}">
                    <input type="hidden" name="order_id" value="{{ $order->id }}">

                    {{-- Pilihan Reward --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Reward</label>
                        <div class="grid grid-cols-2 gap-3">
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="Diskon 10k" class="peer sr-only" checked>
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Diskon 10k</span>
                                    <span class="text-xs opacity-80 block mt-1">Potongan Harga</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="Gratis Cuci 3kg" class="peer sr-only">
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Gratis 3kg</span>
                                    <span class="text-xs opacity-80 block mt-1">Layanan Regular</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="Merchandise" class="peer sr-only">
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Merchandise</span>
                                    <span class="text-xs opacity-80 block mt-1">Mug / Kaos</span>
                                </div>
                            </label>

                             <label class="cursor-pointer">
                                <input type="radio" name="reward_item" value="Voucher Next" class="peer sr-only">
                                <div class="p-3 text-center border-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                    <span class="font-bold text-sm block">Voucher</span>
                                    <span class="text-xs opacity-80 block mt-1">Next Order</span>
                                </div>
                            </label>

                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeClaimModal()" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#3b66ff] text-white rounded-lg font-bold shadow-lg hover:bg-blue-700">Klaim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- SCRIPT UNTUK MODAL KLAIM REWARD --}}
    <script>
        function openClaimModal() {
            document.getElementById('modal-claim-reward').classList.remove('hidden');
        }

        function closeClaimModal() {
            document.getElementById('modal-claim-reward').classList.add('hidden');
        }
    </script>
</x-app-layout>