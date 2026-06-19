@extends('layouts.kepala')

@section('content')
    <div class="space-y-8">

        <div>
            <h2 class="text-3xl font-display font-bold text-gray-800">Verifikasi Faktur</h2>
            <p class="text-gray-500 mt-2 text-sm">Daftar faktur dari supplier yang menunggu peninjauan dan persetujuan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-bold text-xs mb-1 uppercase tracking-wider">Total Menunggu</p>
                    <p class="text-4xl font-display font-bold text-gray-800">{{ $totalMenunggu }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </div>

            <div class="bg-red-50 rounded-xl p-6 shadow-sm border border-red-200 flex items-center justify-between">
                <div>
                    <p class="text-red-600 font-bold text-xs mb-1 uppercase tracking-wider">Urgent (Expiring Soon)</p>
                    <p class="text-4xl font-display font-bold text-red-600">{{ $urgentCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="font-display font-bold text-gray-800 text-lg">Daftar Menunggu Verifikasi</h3>
                <div class="flex gap-2">
                    <button
                        class="p-2 border border-gray-300 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-bold text-xs uppercase tracking-wider">
                            <th class="p-4 border-b border-gray-200">No. Faktur</th>
                            <th class="p-4 border-b border-gray-200">Supplier</th>
                            <th class="p-4 border-b border-gray-200">Tanggal Masuk</th>
                            <th class="p-4 border-b border-gray-200 text-center">Jumlah Item</th>
                            <th class="p-4 border-b border-gray-200">Status</th>
                            <th class="p-4 border-b border-gray-200 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($fakturList as $faktur)
                            <tr
                                class="border-b border-gray-100 hover:bg-gray-50 transition-colors {{ $faktur->ada_urgent > 0 ? 'bg-red-50/40' : '' }}">
                                <td class="p-4 font-display font-bold text-gray-800">{{ $faktur->no_faktur }}</td>
                                <td class="p-4 text-gray-700">{{ $faktur->nama_supplier }}</td>
                                <td class="p-4 text-gray-500">
                                    {{ \Carbon\Carbon::parse($faktur->tanggal_masuk)->format('d M Y') }}</td>
                                <td class="p-4 text-center font-bold text-gray-800">{{ $faktur->jumlah_item }}</td>
                                <td class="p-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $faktur->status_verifikasi }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('kepala.verifikasi.detail', $faktur->id_masuk) }}"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white text-sm font-bold rounded-lg hover:bg-teal-700 transition-colors">
                                        Lihat Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    Hore! Tidak ada faktur yang menunggu verifikasi saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
