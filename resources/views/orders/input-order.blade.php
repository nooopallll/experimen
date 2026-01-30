<x-app-layout>
    <div class="p-6 bg-white min-h-screen">
        
        {{-- HEADER & INFO POIN --}}
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
                    <div class="text-right">
                        <p class="text-xs font-bold uppercase opacity-70">Loyalty Points</p>
                        <p class="text-xl font-black">{{ number_format($poin) }} pts</p>
                        
                        {{-- Tombol Buka Modal --}}
                        @if($poin >= 8)
                            <button type="button" onclick="bukaModalReward()" 
                                class="mt-1 text-xs bg-purple-600 text-white px-2 py-1 rounded font-bold hover:bg-purple-700 transition">
                                Klaim Reward
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- FORM UTAMA (KE STORE) --}}
        <form action="{{ route('pesanan.store') }}" method="POST" id="formOrder">
            @csrf
            
            {{-- INPUT HIDDEN PENTING (LOGIKA KLAIM) --}}
            <input type="hidden" name="tipe_customer" value="{{ $is_member ? 'Member' : ($status == 'New Customer' ? 'Baru' : 'Repeat') }}">
            <input type="hidden" name="is_claim" id="input_is_claim" value="0">
            <input type="hidden" name="reward_type" id="input_reward_type" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- KOLOM KIRI: DATA CUSTOMER --}}
                <div class="space-y-6">
                    <div class="bg-gray-200 rounded-xl p-4">
                        <label class="block text-xs text-gray-500 font-bold mb-1">Nama Customer</label>
                        <input type="text" name="nama_customer" value="{{ $customer->nama ?? '' }}" 
                               class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-semibold" 
                               placeholder="Masukkan Nama">
                    </div>

                    <div class="bg-gray-200 rounded-xl p-4">
                        <label class="block text-xs text-gray-500 font-bold mb-1">No HP / WhatsApp</label>
                        <input type="text" name="no_hp" value="{{ $no_hp }}" readonly 
                               class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-mono">
                    </div>

                    <div class="bg-gray-200 rounded-xl p-4">
                        <label class="block text-xs text-gray-500 font-bold mb-1">Alamat</label>
                        <input type="text" name="alamat" value="{{ $customer->alamat ?? '' }}" 
                               class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                    </div>
                </div>

                {{-- KOLOM KANAN: ITEM & PEMBAYARAN --}}
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Item Laundry</label>
                        <div id="items-container" class="space-y-2">
                            <div class="flex gap-2 item-row">
                                <input type="text" name="item[]" placeholder="Nama Barang" required class="w-1/2 border-gray-300 rounded text-sm">
                                <select name="kategori_treatment[]" class="w-1/4 border-gray-300 rounded text-sm">
                                    @foreach($treatments as $t)
                                        <option value="{{ $t->nama_treatment }}">{{ $t->nama_treatment }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="harga[]" placeholder="Harga" required oninput="hitungTotal()" 
                                       class="input-harga w-1/4 border-gray-300 rounded text-sm text-right">
                            </div>
                        </div>
                        <button type="button" onclick="tambahItem()" class="text-sm text-blue-600 font-bold mt-2">+ Tambah Item</button>
                    </div>

                    {{-- RINGKASAN PEMBAYARAN --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div class="flex justify-between text-sm font-bold text-gray-600 mb-1">
                            <span>Subtotal</span>
                            <span id="display-subtotal">Rp 0</span>
                        </div>
                        
                        {{-- Baris Diskon (Muncul Otomatis via JS) --}}
                        <div id="row-diskon" class="flex justify-between text-sm font-bold text-red-600 mb-1 hidden">
                            <span>Diskon Member</span>
                            <span>- Rp 35.000</span>
                        </div>

                        {{-- Baris Item Reward (Muncul Otomatis via JS) --}}
                        <div id="row-item-reward" class="flex justify-between text-sm font-bold text-blue-600 mb-1 hidden">
                            <span id="text-item-reward">Reward Item</span>
                            <span>FREE (Rp 0)</span>
                        </div>

                        <div class="flex justify-between text-xl font-black text-gray-800 border-t border-gray-300 pt-2 mt-2">
                            <span>Total</span>
                            <span id="display-total">Rp 0</span>
                        </div>
                    </div>

                    {{-- Opsi Pembayaran --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500">CS</label>
                            <select name="cs" class="w-full border-gray-300 rounded-md text-sm">
                                <option value="Admin">Admin</option>
                                <option value="Karyawan 1">Karyawan 1</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500">Metode</label>
                            <select name="metode_pembayaran" class="w-full border-gray-300 rounded-md text-sm">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOMBOL ACTION --}}
            <div class="flex justify-end space-x-4 mt-12">
                @if(!$is_member)
                    <button type="button" onclick="openMemberModal()" class="bg-gray-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-gray-900 shadow-lg flex items-center">
                        DAFTAR MEMBER
                    </button>
                @endif
                
                <button type="submit" class="bg-[#3b66ff] text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg">
                    INPUT ORDER
                </button>
            </div>
        </form>
    </div>

    {{-- MODAL PILIH REWARD (KHUSUS INPUT ORDER - TANPA FORM SUBMIT) --}}
    <div id="modalReward" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg p-6 w-96 shadow-xl transform transition-all">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Pilih Reward Member</h3>
            <p class="text-sm text-gray-600 mb-4">Poin kamu: <strong>{{ $poin ?? 0 }}</strong>. Tukar 8 Poin dengan:</p>
            
            <div class="space-y-3">
                {{-- Opsi 1: Diskon --}}
                <button type="button" onclick="pilihReward('diskon')" 
                    class="w-full flex justify-between items-center p-3 border rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
                    <div class="text-left">
                        <span class="font-bold text-gray-700 block">Diskon Rp 35.000</span>
                        <span class="text-xs text-gray-500">Potong langsung total tagihan</span>
                    </div>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded font-bold">Pilih</span>
                </button>

                {{-- Opsi 2: Parfum --}}
                <button type="button" onclick="pilihReward('Parfum Sepatu')" 
                    class="w-full flex justify-between items-center p-3 border rounded-lg hover:bg-green-50 hover:border-green-300 transition">
                    <div class="text-left">
                        <span class="font-bold text-gray-700 block">Parfum Sepatu</span>
                        <span class="text-xs text-gray-500">Barang Gratis</span>
                    </div>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-bold">Pilih</span>
                </button>

                {{-- Opsi 3: Gratis Cuci --}}
                <button type="button" onclick="pilihReward('Gratis Cuci 1 Pasang')" 
                    class="w-full flex justify-between items-center p-3 border rounded-lg hover:bg-purple-50 hover:border-purple-300 transition">
                    <div class="text-left">
                        <span class="font-bold text-gray-700 block">Gratis Cuci 1 Pasang</span>
                        <span class="text-xs text-gray-500">Layanan Gratis</span>
                    </div>
                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded font-bold">Pilih</span>
                </button>
            </div>

            <button type="button" onclick="tutupModalReward()" class="mt-6 w-full py-2 text-gray-500 hover:text-gray-800 font-bold text-sm">
                Batal
            </button>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        function bukaModalReward() {
            document.getElementById('modalReward').classList.remove('hidden');
        }
        function tutupModalReward() {
            document.getElementById('modalReward').classList.add('hidden');
        }

        function tambahItem() {
            let container = document.getElementById('items-container');
            let row = container.querySelector('.item-row').cloneNode(true);
            row.querySelectorAll('input').forEach(i => i.value = '');
            container.appendChild(row);
        }

        // --- LOGIKA PILIH REWARD (JS ONLY - TIDAK SUBMIT SERVER) ---
        function pilihReward(jenis) {
            // 1. Simpan Pilihan ke Input Hidden
            document.getElementById('input_is_claim').value = "1";
            document.getElementById('input_reward_type').value = jenis;

            // 2. Update Tampilan Visual
            if (jenis === 'diskon') {
                document.getElementById('row-diskon').classList.remove('hidden');
                document.getElementById('row-item-reward').classList.add('hidden');
            } else {
                document.getElementById('row-diskon').classList.add('hidden');
                document.getElementById('row-item-reward').classList.remove('hidden');
                document.getElementById('text-item-reward').innerText = "+ " + jenis;
            }

            // 3. Hitung Ulang Total
            hitungTotal();

            // 4. Tutup Modal
            tutupModalReward();
        }

        function hitungTotal() {
            let inputs = document.querySelectorAll('.input-harga');
            let subtotal = 0;
            inputs.forEach(input => {
                subtotal += Number(input.value) || 0;
            });

            // Cek apakah diskon aktif
            let diskon = 0;
            let isClaim = document.getElementById('input_is_claim').value;
            let rewardType = document.getElementById('input_reward_type').value;

            if (isClaim === "1" && rewardType === 'diskon') {
                diskon = 35000;
            }

            let total = subtotal - diskon;
            if (total < 0) total = 0;

            // Update Angka di Layar
            document.getElementById('display-subtotal').innerText = "Rp " + subtotal.toLocaleString('id-ID');
            document.getElementById('display-total').innerText = "Rp " + total.toLocaleString('id-ID');
        }
    </script>
</x-app-layout>