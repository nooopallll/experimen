<x-app-layout>
    {{-- Library SheetJS untuk Export Excel --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        @media print {
            @page { size: landscape; margin: 5mm; }
            /* Sembunyikan elemen navigasi, filter, dan tombol saat mencetak */
            nav, aside, .no-print, form { display: none !important; }
            /* Pastikan layout lebar penuh */
            .max-w-7xl { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            body { background-color: white !important; font-size: 9px; }
            .shadow-sm, .shadow-md, .border { box-shadow: none !important; border: none !important; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid black !important; padding: 3px !important; }
            .print-hidden { display: none !important; }
        }
    </style>

    <div class="py-6" x-data="{ showFilterModal: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- HEADER & FILTER SECTION --}}
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 uppercase tracking-wide">Laporan Pendapatan</h2>
                    <p class="text-sm text-gray-500">
                        Periode: 
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    </p>
                </div>

                <div class="flex items-center gap-2 no-print">
                    {{-- TOMBOL FILTER POPUP --}}
                    <button @click="showFilterModal = true" class="bg-[#003d4d] text-white px-4 py-2 rounded-lg hover:bg-cyan-800 transition shadow-sm flex items-center gap-2 h-full font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filter
                    </button>

                    {{-- TOMBOL EXPORT EXCEL --}}
                    <button onclick="exportToExcel()" class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-900 transition shadow-sm flex items-center gap-2 h-full font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l4 4a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </button>
                </div>
            </div>

            {{-- TOTAL PENDAPATAN SECTION --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 print-hidden">
                <div class="p-6 flex flex-col md:flex-row justify-between items-center">
                    <div>
                        <h3 class="text-gray-500 font-bold uppercase tracking-wider text-sm">Total Pendapatan (Terfilter)</h3>
                        <p class="text-xs text-gray-400 mt-1">Total uang masuk (Paid Amount) pada periode ini</p>
                    </div>
                    <div class="text-4xl font-black text-emerald-600 mt-4 md:mt-0">
                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            
            {{-- TABEL TRANSAKSI --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700">Rincian Transaksi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table id="table-laporan" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-3 py-3">ID</th>
                                <th class="px-3 py-3">Nama Cust</th>
                                <th class="px-3 py-3">No WA</th>
                                <th class="px-3 py-3">Item</th>
                                <th class="px-3 py-3">Catatan</th>
                                <th class="px-3 py-3">Treatment</th>
                                <th class="px-3 py-3 text-right">Harga</th>
                                <th class="px-3 py-3">Ket. Bayar</th>
                                <th class="px-3 py-3">Tgl Masuk</th>
                                <th class="px-3 py-3">Tgl Keluar</th>
                                <th class="px-3 py-3 text-center">WA Nota</th>
                                <th class="px-3 py-3 text-center">WA Ambil</th>
                                <th class="px-3 py-3">Kategori</th>
                                <th class="px-3 py-3">Tipe Cust</th>
                                <th class="px-3 py-3 text-right">Jml DP/Bayar</th>
                                <th class="px-3 py-3">Sumber Info</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-3 py-3 font-bold text-xs">{{ $order->no_invoice }}</td>
                                    <td class="px-3 py-3">{{ $order->customer->nama ?? '-' }}</td>
                                    <td class="px-3 py-3 text-xs">{{ $order->customer->no_hp ?? '-' }}</td>
                                    <td class="px-3 py-3 text-xs">
                                        @foreach($order->details as $d)
                                            <div class="whitespace-nowrap">- {{ $d->nama_barang }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-3 py-3 text-xs italic max-w-[150px] truncate hover:whitespace-normal">{{ $order->catatan ?? '-' }}</td>
                                    <td class="px-3 py-3 text-xs">
                                        @foreach($order->details as $d)
                                            <div class="whitespace-nowrap">{{ $d->layanan }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-xs">
                                        <span class="font-bold block">{{ $order->status_pembayaran }}</span>
                                        <span class="text-[10px] text-gray-500">{{ $order->metode_pembayaran ?? '-' }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-xs">{{ $order->created_at->format('d/m/y') }}</td>
                                    <td class="px-3 py-3 text-xs">
                                        @php $est = $order->details->max('estimasi_keluar'); @endphp
                                        {{ $est ? \Carbon\Carbon::parse($est)->format('d/m/y') : '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-xs">
                                        {{ $order->wa_sent_1 ? 'Sudah' : 'Belum' }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-xs">
                                        {{ $order->wa_sent_2 ? 'Sudah' : 'Belum' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs">{{ $order->tipe_customer }}</td>
                                    <td class="px-3 py-3 text-xs">{{ $order->customer->tipe ?? '-' }}</td>
                                    <td class="px-3 py-3 text-right font-bold">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-xs">{{ $order->customer->sumber_info ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="px-6 py-8 text-center text-gray-500 italic">Tidak ada data transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MODAL FILTER --}}
            <div x-show="showFilterModal" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="display: none;">
                 
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden" @click.away="showFilterModal = false">
                    {{-- Modal Header --}}
                    <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="font-bold text-xl text-gray-800 uppercase tracking-wide">Filter</h3>
                        <button @click="showFilterModal = false" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                    </div>

                    {{-- Modal Body --}}
                    <form id="filterForm" action="{{ route('owner.laporan') }}" method="GET" class="p-6 space-y-4">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Tanggal Masuk --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Masuk</label>
                                <div class="flex gap-2">
                                    <input type="date" name="tgl_masuk_start" value="{{ request('tgl_masuk_start', $startDate) }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                    <input type="date" name="tgl_masuk_end" value="{{ request('tgl_masuk_end', $endDate) }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Tanggal Keluar --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Keluar</label>
                                <div class="flex gap-2">
                                    <input type="date" name="tgl_keluar_start" value="{{ request('tgl_keluar_start') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                    <input type="date" name="tgl_keluar_end" value="{{ request('tgl_keluar_end') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Kategori Customer --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Customer</label>
                                <select name="kategori_customer" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                    <option value="">Semua Kategori</option>
                                    <option value="Member" {{ request('kategori_customer') == 'Member' ? 'selected' : '' }}>Member</option>
                                    <option value="Repeat Order" {{ request('kategori_customer') == 'Repeat Order' ? 'selected' : '' }}>Repeat Order</option>
                                    <option value="New Customer" {{ request('kategori_customer') == 'New Customer' ? 'selected' : '' }}>New Customer</option>
                                </select>
                            </div>

                            {{-- Treatment --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Treatment</label>
                                <select name="treatment" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                    <option value="">Semua Treatment</option>
                                    @foreach($treatments as $t)
                                        <option value="{{ $t->nama_treatment }}" {{ request('treatment') == $t->nama_treatment ? 'selected' : '' }}>{{ $t->nama_treatment }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Range Harga --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Range Harga (Rp)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="min_harga" placeholder="Min" value="{{ request('min_harga') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                    <span class="text-gray-400">-</span>
                                    <input type="number" name="max_harga" placeholder="Max" value="{{ request('max_harga') }}" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- Komplain Checkbox --}}
                        <div class="flex items-center gap-2 bg-red-50 p-3 rounded-lg border border-red-100">
                            <input type="checkbox" name="komplain" id="komplain" value="1" {{ request('komplain') ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <label for="komplain" class="text-sm font-bold text-red-700 select-none cursor-pointer">Tampilkan yang ada Komplain / Catatan Khusus</label>
                        </div>

                        {{-- Footer Buttons --}}
                        <div class="flex justify-between pt-4 border-t border-gray-100">
                            <button type="button" onclick="clearFilterForm()" class="px-5 py-2 text-red-500 font-bold hover:bg-red-50 rounded-lg transition flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Reset
                            </button>
                            <div class="flex gap-2">
                                <button type="button" @click="showFilterModal = false" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-lg transition">Batal</button>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 shadow-lg transition transform hover:scale-105">
                                    Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportToExcel() {
            // Ambil tabel berdasarkan ID
            var table = document.getElementById("table-laporan");
            // Konversi tabel ke worksheet SheetJS
            var wb = XLSX.utils.table_to_book(table, { sheet: "Laporan" });
            // Simpan file dengan nama Laporan_Pendapatan.xlsx
            XLSX.writeFile(wb, "Laporan_Pendapatan.xlsx");
        }

        function clearFilterForm() {
            const form = document.getElementById('filterForm');
            // Reset semua input text, number, date, select menjadi kosong
            form.querySelectorAll('input:not([type=checkbox]):not([type=radio]), select').forEach(el => el.value = '');
            // Uncheck checkbox
            form.querySelectorAll('input[type=checkbox], input[type=radio]').forEach(el => el.checked = false);
        }
    </script>
</x-app-layout>