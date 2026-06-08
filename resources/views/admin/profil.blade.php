@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-2xl p-8 md:p-12 transition-all duration-300">

        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">Profil Saya</h1>
            <p class="text-gray-500 text-sm">Atur foto profil dan informasi akun Anda.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-8 text-sm font-medium border border-green-200 text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-8 text-sm font-medium border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.update-profil') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="flex justify-center mb-12">
                <div class="relative group cursor-pointer">
                    <img class="w-32 h-32 rounded-full object-cover border-4 border-gray-50 shadow-sm transition-colors duration-300"
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->nama_lengkap) }}&background=00685f&color=fff&size=128" alt="Profile Picture">
                    <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                        <span class="material-symbols-outlined text-white">photo_camera</span>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex flex-col space-y-1.5">
                    <label class="text-sm font-semibold text-gray-700" for="nama_lengkap">Nama Lengkap</label>
                    <input name="nama_lengkap" id="nama_lengkap" type="text" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow">
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-sm font-semibold text-gray-700" for="username">Username</label>
                    <input name="username" id="username" type="text" value="{{ old('username', $user->username) }}" required
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-shadow">
                </div>

                <div class="flex flex-col space-y-1.5">
                    <label class="text-sm font-semibold text-gray-700">Peran / Jabatan</label>
                    <div class="inline-flex items-center px-4 py-2.5 bg-teal-50 text-teal-800 border border-teal-200 rounded-lg font-semibold text-sm self-start">
                        <span class="material-symbols-outlined mr-2 text-[18px]">shield_person</span>
                        {{ $user->peran }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Peran akun tidak dapat diubah dari halaman profil ini demi keamanan.</p>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-primary hover:bg-teal-700 text-white font-semibold text-sm px-8 py-2.5 rounded-lg shadow-sm transition-colors duration-200 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                    Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
