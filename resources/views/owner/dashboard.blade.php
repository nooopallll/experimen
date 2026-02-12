<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- HEADER & FILTER SECTION --}}
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-gray-800">Dashboard Owner</h2>
                    <span class="text-sm text-gray-500">
                        Data Kartu: {{ now()->translatedFormat('d F Y') }} (Hari Ini)
                    </span>
                </div>

                {{-- FORM FILTER --}}
                <form action="{{ route('owner.dashboard') }}" method="GET" class="bg-white p-3 rounded-xl shadow-sm border flex flex-col md:flex-row gap-3 items-center">
                    
                    {{-- 1. PILIH TIPE FILTER --}}
                    <select name="filter_type" id="filterType" onchange="toggleFilterInputs()" 
                            class="border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-md text-sm shadow-sm">
                        <option value="harian" {{ $filterType == 'harian' ? 'selected' : '' }}>Harian / Range</option>
                        <option value="bulanan" {{ $filterType == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    </select>

                    {{-- 2. INPUT RANGE HARIAN (Muncul jika pilih Harian) --}}
                    <div id="harianInputs" class="flex items-center gap-2 {{ $filterType == 'bulanan' ? 'hidden' : '' }}">
                        <div class="flex flex-col">
                            <label class="text-[10px] text-gray-400 font-bold uppercase ml-1">Dari</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                   class="border-gray-300 p-1 text-sm rounded-md cursor-pointer focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                        <span class="text-gray-400">-</span>
                        <div class="flex flex-col">
                            <label class="text-[10px] text-gray-400 font-bold uppercase ml-1">Sampai</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                   class="border-gray-300 p-1 text-sm rounded-md cursor-pointer focus:ring-cyan-500 focus:border-cyan-500">
                        </div>
                    </div>

                    {{-- 3. INPUT BULANAN (Muncul jika pilih Bulanan) --}}
                    <div id="bulananInputs" class="flex flex-col {{ $filterType == 'harian' ? 'hidden' : '' }}">
                        <label class="text-[10px] text-gray-400 font-bold uppercase ml-1">Pilih Bulan</label>
                        <input type="month" name="bulan" value="{{ request('bulan', now()->format('Y-m')) }}" 
                               class="border-gray-300 p-1 text-sm rounded-md cursor-pointer focus:ring-cyan-500 focus:border-cyan-500">
                    </div>

                    <button type="submit" class="bg-[#003d4d] text-white p-2 rounded-md hover:bg-cyan-800 transition shadow-sm h-10 mt-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>

            {{-- LAYOUT UTAMA --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- 1. GRAFIK --}}
                <div class="lg:col-span-2 bg-white shadow-sm rounded-xl p-6 h-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800">
                            Grafik Pendapatan 
                            ({{ $filterType == 'bulanan' ? 'Per Minggu' : 'Per Hari' }})
                        </h3>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">
                            {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <div class="relative w-full" style="height: 400px;"> 
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                {{-- 2. KARTU STATISTIK (TETAP HARI INI) --}}
                <div class="flex flex-col gap-6 h-full">
                    
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-l-4 border-emerald-500 flex-1 flex flex-col justify-center">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-gray-500 text-sm font-medium">Pendapatan <span class="font-bold text-emerald-600">Hari Ini</span></div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">
                                    Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-l-4 border-blue-500 flex-1 flex flex-col justify-center">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-gray-500 text-sm font-medium">Customer <span class="font-bold text-blue-600">Hari Ini</span></div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">
                                    {{ $customerHariIni }} <span class="text-sm font-normal text-gray-500">Orang</span>
                                </div>
                            </div>
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-l-4 border-purple-500 flex-1 flex flex-col justify-center">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-gray-500 text-sm font-medium">Barang Masuk <span class="font-bold text-purple-600">Hari Ini</span></div>
                                <div class="text-2xl font-bold text-gray-800 mt-1">
                                    {{ $barangMasukHariIni }} <span class="text-sm font-normal text-gray-500">Item</span>
                                </div>
                            </div>
                            <div class="p-2 bg-purple-100 rounded-lg text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- 3. TABEL ORDER --}}
            <div class="bg-white shadow-sm rounded-xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-lg text-gray-800">Riwayat Order</h3>
                    <div class="text-xs text-gray-500">
                        Filter: {{ $filterType == 'bulanan' ? 'Bulanan' : 'Harian' }}
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3">No Nota</th>
                                <th class="px-6 py-3">Pelanggan</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3 text-center">Status Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $order->no_nota ?? 'ORDER-'.$order->id }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $order->customer ? $order->customer->nama : 'Guest' }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->customer ? $order->customer->no_hp : '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $order->created_at->format('d M H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-700">
                                        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($order->status_pembayaran == 'lunas')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Lunas</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($order->status_pembayaran) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        Tidak ada transaksi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT UNTUK TOGGLE FILTER & CHART --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fungsi Toggle Input Filter
        function toggleFilterInputs() {
            const type = document.getElementById('filterType').value;
            const harianInputs = document.getElementById('harianInputs');
            const bulananInputs = document.getElementById('bulananInputs');

            if (type === 'bulanan') {
                harianInputs.classList.add('hidden');
                bulananInputs.classList.remove('hidden');
            } else {
                harianInputs.classList.remove('hidden');
                bulananInputs.classList.add('hidden');
            }
        }

        // Render Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const labels = @json($chartLabels);
            const dataValues = @json($chartValues);
            const filterType = "{{ $filterType }}";

            new Chart(ctx, {
                type: filterType === 'bulanan' ? 'bar' : 'line', // Bulanan pakai BAR, Harian pakai LINE
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: dataValues,
                        backgroundColor: 'rgba(14, 165, 233, 0.6)',
                        borderColor: 'rgba(14, 165, 233, 1)',
                        borderWidth: 2,
                        tension: 0.3, 
                        fill: true,
                        borderRadius: 4, // Efek rounded jika Bar Chart
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [2, 4], color: '#f3f4f6' },
                            ticks: { callback: function(value) { return 'Rp ' + (value / 1000) + 'k'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
</x-app-layout>