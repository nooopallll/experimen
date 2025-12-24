<x-app-layout>
    <h1 class="text-4xl font-bold text-[#7FB3D5] mb-20">Input Order</h1>

    <div class="max-w-xl mx-auto mt-20">    
        <form action="{{ route('order.check') }}" method="POST">
    @csrf
    <div class="max-w-xl mx-auto mt-10">
        <label class="block text-black font-semibold mb-2">Masukkan Nomor WhatsApp</label>
        <input type="text" name="no_hp" placeholder="No HP" class="w-full p-4 bg-gray-200 border-none rounded-xl mb-4">
        <div class="flex justify-end">
            <button type="submit" class="bg-[#3b66ff] text-white px-10 py-2 rounded-xl font-semibold shadow-lg">
                Cari
            </button>
        </div>
    </div>
</form>
    </div>
</x-app-layout>