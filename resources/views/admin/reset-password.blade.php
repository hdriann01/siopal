@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="bg-white w-full max-w-md rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col z-50">

        <div class="px-6 pt-6 pb-4 border-b border-gray-100">
            <h2 class="text-gray-800 font-bold text-xl mb-1 tracking-tight">Reset Password</h2>
            <p class="text-gray-500 text-sm leading-snug">Masukkan kata sandi baru untuk pengguna <span class="font-semibold text-gray-800">{{ $user->nama_lengkap }}</span>.</p>
        </div>

        <form action="{{ route('admin.update-password', $user->id_pengguna) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="px-6 py-5 flex flex-col gap-5 bg-white">

                @if ($errors->any())
                    <div class="bg-red-50 text-red-500 p-3 rounded-lg text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="flex flex-col gap-1.5">
                    <label class="text-gray-700 text-sm font-semibold" for="password">Password Baru</label>
                    <div class="relative">
                        <input name="password" id="password" type="password" required class="w-full bg-gray-50 text-gray-800 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all pr-10" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-gray-700 text-sm font-semibold" for="password_confirmation">Konfirmasi Password</label>
                    <div class="relative">
                        <input name="password_confirmation" id="password_confirmation" type="password" required class="w-full bg-gray-50 text-gray-800 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all pr-10" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-start gap-2 mt-1 bg-teal-50 p-3 rounded-lg border border-teal-100">
                    <span class="material-symbols-outlined text-primary text-base mt-0.5">info</span>
                    <p class="text-teal-800 text-xs leading-relaxed">Pastikan kata sandi baru menggunakan kombinasi huruf dan angka untuk keamanan data medis.</p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50/50 flex items-center justify-end gap-3 border-t border-gray-100 rounded-b-xl">
                <a href="{{ route('admin.manajemen-user') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-100 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-lg text-sm font-medium bg-primary text-white hover:bg-teal-700 transition-colors shadow-sm flex items-center gap-2">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
