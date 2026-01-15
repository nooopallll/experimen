<x-app-layout>
    <div class="min-h-screen bg-white p-4 md:p-8">
        
        {{-- HEADER --}}
        <div class="flex items-center gap-4 mb-10">
            <h1 class="text-4xl font-bold text-[#7FB3D5]">Input Order</h1>
            <span id="badge-status" class="text-xl font-bold {{ $color ?? 'text-blue-500' }}">
                {{ $status ?? 'Baru' }}
            </span>
        </div>

        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf
            
            {{-- HIDDEN INPUTS --}}
            <input type="hidden" name="tipe_customer" id="tipe_customer_input" value="{{ $status ?? 'Baru' }}">
            <input type="hidden" name="is_registered_member" id="is_registered_member" value="{{ $is_member ?? 0 }}">
            <input type="hidden" name="member_id" id="member_id" value="{{ $customer->member->id ?? '' }}">

            {{-- ROW 1: NAMA & NO HP --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Nama --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Customer</label>
                    <input type="text" name="nama_customer" id="nama_customer"
                           value="{{ $customer->nama ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold placeholder-gray-400" 
                           placeholder="Masukkan nama">
                </div>

                {{-- No HP --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex items-center relative">
                    <div class="border-r border-gray-400 pr-4 mr-4 h-full flex items-center">
                        <label class="text-sm font-semibold text-gray-600 whitespace-nowrap">No HP</label>
                    </div>
                    <input type="number" name="no_hp" id="no_hp" onkeyup="cekCustomer()"
                           value="{{ $no_hp ?? '' }}" 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-bold"
                           placeholder="08...">
                </div>
            </div>

            {{-- ROW 2: JUMLAH & CS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex justify-between items-center">
                    <label class="text-sm font-semibold text-gray-600">Jumlah</label>
                    <div class="flex items-center gap-6">
                        <button type="button" onclick="adjustJumlah(-1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none">&minus;</button>
                        <input type="number" id="inputJumlah" name="jumlah_total" value="1" readonly 
                               class="w-12 bg-transparent border-none text-center font-bold text-lg p-0 focus:ring-0">
                        <button type="button" onclick="adjustJumlah(1)" class="text-2xl text-gray-600 hover:text-black font-bold focus:outline-none">&plus;</button>
                    </div>
                </div>

                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex flex-col justify-center">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Cs</label>
                    <select name="cs" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                        <option value="Admin 1">Admin 1</option>
                        <option value="Admin 2">Admin 2</option>
                    </select>
                </div>
            </div>

            {{-- ROW 3: ITEMS CONTAINER --}}
            <div id="itemsContainer" class="space-y-4 mb-6">
                <div class="item-row grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Item</label>
                        <input type="text" name="item[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="Nama Barang">
                    </div>
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Kategori Treatment</label>
                        <select name="kategori_treatment[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-700">
                            <option value="Deep Clean">Deep Clean</option>
                            <option value="Fast Clean">Fast Clean</option>
                            <option value="Repaint">Repaint</option>
                            <option value="Uyellowing">Uyellowing</option>
                            <option value="Repair">Repair</option>
                        </select>
                    </div>
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium text-gray-700">
                    </div>
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Harga</label>
                        <input type="number" name="harga[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="0">
                    </div>
                    <div class="bg-[#E0E0E0] rounded-lg p-3 px-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="catatan[]" class="w-full bg-transparent border-none p-0 focus:ring-0 text-sm font-medium placeholder-gray-500" placeholder="-">
                    </div>
                </div>
            </div>

            {{-- ROW 4: PEMBAYARAN, POINT, TIPE (MODIFIED) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Pembayaran --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Pembayaran</label>
                    <select name="pembayaran" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                        <option value="Belum Lunas">Belum Lunas</option>
                        <option value="Lunas">Lunas</option>
                        <option value="DP">DP</option>
                    </select>
                </div>

                {{-- POINT (NEW SECTION) --}}
                <div id="box-point" class="bg-[#E0E0E0] rounded-lg p-3 px-5 flex items-center justify-between {{ ($is_member ?? false) ? '' : 'hidden' }}">
    <label class="text-sm font-semibold text-gray-600">Point</label>
    <div class="flex items-center gap-3">
        <span id="poin-text" class="text-gray-800 font-bold text-lg">{{ $poin ?? 0 }}/8</span>
       @php
    $poinSekarang = $customer->member->poin ?? 0; // Ambil poin dari data customer yang diload (jika ada)
    $targetPoin = 8; // Samakan dengan controller
@endphp

<button type="button" id="btn-claim" onclick="claimReward()" 
        class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded shadow hover:bg-blue-700 transition {{ $poinSekarang >= $targetPoin ? '' : 'hidden' }}">
    Claim
</button>
    </div>
</div>

                {{-- Tipe Customer --}}
                <div class="bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tipe Customer</label>
                    <input type="text" id="display_tipe_customer" value="{{ $status ?? 'Baru' }}" readonly 
                           class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                </div>
            </div>

            {{-- ROW 5: INFO --}}
            <div class="grid grid-cols-1 mb-12">
                <div class="md:w-1/2 bg-[#E0E0E0] rounded-lg p-3 px-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tau Tempat ini Dari...</label>
                    <select name="sumber_info" class="w-full bg-transparent border-none p-0 focus:ring-0 text-gray-800 font-medium">
                        <option value="Instagram">Instagram</option>
                        <option value="Teman">Teman</option>
                        <option value="Google Maps">Google Maps</option>
                        <option value="Lewat">Lewat Depan Toko</option>
                    </select>
                </div>
            </div>

            {{-- FOOTER BUTTONS --}}
            <div class="flex justify-end gap-4">
                {{-- Tombol Member tampil jika belum member --}}
                <button type="button" id="btn-daftar-member" onclick="openMemberModal()" 
                        class="bg-[#3b66ff] text-white px-10 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition {{ ($is_member ?? false) ? 'hidden' : '' }}">
                    MEMBER
                </button>

                <button type="submit" class="bg-[#3b66ff] text-white px-12 py-3 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition">
                    INPUT
                </button>
            </div>
        </form>
    </div>

    @include('components.member-modal')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // --- 1. LOGIKA CEK CUSTOMER (POINT & MEMBER) ---
        let timeout = null;
        function cekCustomer() {
            let noHp = document.getElementById('no_hp').value;
            
            // Reset state tombol member
            document.getElementById('btn-daftar-member').classList.remove('hidden');

            clearTimeout(timeout);
            timeout = setTimeout(function () {
                if(noHp.length >= 4) { // Min 4 digit
                    $.ajax({
                        url: "{{ route('check.customer') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            no_hp: noHp
                        },
                        success: function (response) {
    if (response.found) {
        // Isi Nama & Tipe
        document.getElementById('nama_customer').value = response.nama;
        document.getElementById('display_tipe_customer').value = response.tipe;
        document.getElementById('tipe_customer_input').value = response.tipe;
        
        // LOGIKA BARU: TAMPILKAN/SEMBUNYIKAN BOX POINT
        if(response.tipe === 'Member') {
            // TAMPILKAN BOX POINT
            document.getElementById('box-point').classList.remove('hidden'); 

            document.getElementById('member_id').value = response.member_id;
            document.getElementById('is_registered_member').value = 1;
            
            // Sembunyikan tombol daftar member
            document.getElementById('btn-daftar-member').classList.add('hidden');
            
            // Update Badge Status
            const badge = document.getElementById('badge-status');
            badge.innerText = 'Member';
            badge.className = 'text-xl font-bold text-pink-500';

            // --- LOGIKA POIN TARGET 8 (Hanya jalan jika member) ---
            let currentPoint = response.poin || 0;
        let targetPoint = response.target || 8;
            document.getElementById('poin-text').innerText = currentPoint + '/' + targetPoint;

            let btnClaim = document.getElementById('btn-claim');
            if (currentPoint >= targetPoint) {
                btnClaim.classList.remove('hidden');
            } else {
                btnClaim.classList.add('hidden');
            }

        } else {
            // JIKA REGULAR / BUKAN MEMBER
            // SEMBUNYIKAN BOX POINT
            document.getElementById('box-point').classList.add('hidden'); 

            document.getElementById('is_registered_member').value = 0;
            document.getElementById('badge-status').innerText = 'Baru'; // Atau Regular
            document.getElementById('badge-status').className = 'text-xl font-bold text-blue-500';
        }

    } else {
        // DATA TIDAK DITEMUKAN (New Customer)
        
        // SEMBUNYIKAN BOX POINT
        document.getElementById('box-point').classList.add('hidden'); 

        document.getElementById('nama_customer').value = '';
        document.getElementById('display_tipe_customer').value = 'New Customer';
        document.getElementById('tipe_customer_input').value = 'New Customer';
        
        // Reset elemen lain
        document.getElementById('btn-claim').classList.add('hidden');
        document.getElementById('badge-status').innerText = 'Baru';
        document.getElementById('badge-status').className = 'text-xl font-bold text-blue-500';
    }
},
                        error: function() {
                            console.log('Error checking customer');
                        }
                    });
                }
            }, 500);
        }

        // --- 2. LOGIKA JUMLAH ITEM (+/-) ---
        function adjustJumlah(delta) {
            const input = document.getElementById('inputJumlah');
            const container = document.getElementById('itemsContainer');
            
            let currentValue = parseInt(input.value);
            let newValue = currentValue + delta;

            if (newValue < 1) return;

            input.value = newValue;

            if (delta > 0) {
                // Clone baris
                const firstRow = container.querySelector('.item-row');
                const newRow = firstRow.cloneNode(true);
                // Reset value
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                newRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
                newRow.classList.add('animate-fade-in'); 
                container.appendChild(newRow);
            } else {
                // Hapus baris
                const rows = container.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    container.removeChild(rows[rows.length - 1]);
                }
            }
        }
        
        // --- 3. FUNGSI MODAL MEMBER (Eksisting) ---
        function openMemberModal() {
    // --- 1. AMBIL DATA DARI FORM UTAMA (Nama & No HP) ---
    const mainNama = document.querySelector('input[name="nama_customer"]').value;
    const mainNoHp = document.getElementById('no_hp').value;

    // Masukkan ke Input di Modal
    const modalNama = document.getElementById('modalNama');
    // Cari input no_hp spesifik di dalam form modal
    const modalNoHp = document.querySelector('#formMemberAjax input[name="no_hp"]'); 

    if(modalNama) modalNama.value = mainNama;
    if(modalNoHp) modalNoHp.value = mainNoHp; 

    // --- 2. HITUNG TOTAL BELANJA & POIN ---
    let totalBelanja = 0;
    // Ambil semua input harga yang ada di form
    const hargaInputs = document.querySelectorAll('input[name="harga[]"]');
    
    hargaInputs.forEach(input => {
        // Pastikan kita hanya mengambil angka (jaga-jaga jika tipe input diubah)
        // Jika input kosong, anggap 0
        let rawVal = input.value;
        let val = parseInt(rawVal) || 0;
        totalBelanja += val;
    });

    // Rumus Poin: 1 Poin setiap kelipatan Rp 50.000
    let poinDidapat = Math.floor(totalBelanja / 50000);

    // --- 3. UPDATE TAMPILAN DI MODAL ---
    if(document.getElementById('modalTotalDisplay')) {
            // Tampilkan format Rupiah (misal: Rp 150.000)
            document.getElementById('modalTotalDisplay').value = "Rp " + totalBelanja.toLocaleString('id-ID');
            
            // Isi input hidden untuk dikirim ke controller
            document.getElementById('modalTotalValue').value = totalBelanja;
            
            // Isi input Poin
            document.getElementById('modalPoin').value = poinDidapat;
    }

    // --- 4. TAMPILKAN MODAL (ANIMASI) ---
    const modal = document.getElementById('memberModal');
    const content = document.getElementById('modalContent');
    if(modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }
}

        function submitMemberAjax(event) {
    event.preventDefault(); // Mencegah form refresh halaman

    // Ambil data form
    let form = document.getElementById('formMemberAjax');
    let formData = new FormData(form);

    // Tambahkan Token CSRF (Wajib di Laravel)
    formData.append('_token', "{{ csrf_token() }}");

    $.ajax({
        url: "{{ route('members.store') }}", // Pastikan route ini ada di web.php
        type: "POST",
        data: formData,
        contentType: false, // Wajib false untuk FormData
        processData: false, // Wajib false untuk FormData
        beforeSend: function() {
            // Opsional: Ubah tombol jadi 'Loading...'
            document.getElementById('btnSimpanMember').innerText = 'Menyimpan...';
        },
        success: function(response) {
            // Kembalikan teks tombol
            document.getElementById('btnSimpanMember').innerText = 'SIMPAN MEMBER';

            if (response.status === 'success') {
                // 1. Tutup Modal
                closeMemberModal();

                // 2. Beri Notifikasi Sukses
                alert(response.message);

                // 3. PENTING: Refresh status customer di halaman Input Order
                // Kita panggil fungsi cekCustomer() agar UI berubah jadi PINK (Member)
                // dan kotak Poin muncul otomatis.
                cekCustomer(); 
                
            } else {
                // Jika error dari logika controller (misal: nomor sudah terdaftar)
                alert('Gagal: ' + response.message);
            }
        },
        error: function(xhr) {
            document.getElementById('btnSimpanMember').innerText = 'SIMPAN MEMBER';
            
            // Tangkap error validasi (422) atau server error (500)
            let errorMessage = 'Terjadi kesalahan sistem.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert(errorMessage);
        }
    });
}

        function closeMemberModal() {
            const modal = document.getElementById('memberModal');
            const content = document.getElementById('modalContent');
            if(content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }
            setTimeout(() => {
                if(modal) modal.classList.add('hidden');
            }, 300);
        }

        function claimReward() {
    // 1. Ambil ID Member dari hidden input
    let memberId = document.getElementById('member_id').value;
    
    if (!memberId) {
        alert("ID Member tidak ditemukan. Silakan cek ulang nomor HP.");
        return;
    }

    // 2. Konfirmasi User
    if (!confirm("Apakah Anda yakin ingin klaim reward? Poin akan dikurangi.")) {
        return;
    }

    // 3. Kirim Request ke Server
    $.ajax({
        url: "{{ route('members.claim') }}", // Panggil route yang baru dibuat
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            member_id: memberId
        },
        beforeSend: function() {
            // Ubah tombol jadi loading
            let btn = document.getElementById('btn-claim');
            btn.innerText = 'Processing...';
            btn.disabled = true;
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);

                // --- UPDATE TAMPILAN POIN SECARA REALTIME ---
                let sisaPoin = response.sisa_poin;
                let target = response.target;

                // Update teks poin (Misal: 2/8)
                document.getElementById('poin-text').innerText = sisaPoin + '/' + target;

                // Sembunyikan tombol Claim karena poin sudah berkurang
                let btn = document.getElementById('btn-claim');
                btn.innerText = 'Claim'; // Reset teks
                btn.disabled = false;    // Reset disable
                
                if (sisaPoin < target) {
                    btn.classList.add('hidden');
                }

            } else {
                alert(response.message);
                resetBtnClaim();
            }
        },
        error: function(xhr) {
            alert("Terjadi kesalahan sistem. Coba lagi.");
            resetBtnClaim();
        }
    });
}

function resetBtnClaim() {
    let btn = document.getElementById('btn-claim');
    btn.innerText = 'Claim';
    btn.disabled = false;
}
    </script>

    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</x-app-layout>