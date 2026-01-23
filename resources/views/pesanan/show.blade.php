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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- KARTU KIRI: Informasi Pelanggan & Status --}}
                <div class="col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Data Pelanggan</h3>
                            
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-gray-500">Nama Lengkap</dt>
                                    <dd class="font-semibold text-gray-900">{{ $order->customer->nama ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Nomor WhatsApp</dt>
                                    <dd class="font-semibold text-gray-900 flex items-center gap-2">
                                        {{ $order->customer->no_hp ?? '-' }}
                                        @if($order->customer)
                                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $order->customer->no_hp) }}" target="_blank" class="text-green-600 hover:text-green-800">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                            </a>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Tipe Pelanggan</dt>
                                    <dd>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->tipe_customer == 'Member' ? 'bg-pink-100 text-pink-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $order->tipe_customer }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Info</h3>
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-gray-500">Tanggal Masuk</dt>
                                    <dd class="font-semibold text-gray-900">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Status Order</dt>
                                    <dd>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order->status_order == 'Selesai' ? 'bg-green-100 text-green-800' : 
                                              ($order->status_order == 'Proses' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $order->status_order }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Catatan</dt>
                                    <dd class="text-gray-900 italic bg-gray-50 p-2 rounded border border-gray-100 mt-1">
                                        "{{ $order->catatan ?? '-' }}"
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                {{-- KARTU KANAN: Detail Item Barang --}}
                <div class="col-span-1 md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Rincian Layanan</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item / Barang</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Keluar</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status Item</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($order->details as $item)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->nama_barang }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->layanan }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->estimasi_keluar ? \Carbon\Carbon::parse($item->estimasi_keluar)->format('d M Y') : '-' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                {{-- Form Update Status Per Item --}}
                                                <form action="{{ route('pesanan.detail.update', $item->id) }}" method="POST">
                                                    @csrf
                                                    <select name="status" onchange="this.form.submit()" 
                                                        class="text-xs rounded-full border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-1 pl-2 pr-6
                                                        {{ $item->status == 'Selesai' ? 'text-green-700 bg-green-50 border-green-200' : 'text-gray-700' }}">
                                                        <option value="Proses" {{ $item->status == 'Proses' ? 'selected' : '' }}>Proses</option>
                                                        <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                        <option value="Diambil" {{ $item->status == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                                Rp{{ number_format($item->harga, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-900">Total Tagihan</td>
                                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">
                                                Rp{{ number_format($order->total_harga, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>