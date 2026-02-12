<x-app-layout>
    <div class="py-6" x-data="{ openEdit: false, currId: null, currNama: '' }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <h1 class="text-4xl font-bold text-[#7FB3D5] mb-8">Manajemen Karyawan</h1>

            {{-- FORM TAMBAH --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm mb-8 border border-gray-100">
                <form action="{{ route('owner.karyawan.store') }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="nama_karyawan" placeholder="Masukkan Nama Karyawan Baru..." required
                        class="flex-1 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none transition">
                    <button type="submit" class="px-6 py-3 bg-[#3b66ff] text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-md">
                        + Tambah
                    </button>
                </form>
            </div>

            {{-- TABEL KARYAWAN --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase">Nama Karyawan</th>
                            <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($karyawans as $k)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-700 font-medium">{{ $k->nama_karyawan }}</td>
                            <td class="px-6 py-4 flex justify-center gap-3">
                                {{-- EDIT BUTTON --}}
                                <button @click="openEdit = true; currId = '{{ $k->id }}'; currNama = '{{ $k->nama_karyawan }}'"
                                    class="p-2 text-yellow-500 hover:bg-yellow-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                
                                {{-- DELETE BUTTON --}}
                                <form action="{{ route('owner.karyawan.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus karyawan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- MODAL EDIT (ALPINE JS) --}}
            <div x-show="openEdit" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" x-cloak>
                <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl" @click.away="openEdit = false">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Nama Karyawan</h2>
                    <form :action="'/owner/karyawan/' + currId" method="POST">
                        @csrf @method('PUT')
                        <input type="text" name="nama_karyawan" x-model="currNama" required
                            class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl mb-6 outline-none focus:ring-2 focus:ring-blue-400">
                        <div class="flex gap-4">
                            <button type="button" @click="openEdit = false" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-100 rounded-xl transition">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-[#3b66ff] text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>