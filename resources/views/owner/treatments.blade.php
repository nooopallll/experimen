<x-app-layout>
    <div class="p-6 bg-white min-h-screen">
        {{-- Header Halaman --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#7FB3D5]">Manajemen Treatment</h1>
            <button onclick="openModal('add')" class="bg-[#3b66ff] text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 transition">
                + Tambah Treatment
            </button>
        </div>

        {{-- Tabel Data Treatment --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow-md border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase w-1/3">Kategori</th>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase w-1/2">Nama Layanan</th>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase text-center w-1/6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatments as $t)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-4 font-medium text-gray-800">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold uppercase border border-blue-100">
                                {{ $t->kategori }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-700 font-semibold">{{ $t->nama_treatment }}</td>
                        <td class="p-4 flex justify-center gap-2">
                            {{-- Tombol Edit --}}
                            <button onclick="editTreatment({{ $t }})" class="bg-yellow-400 text-white p-2 rounded-lg shadow hover:bg-yellow-500 transition" title="Edit Layanan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            
                            {{-- Tombol Hapus --}}
                            <form action="{{ route('owner.treatments.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white p-2 rounded-lg shadow hover:bg-red-600 transition" title="Hapus Layanan">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-10 text-center text-gray-500 italic">Belum ada data treatment yang tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL FORM (ADD & EDIT) --}}
    <div id="modal-treatment" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-60 hidden" onclick="closeModalOutside(event)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden animate-fade-in" onclick="event.stopPropagation()">
            {{-- Modal Header --}}
            <div class="bg-[#3b66ff] p-4 flex justify-between items-center text-white">
                <h3 id="modal-title" class="font-bold text-lg">Tambah Treatment</h3>
                <button onclick="closeModal()" class="text-2xl font-bold hover:text-gray-200 transition">&times;</button>
            </div>

            {{-- Modal Body --}}
            <form id="form-treatment" method="POST" class="p-6 space-y-5">
                @csrf
                <div id="method-field"></div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Kategori Layanan</label>
                    <input type="text" name="kategori" id="input-kategori" class="w-full border-gray-300 rounded-lg focus:ring-[#3b66ff] focus:border-[#3b66ff] placeholder-gray-400" placeholder="Cth: Deep Clean / Unyellowing" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wider">Nama Treatment</label>
                    <input type="text" name="nama_treatment" id="input-nama" class="w-full border-gray-300 rounded-lg focus:ring-[#3b66ff] focus:border-[#3b66ff] placeholder-gray-400" placeholder="Cth: Deep Clean Small / Leather Care" required>
                </div>
                
                {{-- Footer Modal --}}
                <div class="flex justify-end gap-3 pt-6 border-t">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 font-bold hover:text-gray-800 transition">Batal</button>
                    <button type="submit" class="bg-[#3b66ff] text-white px-8 py-2 rounded-lg font-bold shadow-lg hover:bg-blue-700 transition transform hover:scale-105 active:scale-95">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal-treatment');
        const form = document.getElementById('form-treatment');
        const title = document.getElementById('modal-title');
        const methodField = document.getElementById('method-field');

        function openModal(type) {
            modal.classList.remove('hidden');
            if (type === 'add') {
                title.innerText = "Tambah Treatment";
                form.action = "{{ route('owner.treatments.store') }}";
                methodField.innerHTML = "";
                form.reset();
            }
        }

        function editTreatment(data) {
            modal.classList.remove('hidden');
            title.innerText = "Edit Treatment";
            // Set URL Action untuk Update
            form.action = `/owner/treatments/${data.id}`;
            // Tambahkan spoofing method PUT untuk Laravel
            methodField.innerHTML = `<input type="hidden" name="_method" value="PUT">`;
            
            // Isi data ke input field
            document.getElementById('input-kategori').value = data.kategori;
            document.getElementById('input-nama').value = data.nama_treatment;
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function closeModalOutside(event) {
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</x-app-layout>