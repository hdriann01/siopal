@extends('layouts.kepala')

@section('content')
    <div class="space-y-6 pb-24">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('kepala.verifikasi') }}"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 border border-gray-200 text-gray-800 hover:bg-gray-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold font-display text-gray-800">Detail Verifikasi Faktur</h2>
                    <p class="text-sm text-gray-500 mt-1">Periksa kembali kesesuaian fisik dan dokumen sebelum otorisasi.
                    </p>
                </div>
            </div>
            <div
                class="bg-blue-50 text-blue-700 px-4 py-2 rounded-full font-semibold text-sm flex items-center gap-2 border border-blue-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $faktur->status_verifikasi }}
            </div>
        </div>

        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-teal-600"></div>
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-6 h-6 text-teal-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                Informasi Dokumen
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Nomor Faktur</p>
                    <p class="text-base font-bold text-gray-800 font-display">{{ $faktur->no_faktur }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Supplier</p>
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <p class="text-base font-semibold text-gray-800">{{ $faktur->nama_supplier }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Tanggal Masuk</p>
                    <p class="text-base font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($faktur->tanggal_masuk)->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1 font-medium">Petugas Penerima</p>
                    <div class="flex items-center gap-2">
                        <div
                            class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($faktur->nama_lengkap, 0, 2)) }}
                        </div>
                        <p class="text-base font-medium text-gray-800">{{ $faktur->nama_lengkap }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6 text-teal-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    Rincian Item Obat
                </h3>
                <span
                    class="text-sm text-gray-600 bg-gray-200 px-3 py-1 rounded-full font-medium">{{ $detailObat->count() }}
                    Item Terdaftar</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Obat</th>
                            <th class="px-6 py-4">Dosis</th>
                            <th class="px-6 py-4">Bentuk Sediaan</th>
                            <th class="px-6 py-4">Nomor Batch</th>
                            <th class="px-6 py-4">Tgl Kedaluwarsa</th>
                            <th class="px-6 py-4 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($detailObat as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $item->nama_obat }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item->dosis }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $item->bentuk_sediaan }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-xs text-gray-600 bg-gray-100 border border-gray-200 px-2 py-1 rounded">{{ $item->nomor_batch }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ \Carbon\Carbon::parse($item->tgl_kadaluwarsa)->format('m / Y') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800 text-base">
                                    {{ $item->jumlah_masuk }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if ($faktur->status_verifikasi == 'Draft')
        <footer
            class="fixed bottom-0 right-0 w-full md:w-[calc(100%-16rem)] bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] flex justify-end items-center px-8 py-4 gap-4 z-30">
            <form action="{{ route('kepala.verifikasi.proses', $faktur->id_masuk) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="status" value="Ditolak">
                <button type="submit"
                    class="border border-red-500 text-red-600 px-6 py-2 rounded-full font-bold hover:bg-red-50 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tolak Faktur
                </button>
            </form>

            <form action="{{ route('kepala.verifikasi.proses', $faktur->id_masuk) }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="status" value="Disetujui">
                <button type="submit"
                    class="bg-teal-600 text-white px-6 py-2 rounded-full font-bold hover:bg-teal-700 transition-all flex items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Setujui & Tambah Stok
                </button>
            </form>
        </footer>
    @endif
@endsection
