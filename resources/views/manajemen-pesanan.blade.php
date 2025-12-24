<x-app-layout>
    <div class="p-6">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-4xl font-bold text-[#7FB3D5]">Manajemen Pesanan</h1>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" placeholder="Search" class="pl-10 pr-4 py-2 border border-gray-800 rounded-full focus:ring-1 focus:ring-blue-400 w-64">
                </div>
                <button class="flex items-center space-x-2 font-semibold text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                    <span>Filter</span>
                </button>
            </div>
        </div>

        <div class="border border-black rounded-sm overflow-hidden bg-white">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black bg-gray-50">
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">ID</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Tanggal</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Nama</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Sepatu</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Treatment</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Harga</th>
                        <th class="p-4 font-bold border-r border-black text-sm uppercase tracking-wider">Status</th>
                        <th class="p-4 font-bold text-center text-sm uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="border-b border-black last:border-b-0 hover:bg-gray-50 transition">
                        <td class="p-4 border-r border-black font-mono text-sm">{{ $order->id }}</td>
                        
                        <td class="p-4 border-r border-black text-sm">
                            {{ $order->created_at->format('d M Y') }}
                        </td>
                        
                        <td class="p-4 border-r border-black font-bold text-gray-800">
                            {{ $order->nama_customer }}
                        </td>
                        
                        <td class="p-4 border-r border-black text-sm">
                            {{ $order->item }}
                        </td>
                        
                        <td class="p-4 border-r border-black text-sm">
                            {{ $order->kategori_treatment }}
                        </td>
                        
                        <td class="p-4 border-r border-black font-bold text-sm">
                            Rp {{ number_format($order->harga, 0, ',', '.') }}
                        </td>
                        
                        <td class="p-4 border-r border-black text-center">
                            <span class="bg-[#38E54D] text-black text-xs font-bold px-4 py-1 rounded-full shadow-sm border border-green-600">
                                {{ $order->status }}
                            </span>
                        </td>
                        
                        <td class="p-4">
                            <div class="flex justify-center space-x-2">
                                <button class="bg-[#4D96FF] p-2 rounded-full text-white hover:bg-blue-600 transition shadow-md" title="Edit Pesanan">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                </button>
                                
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $order->no_hp) }}" target="_blank" class="bg-[#474E51] p-2 rounded-full text-white hover:bg-green-600 transition shadow-md" title="Hubungi Customer">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-gray-500 bg-gray-50 italic">
                            Belum ada pesanan masuk. Silakan input order baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>