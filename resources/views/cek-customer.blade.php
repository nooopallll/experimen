<x-app-layout>
    <div class="md:ml-64 flex flex-col justify-center items-center h-full bg-white px-4">
        
        <div class="w-full max-w-md flex flex-col items-center">
            {{-- Judul --}}
            <h1 class="text-2xl md:text-4xl font-bold text-[#7FB3D5] mb-6 md:mb-8 text-center">
                Cek Data Customer
            </h1>

            {{-- Box Form --}}
            <div class="w-full bg-white p-6 md:p-8 rounded-2xl shadow-xl border border-gray-200">    
                <form action="{{ route('order.check') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2 text-sm md:text-base text-left w-full">
                            Nomor WhatsApp Customer
                        </label>
                        
                        <input type="number" 
                               name="no_hp" 
                               placeholder="Contoh: 08123456789" 
                               required
                               class="w-full p-3 md:p-4 bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition outline-none text-gray-800
                               @error('no_hp') border-red-500 @enderror">
                        
                        @error('no_hp')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full bg-[#3b66ff] text-white py-3 md:py-4 rounded-xl font-bold text-base md:text-lg hover:bg-blue-700 shadow-lg transition transform active:scale-95 md:hover:-translate-y-1">
                        CARI CUSTOMER
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</x-app-layout>