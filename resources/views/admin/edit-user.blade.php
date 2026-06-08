@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-center min-h-[80vh]">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant w-full max-w-lg flex flex-col z-50">

            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-start">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-1">Edit Data Pengguna</h2>
                    <p class="text-sm text-gray-500">Perbarui informasi profil dan hak akses staf di bawah ini.</p>
                </div>
            </div>

            <form action="{{ route('admin.update-user', $user->id_pengguna) }}" method="POST">
                @csrf
                @method('PUT') <div class="px-8 py-6 space-y-6 flex-1">

                    @if ($errors->any())
                        <div class="bg-red-50 text-red-500 p-3 rounded-lg text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="nama_lengkap">Nama Lengkap</label>
                        <input name="nama_lengkap" id="nama_lengkap" type="text" required
                            value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="username">Username</label>
                        <input name="username" id="username" type="text" required
                            value="{{ old('username', $user->username) }}"
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="peran">Peran</label>
                        <div class="relative">
                            <select name="peran" id="peran" required
                                class="bg-none w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-800 appearance-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow pr-10">
                                <option value="Administrator" {{ $user->peran == 'Administrator' ? 'selected' : '' }}>
                                    Administrator</option>
                                <option value="Kepala Apotek" {{ $user->peran == 'Kepala Apotek' ? 'selected' : '' }}>Kepala
                                    Apotek</option>
                                <option value="Petugas Apotek" {{ $user->peran == 'Petugas Apotek' ? 'selected' : '' }}>
                                    Petugas Apotek</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex justify-end space-x-4 rounded-b-xl">
                    <a href="{{ route('admin.manajemen-user') }}"
                        class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700 shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
