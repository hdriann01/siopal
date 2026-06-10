@extends('layouts.petugas')

@section('content')
    <div class="space-y-8">

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-display font-bold text-gray-800 tracking-tight">Dashboard Operasional</h2>
            <p class="text-gray-500 mt-2 text-lg">Kelola persediaan obat dan catat mutasi stok hari ini.</p>
        </div>

        <!-- Row 1: Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <!-- Total Obat -->
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Jenis Obat</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalObat, 0, ',', '.') }} <span
                                class="text-base font-normal text-gray-400">Item</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="bg-white rounded-xl p-6 border-l-4 border-l-amber-500 border-y border-r border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Stok Menipis</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ $stokMenipis }} <span
                                class="text-base font-normal text-gray-400">Item</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-amber-600 font-medium">
                    <a href="{{ route('petugas.obat') }}" class="hover:underline flex items-center">Lihat detail stok <svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4 ml-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg></a>
                </div>
            </div>

            <!-- Kedaluwarsa -->
            <div class="bg-white rounded-xl p-6 border-l-4 border-l-red-500 border-y border-r border-gray-200 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Akan Kedaluwarsa</p>
                        <h3 class="text-3xl font-bold text-gray-800">{{ $akanKedaluwarsa }} <span
                                class="text-base font-normal text-gray-400">Batch</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm text-red-600 font-medium">
                    <a href="{{ route('petugas.opname') }}" class="hover:underline flex items-center">Tindak lanjuti segera
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4 ml-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg></a>
                </div>
            </div>
        </div>

        <!-- Row 2: Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a href="{{ route('petugas.masuk') }}"
                class="group bg-white rounded-xl p-8 border border-gray-200 shadow-sm hover:border-teal-500 hover:shadow-md transition-all text-left flex items-center gap-6 cursor-pointer block">
                <div
                    class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1 group-hover:text-teal-600 transition-colors">Catat Obat
                        Masuk</h3>
                    <p class="text-gray-500 text-sm">Registrasi faktur stok baru dari supplier</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-8 h-8 text-gray-300 group-hover:text-teal-600 ml-auto transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            <a href="{{ route('petugas.keluar') }}"
                class="group bg-white rounded-xl p-8 border border-gray-200 shadow-sm hover:border-teal-500 hover:shadow-md transition-all text-left flex items-center gap-6 cursor-pointer block">
                <div
                    class="w-16 h-16 rounded-full bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-1 group-hover:text-teal-600 transition-colors">Catat Obat
                        Keluar</h3>
                    <p class="text-gray-500 text-sm">Catat pengeluaran ke pasien atau pemusnahan</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor"
                    class="w-8 h-8 text-gray-300 group-hover:text-teal-600 ml-auto transition-colors">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        <!-- Row 3: Activity Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5 text-teal-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
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
                                    {{ \Carbon\Carbon::parse($aktivitas->tanggal)->locale('id')->diffForHumans() }}</td>
                                <td class="p-4 font-medium text-gray-800">{{ $aktivitas->nama_obat }}</td>
                                <td class="p-4">
                                    @if ($aktivitas->tipe == 'Masuk')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                            </svg>
                                            Masuk
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                                            </svg>
                                            Keluar
                                        </span>
                                    @endif
                                </td>
                                <td
                                    class="p-4 text-right font-bold {{ $aktivitas->tipe == 'Masuk' ? 'text-teal-600' : 'text-gray-500' }}">
                                    {{ $aktivitas->tipe == 'Masuk' ? '+' : '-' }}{{ $aktivitas->jumlah }} Item
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
