{{-- 
    =============================================
    TAMPILAN DESKTOP (TABLE)
    Muncul hanya di layar besar (LG ke atas)
    =============================================
--}}
<div class="hidden lg:block bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Invoice</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Tanggal</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Pelanggan</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Sepatu</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Layanan</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Total</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Status</th>
                    <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-center w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-blue-50/30 transition duration-150">
                        {{-- No. Invoice --}}
                        <td class="p-4 text-center align-middle">
                            <span class="font-mono text-sm font-bold text-blue-600">{{ $order->no_invoice }}</span>
                        </td>
                        
                        {{-- Tanggal --}}
                        <td class="p-4 text-gray-700 text-sm align-middle">
                            {{ $order->created_at->format('d/m/Y') }}
                            <div class="text-[10px] text-gray-400">{{ $order->created_at->format('H:i') }}</div>
                        </td>
                        
                        {{-- Nama --}}
                        <td class="p-4 font-semibold text-gray-900 text-sm align-middle">
                            {{ $order->customer->nama ?? '-' }}
                            @if($order->tipe_customer == 'Member')
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-pink-100 text-pink-800">M</span>
                            @endif
                        </td>
                        
                        {{-- SEPATU (LOGIKA POPUP LENGKAP) --}}
                        <td class="p-4 align-middle text-sm">
                            @if($order->details->count() > 1)
                                <div class="flex flex-col items-start gap-1">
                                    <span class="font-bold text-gray-800">{{ $order->details->count() }} Pasang</span>
                                    <button onclick="openModal('modal-{{ $order->id }}')" 
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 cursor-pointer transition hover:underline">
                                        Lihat Rincian
                                    </button>
                                </div>

                                {{-- === ISI POPUP (MODAL) === --}}
                                <div id="modal-{{ $order->id }}" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    {{-- Backdrop --}}
                                    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-{{ $order->id }}')"></div>

                                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
                                        <div class="pointer-events-auto inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                                            {{-- Header Modal --}}
                                            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-b border-gray-100">
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-900">Rincian Order</h3>
                                                    <p class="text-xs text-blue-600 font-mono mt-0.5">{{ $order->no_invoice }}</p>
                                                </div>
                                                <button onclick="closeModal('modal-{{ $order->id }}')" class="bg-white rounded-full p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition shadow-sm border border-gray-200">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            {{-- Body Modal (Tabel Item) --}}
                                            <div class="bg-white p-0">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-100">
                                                        <thead class="bg-gray-50/50">
                                                            <tr>
                                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item & Layanan</th>
                                                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status Per Item</th>
                                                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-100">
                                                            @foreach($order->details as $item)
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-6 py-4">
                                                                    <div class="text-sm font-bold text-gray-900">{{ $item->nama_barang }}</div>
                                                                    <div class="text-xs text-gray-500 mt-0.5">{{ $item->layanan }}</div>
                                                                    <div class="text-xs font-bold text-blue-600 mt-1">Rp{{ number_format($item->harga, 0, ',', '.') }}</div>
                                                                </td>
                                                                <td class="px-6 py-4">
                                                                    <form action="{{ route('pesanan.detail.update', $item->id) }}" method="POST">
                                                                        @csrf
                                                                        <select name="status" onchange="this.form.submit()" 
                                                                            class="text-xs font-bold rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 cursor-pointer py-1.5 pl-3 pr-8 shadow-sm transition
                                                                            @if($item->status == 'Selesai') bg-green-50 text-green-700 border-green-200
                                                                            @elseif($item->status == 'Diambil') bg-blue-50 text-blue-700 border-blue-200
                                                                            @else bg-yellow-50 text-yellow-700 border-yellow-200 @endif">
                                                                            <option value="Proses" {{ $item->status == 'Proses' ? 'selected' : '' }}>Proses</option>
                                                                            <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                                            <option value="Diambil" {{ $item->status == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                                                        </select>
                                                                    </form>
                                                                </td>
                                                                <td class="px-6 py-4 text-center">
                                                                    <div class="flex items-center justify-center space-x-3">
                                                                        <a href="{{ route('pesanan.show', $order->id) }}" class="text-gray-400 hover:text-blue-600 transition" title="Edit Detail">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                                        </a>
                                                                        @php
                                                                            $pesanItem = "Halo kak {$order->customer->nama}, update untuk sepatu *{$item->nama_barang}* ({$item->layanan}). Status saat ini: *{$item->status}*.";
                                                                            $linkWaItem = "https://wa.me/" . preg_replace('/^0/', '62', $order->customer->no_hp) . "?text=" . urlencode($pesanItem);
                                                                        @endphp
                                                                        <a href="{{ $linkWaItem }}" target="_blank" class="text-gray-400 hover:text-green-500 transition" title="Chat WA Item Ini">
                                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t border-gray-100">
                                                    <span class="text-sm font-semibold text-gray-500">Total Tagihan</span>
                                                    <span class="text-xl font-bold text-gray-900">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="font-medium text-gray-800 text-sm">{{ $order->details->first()->nama_barang ?? '-' }}</span>
                            @endif
                        </td>
                        
                        {{-- TREATMENT --}}
                        <td class="p-4 align-middle">
                            @if($order->details->count() > 1)
                                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded-md">
                                    {{ $order->details->count() }} Layanan
                                </span>
                            @else
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded border border-gray-200 font-medium">
                                    {{ $order->details->first()->layanan ?? '-' }}
                                </span>
                            @endif
                        </td>
                        
                        {{-- Harga --}}
                        <td class="p-4 font-bold text-gray-900 align-middle text-sm">
                            Rp{{ number_format($order->total_harga, 0, ',', '.') }}
                        </td>
                        
                        {{-- STATUS UTAMA --}}
                        <td class="p-4 text-center align-middle">
                            @php
                                $statusColor = match($order->status_order) {
                                    'Selesai' => 'bg-green-500 text-white shadow-green-200', 
                                    'Diambil' => 'bg-blue-500 text-white shadow-blue-200', 
                                    'Batal'   => 'bg-red-500 text-white shadow-red-200',   
                                    default   => 'bg-yellow-400 text-yellow-900 shadow-yellow-100' 
                                };
                            @endphp
                            <span class="text-[10px] uppercase font-bold px-3 py-1 rounded-full shadow-sm tracking-wide {{ $statusColor }}">
                                {{ $order->status_order }}
                            </span>
                        </td>
                        
                        {{-- KOLOM AKSI --}}
                        <td class="p-4 align-middle">
                            <div class="flex items-center justify-center gap-2">
                                {{-- WA Masuk --}}
                                <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 1]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full transition shadow-sm border {{ $order->wa_sent_1 ? 'bg-green-50 text-green-600 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100' }}" title="WA Masuk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </button>
                                </form>
                                {{-- Edit --}}
                                <a href="{{ route('pesanan.show', $order->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 transition shadow-sm" title="Edit Pesanan">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32L19.513 8.199z" /></svg>
                                </a>
                                {{-- WA Ambil --}}
                                <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 2]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full transition shadow-sm border {{ $order->wa_sent_2 ? 'bg-green-50 text-green-600 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100' }}" title="WA Ambil">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 640 640" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M96 32C126.9 32 152 57.1 152 88C152 118.9 126.9 144 96 144C65.1 144 40 118.9 40 88C40 57.1 65.1 32 96 32zM32 235.1C32 202.5 58.5 176 91.1 176C114.6 176 136.6 187.3 150.2 206.4L198.9 274.6C204.7 282.8 214 287.7 224 288L224 192C224 174.3 238.3 160 256 160L384 160C401.7 160 416 174.3 416 192L416 288C426 287.7 435.3 282.8 441.1 274.6L489.8 206.4C503.4 187.3 525.4 176 548.9 176C581.5 176 608 202.5 608 235.1L608 336C608 366.2 593.8 394.7 569.6 412.8L492.8 470.4C484.7 476.4 480 485.9 480 496L480 576C480 593.7 465.7 608 448 608C430.3 608 416 593.7 416 576L416 496C416 465.8 430.2 437.3 454.4 419.2L496 388L496 307.9L493.2 311.8C475.2 337 446.1 352 415.1 352L384 352C383.4 352 382.7 352 382.1 351.9C381.5 351.9 380.8 352 380.2 352L259.8 352C259.2 352 258.5 352 257.9 351.9C257.3 351.9 256.6 352 256 352L224.9 352C193.9 352 164.8 337 146.8 311.8L144 307.9L144 388L185.6 419.2C209.8 437.3 224 465.8 224 496L224 576C224 593.7 209.7 608 192 608C174.3 608 160 593.7 160 576L160 496C160 485.9 155.3 476.4 147.2 470.4L70.4 412.8C46.2 394.7 32 366.2 32 336L32 235.1zM32 443.3C35.1 446 38.3 448.7 41.6 451.2L96 492L96 576C96 593.7 81.7 608 64 608C46.3 608 32 593.7 32 576L32 443.3zM600 88C600 118.9 574.9 144 544 144C513.1 144 488 118.9 488 88C488 57.1 513.1 32 544 32C574.9 32 600 57.1 600 88zM608 576C608 593.7 593.7 608 576 608C558.3 608 544 593.7 544 576L544 492L598.4 451.2C601.7 448.7 604.9 446.1 608 443.3L608 576z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="font-medium text-sm">Tidak ada pesanan ditemukan.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 
    =============================================
    TAMPILAN MOBILE & TABLET (GRID CARDS)
    Layout responsif: 1 kolom di HP, 2 kolom di Tablet
    =============================================
