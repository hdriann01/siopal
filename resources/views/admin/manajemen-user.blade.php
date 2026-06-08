@extends('layouts.admin')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface tracking-tight">Manajemen Pengguna</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola akses sistem dan peran.</p>
        </div>

        <div class="flex flex-wrap gap-6 max-w-xs">
            <div
                class="bg-white p-6 rounded-xl border border-outline-variant shadow-[0_4px_15px_rgba(100,116,139,0.04)] flex items-start space-x-4 w-full">
                <div class="p-3 bg-primary-container rounded-lg text-white">
                    <span class="material-symbols-outlined fill text-2xl">group</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-on-surface-variant mb-1">Total Pengguna</p>
                    <h3 class="text-3xl font-bold text-on-surface">{{ $totalPengguna }}</h3>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-xl border border-outline-variant shadow-[0_4px_15px_rgba(100,116,139,0.04)] overflow-hidden">

            <div class="p-5 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-center gap-4">

                <form action="{{ route('admin.manajemen-user') }}" method="GET" class="relative w-full sm:w-72">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                    <input name="search" value="{{ request('search') }}"
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none text-on-surface"
                        placeholder="Cari pengguna (Tekan Enter)..." type="text">
                </form>

                <div class="flex space-x-3 w-full sm:w-auto">
                    <a href="{{ route('admin.tambah-user') }}"
                        class="flex-1 sm:flex-none flex items-center justify-center space-x-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        <span>Tambah Pengguna</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 border-b border-outline-variant text-xs uppercase tracking-wider text-on-surface-variant">
                            <th class="p-4 font-semibold">INFO PENGGUNA</th>
                            <th class="p-4 font-semibold">USERNAME</th>
                            <th class="p-4 font-semibold">PERAN</th>
                            <th class="p-4 font-semibold text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($pengguna as $user)
                            <tr class="hover:bg-teal-50/50 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-gray-800">{{ $user->nama_lengkap }}</p>
                                </td>
                                <td class="p-4 text-gray-500 font-mono text-xs">{{ $user->username }}</td>
                                <td class="p-4">
                                    @if ($user->peran == 'Administrator')
                                        <span
                                            class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold border border-red-200">Administrator</span>
                                    @elseif($user->peran == 'Kepala Apotek')
                                        <span
                                            class="px-2.5 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-semibold border border-teal-200">Kepala
                                            Apotek</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium border border-gray-200">Petugas</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.edit-user', $user->id_pengguna) }}"
                                            class="inline-flex p-1.5 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded transition-colors"
                                            title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>

                                        <a href="{{ route('admin.reset-password', $user->id_pengguna) }}"
                                            class="inline-flex p-1.5 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded transition-colors"
                                            title="Reset Password">
                                            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                        </a>

                                        <a href="{{ route('admin.konfirmasi-hapus', $user->id_pengguna) }}"
                                            class="inline-flex p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                            title="Hapus Permanen">
                                            <span class="material-symbols-outlined text-[18px]">block</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">
                                    @if (request('search'))
                                        Pencarian "<b>{{ request('search') }}</b>" tidak ditemukan.
                                    @else
                                        Belum ada data pengguna di dalam sistem.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
