@extends('layouts.kepala')

@section('content')
    <div class="flex-1 overflow-y-auto p-4 md:p-8 flex items-center justify-center bg-gray-50 pb-24">
        <div class="w-full max-w-2xl bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">

            @if (session('success'))
                <div class="bg-teal-50 border-l-4 border-teal-500 p-4 m-4 rounded-r-lg">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined text-teal-500 mr-2">check_circle</span>
                        <p class="text-sm text-teal-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 m-4 rounded-r-lg">
                    <div class="flex flex-col">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center mb-1">
                                <span class="material-symbols-outlined text-red-500 mr-2 text-sm">error</span>
                                <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="px-8 pt-8 pb-6 border-b border-gray-200 text-center">
                <h2 class="text-2xl font-display font-bold text-gray-800 tracking-tight mb-2">Profil Saya</h2>
                <p class="text-gray-500 text-sm">Kelola informasi pribadi dan nama akun Anda sebagai Kepala Apotek.</p>
            </div>

            <form action="{{ route('kepala.profil.update') }}" method="POST">
                @csrf

                <div class="p-8 flex flex-col gap-8 items-center">
                    <div class="relative inline-block">
                        <img alt="Profile Picture"
                            class="w-32 h-32 rounded-full object-cover border-4 border-gray-100 shadow-sm"
                            src="https://ui-avatars.com/api/?name={{ urlencode($user->nama_lengkap) }}&background=00685f&color=fff&size=256" />
                        <button type="button"
                            class="absolute bottom-1 right-1 w-10 h-10 bg-teal-600 text-white rounded-full flex items-center justify-center shadow-md hover:bg-teal-700 transition-colors border-2 border-white">
                            <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                        </button>
                    </div>

                    <div class="w-full max-w-md flex flex-col gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700" for="nama_lengkap">Nama Lengkap</label>
                            <input
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                id="nama_lengkap" name="nama_lengkap" type="text"
                                value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700" for="username">Username</label>
                            <input
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                id="username" name="username" type="text" value="{{ old('username', $user->username) }}"
                                required />
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-700">Peran / Jabatan</label>
                            <div
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-lg flex items-center gap-3 cursor-not-allowed opacity-80">
                                <div class="w-2 h-2 rounded-full bg-teal-600"></div>
                                <span class="text-teal-700 font-bold">{{ $user->peran }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="submit"
                        class="px-6 py-3 bg-teal-600 text-white font-bold rounded-full shadow-sm hover:bg-teal-700 active:scale-95 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">save</span> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
