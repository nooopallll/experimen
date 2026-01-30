<x-app-layout>
    <div class="p-6 bg-white min-h-screen">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b pb-4">
            <div class="flex items-center">
                <h1 class="text-3xl font-bold text-[#7FB3D5] mr-4">Input Order</h1>
                
                <span class="px-4 py-2 rounded-lg font-bold border {{ $color }} shadow-sm text-sm uppercase tracking-wide">
                    {{ $status }}
                </span>
            </div>

            @if($is_member)
                <div class="mt-4 md:mt-0 flex items-center bg-yellow-50 px-4 py-2 rounded-xl border border-yellow-200 text-yellow-700">
                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <div>
                        <p class="text-xs font-bold uppercase opacity-70">Loyalty Points</p>
                        <p class="text-xl font-black">{{ number_format($poin) }} pts</p>
                    </div>
                </div>
            @endif
        </div>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe_customer" value="{{ $is_member ? 'Member' : ($status == 'New Customer' ? 'Baru' : 'Repeat') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-200 rounded-xl p-4">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Nama Customer</label>
                    <input type="text" name="nama_customer" 
                           value="{{ $customer->nama ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-semibold" 
                           placeholder="Masukkan Nama">
                </div>

                <div class="bg-gray-200 rounded-xl p-4">
                    <label class="block text-xs text-gray-500 font-bold mb-1">No HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ $no_hp }}" readonly class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-mono">
                </div>

                <div class="bg-gray-200 rounded-xl p-4 col-span-1 md:col-span-2">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Alamat</label>
                    <input type="text" name="alamat" 
                           value="{{ $customer->alamat ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-12">
                @if(!$is_member)
                    <button type="button" onclick="openMemberModal()" class="bg-gray-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-gray-900 shadow-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        DAFTAR MEMBER
                    </button>
                @endif
                
                <button type="submit" class="bg-[#3b66ff] text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg">
                    INPUT ORDER
                </button>
            </div>
        </form>
    </div>
    
    </x-app-layout>