--}}
<div class="lg:hidden grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse($orders as $order)
        <div class="bg-white rounded-2xl shadow-[0_2px_8px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden relative flex flex-col justify-between h-full">
            
            {{-- Header Card --}}
            <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/30">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $order->no_invoice }}</span>
                        <h2 class="font-bold text-gray-900 text-lg leading-tight">{{ $order->customer->nama ?? 'Tanpa Nama' }}</h2>
                    </div>
                    @php
                        $mobileStatusColor = match($order->status_order) {
                            'Selesai' => 'bg-green-100 text-green-700 border-green-200', 
                            'Diambil' => 'bg-blue-100 text-blue-700 border-blue-200', 
                            'Batal'   => 'bg-red-100 text-red-700 border-red-200',   
                            default   => 'bg-yellow-100 text-yellow-800 border-yellow-200' 
                        };
                    @endphp
                    <span class="{{ $mobileStatusColor }} text-[10px] font-bold px-2.5 py-1 rounded-full border">
                        {{ $order->status_order }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $order->created_at->format('d M Y, H:i') }}
                </div>
            </div>
            
            {{-- Body Card (Item List) --}}
            <div class="px-5 py-4 flex-grow">
                <div class="space-y-3">
                    @foreach($order->details as $detail)
                        <div class="flex justify-between items-start text-sm group">
                            <div class="flex items-start gap-2.5">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-300 mt-1.5 group-hover:bg-blue-400 transition-colors"></div>
                                <div class="flex flex-col">
                                    <span class="text-gray-800 font-semibold leading-snug">{{ $detail->nama_barang }}</span>
                                    <span class="text-gray-500 text-xs">{{ $detail->layanan }}</span>
                                </div>
                            </div>
                            @if($detail->status == 'Selesai')
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer Card (Total & Actions) --}}
            <div class="bg-gray-50 px-5 py-4 border-t border-gray-100">
                <div class="flex justify-between items-end mb-4">
                    <span class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Tagihan</span>
                    <span class="text-xl font-bold text-gray-900 tracking-tight">Rp{{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>
                
                {{-- Tombol Aksi Mobile (Grid Besar) --}}
                <div class="grid grid-cols-4 gap-2">
                    {{-- WA Masuk --}}
                    <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 1]) }}" method="POST" class="col-span-1">
                        @csrf
                        <button type="submit" class="w-full h-10 flex items-center justify-center rounded-xl border {{ $order->wa_sent_1 ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white text-gray-400 border-gray-200' }} active:scale-95 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </button>
                    </form>

                    {{-- Edit (Tombol Utama) --}}
                    <a href="{{ route('pesanan.show', $order->id) }}" class="col-span-2 h-10 flex items-center justify-center gap-2 bg-blue-600 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-200 active:bg-blue-700 active:scale-95 transition">
                        <span>Detail & Edit</span>
                    </a>

                    {{-- WA Ambil --}}
                    <form action="{{ route('pesanan.toggle-wa', ['id' => $order->id, 'type' => 2]) }}" method="POST" class="col-span-1">
                        @csrf
                        <button type="submit" class="w-full h-10 flex items-center justify-center rounded-xl border {{ $order->wa_sent_2 ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white text-gray-400 border-gray-200' }} active:scale-95 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 640 640" xmlns="http://www.w3.org/2000/svg">
                                <path d="M96 32C126.9 32 152 57.1 152 88C152 118.9 126.9 144 96 144C65.1 144 40 118.9 40 88C40 57.1 65.1 32 96 32zM32 235.1C32 202.5 58.5 176 91.1 176C114.6 176 136.6 187.3 150.2 206.4L198.9 274.6C204.7 282.8 214 287.7 224 288L224 192C224 174.3 238.3 160 256 160L384 160C401.7 160 416 174.3 416 192L416 288C426 287.7 435.3 282.8 441.1 274.6L489.8 206.4C503.4 187.3 525.4 176 548.9 176C581.5 176 608 202.5 608 235.1L608 336C608 366.2 593.8 394.7 569.6 412.8L492.8 470.4C484.7 476.4 480 485.9 480 496L480 576C480 593.7 465.7 608 448 608C430.3 608 416 593.7 416 576L416 496C416 465.8 430.2 437.3 454.4 419.2L496 388L496 307.9L493.2 311.8C475.2 337 446.1 352 415.1 352L384 352C383.4 352 382.7 352 382.1 351.9C381.5 351.9 380.8 352 380.2 352L259.8 352C259.2 352 258.5 352 257.9 351.9C257.3 351.9 256.6 352 256 352L224.9 352C193.9 352 164.8 337 146.8 311.8L144 307.9L144 388L185.6 419.2C209.8 437.3 224 465.8 224 496L224 576C224 593.7 209.7 608 192 608C174.3 608 160 593.7 160 576L160 496C160 485.9 155.3 476.4 147.2 470.4L70.4 412.8C46.2 394.7 32 366.2 32 336L32 235.1zM32 443.3C35.1 446 38.3 448.7 41.6 451.2L96 492L96 576C96 593.7 81.7 608 64 608C46.3 608 32 593.7 32 576L32 443.3zM600 88C600 118.9 574.9 144 544 144C513.1 144 488 118.9 488 88C488 57.1 513.1 32 544 32C574.9 32 600 57.1 600 88zM608 576C608 593.7 593.7 608 576 608C558.3 608 544 593.7 544 576L544 492L598.4 451.2C601.7 448.7 604.9 446.1 608 443.3L608 576z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-12 text-center bg-white rounded-xl border border-dashed border-gray-300">
            <p class="text-gray-500 font-medium">Tidak ada data pesanan.</p>
        </div>
    @endforelse
</div>

{{-- PAGINATION --}}
<div class="mt-6">
    {{ $orders->withQueryString()->links() }}
</div>