<x-app-layout>
    <div class="p-6 bg-white min-h-screen">
        <div class="flex items-center mb-10">
            <button class="mr-4 p-2 bg-gray-200 rounded-lg">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <h1 class="text-4xl font-bold text-[#7FB3D5]">
                Input Order <span class="{{ $color }} ml-4 text-2xl">{{ $status }}</span>
            </h1>
        </div>

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe_customer" value="{{ $status }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-gray-200 rounded-xl p-4">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Nama Customer</label>
                    <input type="text" name="nama_customer" value="{{ $nama }}" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 placeholder-gray-400" placeholder="Masukkan Nama">
                </div>

                <div class="bg-gray-200 rounded-xl p-4 flex">
                    <div class="border-r border-gray-400 pr-4 mr-4 flex items-center">
                        <label class="text-xs text-gray-500 font-bold">No HP</label>
                    </div>
                    <input type="text" name="no_hp" value="{{ $no_hp }}" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                </div>

                <div class="bg-gray-200 rounded-xl p-4 flex justify-between items-center">
                    <label class="text-xs text-gray-500 font-bold">Jumlah Item</label>
                    <div class="flex items-center space-x-6">
                        <button type="button" onclick="removeItem()" class="text-xl font-bold text-gray-600 hover:text-red-500">-</button>
                        
                        <span id="counter-display" class="font-bold text-lg">1</span>
                        
                        <button type="button" onclick="addItem()" class="text-xl font-bold text-gray-600 hover:text-blue-500">+</button>
                    </div>
                </div>

                <div class="bg-gray-200 rounded-xl p-4 relative">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Cs</label>
                    <select name="cs" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 appearance-none">
                        <option value="Admin 1">Admin 1</option>
                        <option value="Admin 2">Admin 2</option>
                    </select>
                </div>

                <div id="items-container" class="col-span-1 md:col-span-2 space-y-4">
                    
                    <div class="item-row grid grid-cols-2 md:grid-cols-5 gap-4 bg-white p-2 rounded-xl border border-gray-100 shadow-sm">
                        
                        <div class="bg-gray-200 rounded-xl p-3">
                            <label class="block text-xs text-gray-500 font-bold mb-1">Item</label>
                            <input type="text" name="item[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm" placeholder="Nama Barang">
                        </div>
                        
                        <div class="bg-gray-200 rounded-xl p-3">
                            <label class="block text-xs text-gray-500 font-bold mb-1">Kategori</label>
                            <select name="kategori_treatment[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm">
                                <option value="Deep Clean">Deep Clean</option>
                                <option value="Fast Clean">Fast Clean</option>
                                <option value="Repaint">Repaint</option>
                            </select>
                        </div>
                        
                        <div class="bg-gray-200 rounded-xl p-3">
                            <label class="block text-xs text-gray-500 font-bold mb-1">Tgl Keluar</label>
                            <input type="date" name="tanggal_keluar[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm">
                        </div>
                        
                        <div class="bg-gray-200 rounded-xl p-3">
                            <label class="block text-xs text-gray-500 font-bold mb-1">Harga</label>
                            <input type="number" name="harga[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm" placeholder="0">
                        </div>
                        
                        <div class="bg-gray-200 rounded-xl p-3">
                            <label class="block text-xs text-gray-500 font-bold mb-1">Catatan</label>
                            <input type="text" name="catatan[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm" placeholder="...">
                        </div>
                    </div>

                </div>
                <div class="bg-gray-200 rounded-xl p-4">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Pembayaran</label>
                    <input type="text" name="pembayaran" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                </div>

                <div class="bg-gray-200 rounded-xl p-4">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Tipe Customer</label>
                    <input type="text" readonly value="{{ $status }}" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                </div>

                <div class="bg-gray-200 rounded-xl p-4 md:w-1/2">
                    <label class="block text-xs text-gray-500 font-bold mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                        <option>Instagram</option>
                        <option>Teman</option>
                        <option>Google Maps</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-12">
                <button type="button" class="bg-[#3b66ff] text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg">MEMBER</button>
                <button type="submit" class="bg-[#3b66ff] text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg">INPUT</button>
            </div>
        </form>
    </div>

    <script>
        function addItem() {
            // Ambil elemen container dan counter
            const container = document.getElementById('items-container');
            const counterDisplay = document.getElementById('counter-display');
            
            // Ambil angka saat ini
            let currentCount = parseInt(counterDisplay.innerText);

            // Clone (copy) baris pertama yang ada di dalam container
            // true berarti mengcopy semua anak elemen di dalamnya juga
            const firstRow = container.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);

            // Kosongkan nilai input di baris baru agar bersih
            const inputs = newRow.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = ''; 
            });

            // Tambahkan baris baru ke bawah container
            container.appendChild(newRow);

            // Update angka display
            counterDisplay.innerText = currentCount + 1;
        }

        function removeItem() {
            const container = document.getElementById('items-container');
            const counterDisplay = document.getElementById('counter-display');
            let currentCount = parseInt(counterDisplay.innerText);

            // Pastikan tidak menghapus jika hanya sisa 1 baris
            if (currentCount > 1) {
                container.removeChild(container.lastElementChild); // Hapus elemen terakhir
                counterDisplay.innerText = currentCount - 1;
            }
        }
    </script>
</x-app-layout>