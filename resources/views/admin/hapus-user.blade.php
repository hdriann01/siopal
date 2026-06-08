@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="relative w-full max-w-md bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-red-600"></div>

        <div class="p-8 flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-6 border border-red-100">
                <span class="material-symbols-outlined text-4xl text-red-600">delete_forever</span>
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-3 tracking-tight">Hapus Permanen Pengguna?</h2>
            <p class="text-gray-600 mb-2">
                Apakah Anda yakin ingin menghapus data <span class="font-bold text-gray-900">{{ $user->nama_lengkap }}</span>?
            </p>
            <p class="text-sm text-gray-500 mb-8">
                Tindakan ini tidak dapat dibatalkan. Seluruh data akses dan riwayat pengguna ini akan terhapus sepenuhnya dari sistem SIOPAL.
            </p>

            <form action="{{ route('admin.proses-hapus', $user->id_pengguna) }}" method="POST" class="w-full">
                @csrf
                @method('DELETE')

                <div class="flex flex-col sm:flex-row gap-3 w-full justify-center">
                    <a href="{{ route('admin.manajemen-user') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-gray-200 text-gray-600 font-semibold tracking-wide hover:bg-gray-50 transition-colors text-center">
                        Batal
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-red-600 text-white font-semibold tracking-wide hover:bg-red-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Ya, Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
