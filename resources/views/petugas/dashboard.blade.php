@extends('layouts.petugas')

@section('content')
    <div class="space-y-8">

        <div class="mb-8">
            <h2 class="text-3xl font-display font-bold text-gray-800 tracking-tight">Dashboard Operasional</h2>
            <p class="text-gray-500 mt-2 text-lg">Kelola persediaan obat dan catat mutasi stok hari ini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Jenis Obat</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalObat, 0, ',', '.') }} <span
                                class="text-base font-normal text-gray-400">Item</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border-l-4 border-l-amber-500 border-y border-r border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Stok Menipis</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ $stokMenipis }} <span
                                class="text-base font-normal text-gray-400">Item</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-outlined text-2xl">warning</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-amber-600 font-medium">
                    <a href="{{ route('petugas.stok-menipis') }}" class="hover:underline flex items-center">Lihat detail
                        stok <span class="material-symbols-outlined text-sm ml-1">chevron_right</span></a>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border-l-4 border-l-red-500 border-y border-r border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Akan Kedaluwarsa</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ $akanKedaluwarsa }} <span
                                class="text-base font-normal text-gray-400">Batch</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                        <span class="material-symbols-outlined text-2xl">event_busy</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-red-600 font-medium">
                    <a href="{{ route('petugas.obat-kedaluwarsa') }}" class="hover:underline flex items-center">Tindak
                        lanjuti segera <span class="material-symbols-outlined text-sm ml-1">chevron_right</span></a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a href="{{ route('petugas.masuk') }}"
                class="group bg-white rounded-xl p-8 border border-gray-200 shadow-sm hover:border-teal-500 hover:shadow-md transition-all text-left flex items-center gap-6 cursor-pointer block">
                <div
                    class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">move_to_inbox</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1 group-hover:text-teal-600 transition-colors">Catat Obat
                        Masuk</h3>
                    <p class="text-gray-500 text-sm">Registrasi faktur stok baru dari supplier</p>
                </div>
                <span
                    class="material-symbols-outlined text-3xl text-gray-300 group-hover:text-teal-600 ml-auto transition-colors">chevron_right</span>
            </a>

            <a href="{{ route('petugas.keluar') }}"
                class="group bg-white rounded-xl p-8 border border-gray-200 shadow-sm hover:border-teal-500 hover:shadow-md transition-all text-left flex items-center gap-6 cursor-pointer block">
                <div
                    class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">outbox</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1 group-hover:text-teal-600 transition-colors">Catat Obat
                        Keluar</h3>
                    <p class="text-gray-500 text-sm">Catat pengeluaran ke pasien atau pemusnahan</p>
                </div>
                <span
                    class="material-symbols-outlined text-3xl text-gray-300 group-hover:text-teal-600 ml-auto transition-colors">chevron_right</span>
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-teal-600">history</span>
                    Aktivitas Stok Terbaru
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
                            <th class="p-4 font-medium">Waktu</th>
                            <th class="p-4 font-medium">Nama Obat</th>
                            <th class="p-4 font-medium">Jenis</th>
                            <th class="p-4 font-medium text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($aktivitasTerbaru as $aktivitas)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 text-gray-500">
                                    @php
                                        $waktu = \Carbon\Carbon::parse($aktivitas->tanggal)
                                            ->setTimezone('Asia/Makassar')
                                            ->locale('id');

                                        if ($waktu->format('H:i:s') === '00:00:00') {
                                            echo $waktu->isToday()
                                                ? '<span class="text-teal-600 font-medium">Hari ini</span>'
                                                : $waktu->translatedFormat('d M Y');
                                        } else {
                                            echo $waktu->diffForHumans();
                                        }
                                    @endphp
                                </td>
                                <td class="p-4 font-medium text-gray-800">{{ $aktivitas->nama_obat }}</td>

                                <td class="p-4">
                                    @if ($aktivitas->tipe == 'Masuk')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700">
                                            <span class="material-symbols-outlined text-[14px]">arrow_downward</span> Masuk
                                        </span>
                                    @elseif ($aktivitas->tipe == 'Keluar')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            <span class="material-symbols-outlined text-[14px]">arrow_upward</span> Keluar
                                        </span>
                                    @elseif ($aktivitas->tipe == 'Opname (+)')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                            <span class="material-symbols-outlined text-[14px]">fact_check</span> Opname (+)
                                        </span>
                                    @elseif ($aktivitas->tipe == 'Opname (-)')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-50 text-orange-700">
                                            <span class="material-symbols-outlined text-[14px]">fact_check</span> Opname (-)
                                        </span>
                                    @endif
                                </td>

                                <td
                                    class="p-4 text-right font-bold {{ in_array($aktivitas->tipe, ['Masuk', 'Opname (+)']) ? 'text-teal-600' : 'text-gray-500' }}">
                                    {{ in_array($aktivitas->tipe, ['Masuk', 'Opname (+)']) ? '+' : '-' }}{{ $aktivitas->jumlah }}
                                    Item
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500">Belum ada aktivitas mutasi obat
                                    terbaru.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
