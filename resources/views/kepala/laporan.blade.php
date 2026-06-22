@extends('layouts.kepala')

@section('content')
    <div class="space-y-6 pb-24">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-display font-bold text-gray-800 mb-2">Laporan Inventaris & Stok</h2>
                <p class="text-gray-500 text-sm">Pantau statistik ketersediaan dan pergerakan obat secara real-time.</p>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <button onclick="window.print()"
                    class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors font-medium text-sm shadow-sm">
                    <span class="material-symbols-outlined text-sm">print</span> Cetak Laporan
                </button>
                <!-- Menggunakan fungsi url() agar terbebas dari masalah nama Route -->
                <a href="{{ url('/export-excel-laporan') }}"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold py-2.5 px-5 rounded-lg transition-colors text-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Export Excel
                </a>
            </div>
        </div>

        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-8 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-2 w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2 bg-gray-50">
                <span class="material-symbols-outlined text-gray-500 text-sm">calendar_today</span>
                <span class="text-sm text-gray-700 font-medium">Data Real-Time:
                    {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </div>

            <form method="GET" action="{{ route('kepala.laporan') }}" class="w-full sm:w-auto">
                <div
                    class="flex items-center gap-2 w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-2 bg-white hover:border-teal-500 transition-colors">
                    <span class="material-symbols-outlined text-gray-500 text-sm">filter_list</span>
                    <select name="kategori" onchange="this.form.submit()"
                        class="bg-transparent border-none focus:ring-0 text-sm text-gray-700 p-0 pr-8 cursor-pointer outline-none">
                        <option value="semua" {{ $kategoriPilihan == 'semua' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->id_kategori }}"
                                {{ $kategoriPilihan == $kat->id_kategori ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-start justify-between relative overflow-hidden">
                <div class="absolute left-0 top-0 w-1 h-full bg-teal-600"></div>
                <div>
                    <p class="text-sm font-bold text-gray-500 mb-1 uppercase tracking-wider">Total Item Obat</p>
                    <h3 class="text-3xl font-bold font-display text-gray-800">{{ number_format($totalItem) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-full bg-teal-50 flex items-center justify-center text-teal-600">
                    <span class="material-symbols-outlined text-2xl">medication</span>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-orange-200 flex items-start justify-between relative overflow-hidden">
                <div class="absolute left-0 top-0 w-1 h-full bg-orange-500"></div>
                <div>
                    <p class="text-sm font-bold text-orange-600 mb-1 uppercase tracking-wider">Stok Kritis / Habis</p>
                    <h3 class="text-3xl font-bold font-display text-gray-800">{{ number_format($stokKritis) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-red-200 flex items-start justify-between relative overflow-hidden">
                <div class="absolute left-0 top-0 w-1 h-full bg-red-500"></div>
                <div>
                    <p class="text-sm font-bold text-red-600 mb-1 uppercase tracking-wider">Obat Kedaluwarsa</p>
                    <h3 class="text-3xl font-bold font-display text-gray-800">{{ number_format($expiredCount) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                    <span class="material-symbols-outlined text-2xl">error</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Detail Stok Obat</h3>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider font-bold">
                            <th class="p-4 border-b border-gray-200">ID Obat</th>
                            <th class="p-4 border-b border-gray-200">Nama Obat & Dosis</th>
                            <th class="p-4 border-b border-gray-200">Kategori</th>
                            <th class="p-4 border-b border-gray-200 text-right">Sisa Stok</th>
                            <th class="p-4 border-b border-gray-200">Satuan</th>
                            <th class="p-4 border-b border-gray-200">Status Stok</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($obatList as $obat)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 font-mono text-gray-500 text-xs">{{ $obat->id_obat }}</td>
                                <td class="p-4 font-bold text-gray-800">{{ $obat->nama_obat }}
                                    {{ $obat->dosis }}{{ $obat->satuan_dosis }}</td>
                                <td class="p-4 text-gray-600">{{ $obat->nama_kategori }}</td>
                                <td class="p-4 text-right font-bold text-gray-800 text-base">{{ $obat->total_stok }}</td>
                                <td class="p-4 text-gray-600">{{ $obat->bentuk_sediaan }}</td>
                                <td class="p-4">
                                    @if ($obat->total_stok == 0)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Kosong
                                        </span>
                                    @elseif($obat->total_stok <= $obat->batas_stok_min)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 border border-orange-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Menipis
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Aman
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">Tidak ada data obat pada kategori
                                    ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $obatList->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
