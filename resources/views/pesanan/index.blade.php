<x-app-layout>
    {{-- Main Container --}}
    <div class="bg-white min-h-screen p-4 md:p-8">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            {{-- Title --}}
            <h1 class="text-3xl font-bold text-[#7FB3D5] self-start md:self-center">
                Manajemen Pesanan
            </h1>
            
            {{-- Search & Filter Wrapper --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                
                {{-- SEARCH FORM (Updated) --}}
                <form id="search-form" action="{{ route('pesanan.index') }}" method="GET" class="relative w-full sm:w-80 flex items-center">
                    {{-- Input Search --}}
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <input type="text" 
                               id="search-input"
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari Invoice, Nama..." 
                               class="pl-10 pr-12 py-2.5 border border-gray-300 rounded-full w-full focus:ring-blue-500 focus:border-blue-500 text-gray-700 placeholder-gray-400 shadow-sm transition"
                               autocomplete="off">
                    </div>

                    {{-- Tombol Search (Submit) --}}
                    <button type="submit" class="absolute right-1.5 top-1.5 p-1.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition shadow-sm" title="Cari">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>

                {{-- Filter Button (Opsional / Bisa difungsikan nanti) --}}
                <button class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                    <span>Filter</span>
                </button>
            </div>
        </div>

        {{-- TABLE DESKTOP VIEW --}}
        <div class="hidden lg:block border border-black rounded-sm overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead class="bg-white">
                    <tr class="border-b border-black">
                        <th class="p-4 font-bold text-gray-900 text-center">No. Invoice</th>
                        <th class="p-4 font-bold text-gray-900">Tanggal</th>
                        <th class="p-4 font-bold text-gray-900">Nama</th>
                        <th class="p-4 font-bold text-gray-900">Sepatu</th>
                        <th class="p-4 font-bold text-gray-900">Treatment</th>
                        <th class="p-4 font-bold text-gray-900">Harga</th>
                        <th class="p-4 font-bold text-gray-900 text-center">Status</th>
                        <th class="p-4 font-bold text-gray-900 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
    @forelse($orders as $order)
        <tr class="border-b border-black hover:bg-gray-50 transition duration-150">
            {{-- No. Invoice --}}
            <td class="p-4 font-bold text-blue-600 text-center align-middle text-sm">
                {{ $order->no_invoice }}
            </td>
            
            {{-- Tanggal --}}
            <td class="p-4 font-bold text-gray-900 align-middle">{{ $order->created_at->format('d/m/Y') }}</td>
            
            {{-- Nama --}}
            <td class="p-4 font-bold text-gray-900 align-middle">{{ $order->customer->nama ?? '-' }}</td>
            
            {{-- SEPATU (LOGIKA POPUP LENGKAP) --}}
            <td class="p-4 align-middle">
                @if($order->details->count() > 1)
                    {{-- JIKA BANYAK ITEM: TAMPILKAN TOMBOL POPUP --}}
                    <div class="flex flex-col items-start gap-1">
                        <span class="font-bold text-gray-900">{{ $order->details->count() }} Pasang</span>
                        <button onclick="openModal('modal-{{ $order->id }}')" 
                            class="text-xs text-blue-600 hover:text-blue-800 underline font-semibold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Kelola Item
                        </button>
                    </div>

                    {{-- === ISI POPUP ITEM (DETAIL) === --}}
                    <div id="modal-{{ $order->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeModal('modal-{{ $order->id }}')"></div>

                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                                {{-- Header Modal --}}
                                <div class="bg-blue-50 px-4 py-3 flex justify-between items-center border-b border-blue-100">
                                    <h3 class="text-lg font-bold text-gray-900">Kelola Pesanan <span class="text-blue-600">#{{ $order->no_invoice }}</span></h3>
                                    <button onclick="closeModal('modal-{{ $order->id }}')" class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>

                                {{-- Body Modal (Tabel Item) --}}
                                <div class="bg-white p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item & Layanan</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($order->details as $item)
                                                <tr>
                                                    {{-- Nama & Harga --}}
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <div class="text-sm font-bold text-gray-900">
                                                            {{ $item->nama_barang }}
                                                            @if($item->klaim) <span class="text-[9px] bg-gray-200 px-1 rounded text-blue-600 font-bold">FREE</span> @endif
                                                        </div>
                                                        <div class="text-xs text-gray-500">{{ $item->layanan }}</div>
                                                        <div class="text-xs font-mono font-bold text-blue-600">Rp{{ number_format($item->harga, 0, ',', '.') }}</div>
                                                    </td>

                                                    {{-- Dropdown Status --}}
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <form action="{{ route('pesanan.detail.update', $item->id) }}" method="POST">
                                                            @csrf
                                                            <select name="status" onchange="this.form.submit()" 
                                                                class="text-xs font-bold rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 cursor-pointer py-1 pl-2 pr-6
                                                                {{ $item->status == 'Selesai' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-yellow-50 text-yellow-700 border-yellow-200' }}">
                                                                <option value="Proses" {{ $item->status == 'Proses' ? 'selected' : '' }}>Proses</option>
                                                                <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                                <option value="Diambil" {{ $item->status == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                                            </select>
                                                        </form>
                                                    </td>

                                                    {{-- Tombol Edit & WA --}}
                                                    <td class="px-3 py-3 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-2">
                                                            {{-- Tombol Edit --}}
                                                            <a href="{{ route('pesanan.show', $order->id) }}" class="text-blue-500 hover:text-blue-700 p-1 bg-blue-50 rounded" title="Edit Detail">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            </a>
                                                            
                                                            {{-- Tombol WA Item --}}
                                                            @php
                                                                $pesanItem = "Halo kak {$order->customer->nama}, update untuk sepatu *{$item->nama_barang}* ({$item->layanan}). Status saat ini: *{$item->status}*.";
                                                                $linkWaItem = "https://wa.me/" . preg_replace('/^0/', '62', $order->customer->no_hp) . "?text=" . urlencode($pesanItem);
                                                            @endphp
                                                            <a href="{{ $linkWaItem }}" target="_blank" class="text-green-500 hover:text-green-700 p-1 bg-green-50 rounded" title="Chat WA Item Ini">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 flex justify-between items-center border-t border-gray-100 pt-2">
                                        <span class="text-sm text-gray-500">Total Tagihan:</span>
                                        <span class="text-lg font-bold text-gray-900">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 text-right">
                                    <button type="button" onclick="closeModal('modal-{{ $order->id }}')" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none">
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- JIKA CUMA 1 ITEM: TAMPIL BIASA --}}
                    <span class="font-bold text-gray-900 text-sm">
                        {{ $order->details->first()->nama_barang }}
                        @if($order->details->first()->klaim) <span class="text-[9px] bg-gray-200 px-1 rounded text-blue-600 font-bold">FREE</span> @endif
                    </span>
                @endif
            </td>
            
            {{-- TREATMENT --}}
            <td class="p-4 align-middle">
                @if($order->details->count() > 1)
                      <span class="text-xs text-blue-500 cursor-pointer hover:underline" onclick="openModal('modal-{{ $order->id }}')">
                        Lihat di Detail
                    </span>
                @else
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">
                        {{ $order->details->first()->layanan }}
                    </span>
                @endif
            </td>
            
            {{-- Harga --}}
            <td class="p-4 font-bold text-gray-900 align-middle">{{ number_format($order->total_harga, 0, ',', '.') }}</td>
            
            {{-- STATUS UTAMA (OTOMATIS BERUBAH SESUAI CONTROLLER) --}}
            <td class="p-4 text-center align-middle">
                <span class="text-xs font-bold px-4 py-1 rounded-full uppercase tracking-wide shadow-sm text-white
                    {{ $order->status_order == 'Selesai' ? 'bg-[#2ecc71]' : 'bg-yellow-500' }}">
                    {{ $order->status_order }}
                </span>
            </td>
            
            {{-- KOLOM AKSI (TOMBOL ORDER UTAMA) --}}
            <td class="p-4 align-middle">
                <div class="flex items-center justify-between gap-2">
                    {{-- WA 1 --}}
                    <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 1]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-full p-2 transition shadow-sm border {{ $order->wa_sent_1 ? 'bg-green-500 text-white border-green-600 hover:bg-green-600' : 'bg-gray-200 text-gray-500 border-gray-300 hover:bg-gray-300' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.96 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 4.5V4.5z" clip-rule="evenodd" /></svg>
                        </button>
                    </form>
                    {{-- EDIT --}}
                    <a href="{{ route('pesanan.show', $order->id) }}" class="text-[#3498db] bg-[#3498db]/10 hover:bg-[#3498db]/20 transition rounded-full p-2 border border-transparent hover:border-[#3498db]/30">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.199z" /></svg>
                    </a>
                    {{-- WA 2 --}}
                    <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 2]) }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-full p-2 transition shadow-sm border {{ $order->wa_sent_2 ? 'bg-green-500 text-white border-green-600 hover:bg-green-600' : 'bg-gray-200 text-gray-500 border-gray-300 hover:bg-gray-300' }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.96 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 4.5V4.5z" clip-rule="evenodd" /></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="text-center py-10 font-bold text-gray-500 border-b border-black">Belum ada data pesanan.</td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>

        {{-- MOBILE VIEW --}}
        <div class="lg:hidden space-y-4">
            @forelse($orders as $order)
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-xs font-bold text-blue-500 uppercase">{{ $order->no_invoice }}</span>
                            <h2 class="font-bold text-gray-800 text-lg">{{ $order->customer->nama ?? 'No Name' }}</h2>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="bg-[#2ecc71] text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">
                            {{ $order->status_order }}
                        </span>
                    </div>
                    
                    <div class="border-t border-dashed border-gray-200 py-3 space-y-2">
                        @foreach($order->details as $detail)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-800 font-medium">
                                    {{ $detail->nama_barang }}
                                    @if($detail->klaim) <span class="text-[9px] bg-gray-200 px-1 rounded text-blue-600 font-bold">FREE</span> @endif
                                </span>
                                <span class="text-gray-500 text-xs">{{ $detail->layanan }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <div class="text-lg font-bold text-gray-800">
                            Rp{{ number_format($order->total_harga, 0, ',', '.') }}
                        </div>
                        
                        {{-- Tombol Aksi Mobile --}}
                        <div class="flex space-x-2">
                            {{-- WA 1 --}}
                            <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 1]) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-full border {{ $order->wa_sent_1 ? 'bg-green-500 text-white border-green-600' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.96 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 4.5V4.5z" clip-rule="evenodd" /></svg>
                                </button>
                            </form>

                            {{-- Edit --}}
                            <a href="{{ route('pesanan.show', $order->id) }}" class="p-2 bg-blue-50 rounded-full text-blue-600 border border-blue-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                            </a>

                            {{-- WA 2 --}}
                            <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 2]) }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-full border {{ $order->wa_sent_2 ? 'bg-green-500 text-white border-green-600' : 'bg-gray-100 text-gray-400 border-gray-200' }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M1.5 4.5a3 3 0 013-3h1.372c.86 0 1.61.586 1.819 1.42l1.105 4.423a1.875 1.875 0 01-.694 1.955l-1.293.97c-.135.101-.164.249-.126.352a11.285 11.285 0 006.697 6.697c.103.038.25.009.352-.126l.97-1.293a1.875 1.875 0 011.955-.694l4.423 1.105c.834.209 1.42.96 1.42 1.82V19.5a3 3 0 01-3 3h-2.25C8.552 22.5 1.5 15.448 1.5 4.5V4.5z" clip-rule="evenodd" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 bg-white rounded-lg border border-dashed border-gray-300 text-gray-500">
                    Tidak ada data pesanan.
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        {{-- AREA HASIL PENCARIAN --}}
        <div id="search-results">
            @include('pesanan.partials.list')
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL INVOICE OTOMATIS (POPUP SETELAH INPUT ORDER)         --}}
    {{-- ========================================================== --}}
    @if(isset($popupOrder) && $popupOrder)
        <div id="invoiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300">
            
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden transform transition-all scale-100 flex flex-col max-h-[90vh]">
                
                {{-- Header Modal --}}
                <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Order Berhasil Disimpan!
                    </h2>
                    <a href="{{ route('pesanan.index') }}" class="text-gray-400 hover:text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                </div>

                {{-- Area Printable --}}
                <div id="printableArea" class="p-6 text-sm text-gray-800 font-mono leading-relaxed overflow-y-auto">
                    
                    {{-- Header Invoice --}}
                    <div class="text-center mb-6 border-b-2 border-black pb-4">
                        <div class="flex justify-center mb-2">
                             <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-12 object-cover rounded-full">
                        </div>
                        
                        <h1 class="text-2xl font-extrabold uppercase tracking-widest">SAMSI DIESEL</h1>
                        <p class="text-xs text-gray-600 font-bold uppercase tracking-wide">Shoe Laundry & Care</p>
                        <p class="text-[10px] text-gray-500 mt-1">Jl. Kebon Agung, Sleman, Yogyakarta</p>
                        <p class="text-[10px] text-gray-500">Instagram: @samsidiesel | WA: 0812-3456-7890</p>
                    </div>

                    <div class="flex justify-between items-end border-b border-black pb-2 mb-2">
                        <div class="text-xs w-full">
                            <div class="flex justify-between">
                                <div><span class="font-bold">CS Masuk:</span> {{ $popupOrder->kasir ?? 'Admin' }}</div>
                                <div class="text-xl font-black tracking-widest">INVOICE</div>
                            </div>
                            @if($popupOrder->kasir_keluar)
                                <div class="mt-1"><span class="font-bold">CS Keluar:</span> {{ $popupOrder->kasir_keluar }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between items-start mb-4 text-xs">
                        <div class="w-1/2 pr-2">
                            <div class="font-bold mb-1">CUSTOMER:</div>
                            <div class="uppercase">{{ $popupOrder->customer->nama ?? 'Guest' }}</div>
                            <div>{{ $popupOrder->customer->no_hp ?? '-' }}</div>
                        </div>
                        <div class="w-1/2 pl-2 text-right">
                            <div class="font-bold mb-1">DETAILS:</div>
                            <div>No: <strong>{{ $popupOrder->no_invoice }}</strong></div>
                            <div>Date: {{ \Carbon\Carbon::parse($popupOrder->created_at)->format('d/m/Y') }}</div>
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
                            @foreach($popupOrder->details as $detail)
                            <tr>
                                <td class="py-2 align-top font-bold">
                                    {{ $detail->nama_barang }}
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

                    {{-- === LOGIKA TOTAL & DISKON (UPDATED) === --}}
                    <div class="flex justify-end mb-6">
                        <div class="w-2/3 text-right text-xs">
                            
                            {{-- SUBTOTAL (Total + Diskon) --}}
                            @php
                                $subtotal = $popupOrder->total_harga + ($popupOrder->discount ?? 0);
                            @endphp

                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            {{-- DISKON --}}
                            <div class="flex justify-between mb-1 {{ ($popupOrder->discount > 0) ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                <span>Diskon {{ ($popupOrder->discount > 0) ? 'Member' : '' }}</span>
                                <span>- Rp {{ number_format($popupOrder->discount ?? 0, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between font-bold text-sm border-t border-black pt-1 mt-1">
                                <span>TOTAL</span>
                                <span>Rp {{ number_format($popupOrder->total_harga, 0, ',', '.') }}</span>
                            </div>

                            {{-- PEMBAYARAN --}}
                            @if($popupOrder->status_pembayaran == 'DP')
                                <div class="flex justify-between text-gray-600 mt-1 italic">
                                    <span>Bayar (DP)</span>
                                    <span>Rp {{ number_format($popupOrder->paid_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-red-600 font-bold mt-1">
                                    <span>Sisa Tagihan</span>
                                    <span>Rp {{ number_format($popupOrder->total_harga - $popupOrder->paid_amount, 0, ',', '.') }}</span>
                                </div>
                            @elseif($popupOrder->status_pembayaran == 'Lunas')
                                <div class="flex justify-between text-green-600 mt-1 font-bold">
                                    <span>LUNAS</span>
                                    <span>via {{ $popupOrder->metode_pembayaran }}</span>
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
                            <p class="font-bold mb-1 underline">NB:</p>
                            <ul class="list-disc list-outside ml-3 space-y-0.5 text-[9px] text-gray-700 leading-tight">
                                <li>Pengambilan barang wajib menyertakan Nota asli.</li>
                                <li>Komplain maksimal 1x24 jam setelah barang diambil.</li>
                                <li>Barang tidak diambil lebih dari 1 bulan, kerusakan/kehilangan bukan tanggung jawab kami.</li>
                                <li>Luntur/susut karena sifat bahan sepatu bukan tanggung jawab kami.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-center mt-6 text-[8px] text-gray-400">
                        -- Terima Kasih --
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-100 px-6 py-4 flex justify-end gap-3 shrink-0">
                    <button onclick="document.getElementById('invoiceModal').remove()" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition">
                        Tutup
                    </button>
                    <button onclick="printInvoice()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Invoice
                    </button>
                </div>
            </div>
        </div>

        <script>
            function printInvoice() {
                var printContents = document.getElementById('printableArea').innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload(); 
            }
        </script>
    @endif

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
{{-- SCRIPT SEARCH ENGINE --}}
<script>
    // 1. Fungsi Modal (Tetap Ada)
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) modal.classList.add('hidden');
    }

    // 2. Logic Search (Live + Tombol + Enter)
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const resultsContainer = document.getElementById('search-results');
        let timeout = null;

        // Fungsi Fetch Data AJAX
        function performSearch(query) {
            // Tampilkan loading (opsional, visual feedback)
            resultsContainer.style.opacity = '0.5';

            fetch(`{{ route('pesanan.index') }}?search=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                resultsContainer.style.opacity = '1';
                // Re-initialize event listeners jika ada elemen dinamis baru
            })
            .catch(error => {
                console.error('Error:', error);
                resultsContainer.style.opacity = '1';
            });
        }

        // Event A: Ketik (Live Search dengan Delay)
        searchInput.addEventListener('keyup', function () {
            clearTimeout(timeout);
            const query = this.value;
            
            timeout = setTimeout(() => {
                performSearch(query);
            }, 300); // Delay 300ms agar tidak spam request
        });

        // Event B: Submit Form (Tekan Enter atau Klik Tombol)
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Mencegah reload halaman
            clearTimeout(timeout); // Batalkan live search yang pending jika ada
            const query = searchInput.value;
            performSearch(query);
        });
    });
</script>

</x-app-layout>