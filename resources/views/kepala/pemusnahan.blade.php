@extends('layouts.kepala')

@section('content')
    <div class="space-y-6">
        <div class="mb-8">
            <h2 class="text-3xl font-display font-bold text-gray-800 mb-2">Otorisasi Pemusnahan Obat</h2>
            <p class="text-gray-500 text-sm">Tinjau dan berikan persetujuan untuk penghapusan stok obat rusak atau
                kedaluwarsa dari inventaris sistem.</p>
        </div>

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2 border border-gray-200 rounded-xl p-1 bg-white shadow-sm">
                <a href="{{ route('kepala.pemusnahan', ['tab' => 'Menunggu']) }}"
                    class="px-4 py-2 font-semibold rounded-lg text-sm transition-colors {{ $activeTab == 'Menunggu' ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-50' }}">
                    Menunggu ({{ $totalMenunggu }})
                </a>
                <a href="{{ route('kepala.pemusnahan', ['tab' => 'Semua']) }}"
                    class="px-4 py-2 font-semibold rounded-lg text-sm transition-colors {{ $activeTab == 'Semua' ? 'bg-gray-200 text-gray-800' : 'text-gray-500 hover:bg-gray-50' }}">
                    Semua
                </a>
                <a href="{{ route('kepala.pemusnahan', ['tab' => 'Disetujui']) }}"
                    class="px-4 py-2 font-semibold rounded-lg text-sm transition-colors {{ $activeTab == 'Disetujui' ? 'bg-teal-100 text-teal-700' : 'text-gray-500 hover:bg-gray-50' }}">
                    Disetujui
                </a>
                <a href="{{ route('kepala.pemusnahan', ['tab' => 'Ditolak']) }}"
                    class="px-4 py-2 font-semibold rounded-lg text-sm transition-colors {{ $activeTab == 'Ditolak' ? 'bg-red-100 text-red-700' : 'text-gray-500 hover:bg-gray-50' }}">
                    Ditolak
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Pengajuan
                            </th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Info Obat</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Alasan</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">Diajukan Oleh
                            </th>
                            <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Aksi /
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($pengajuanList as $item)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="py-5 px-6 whitespace-nowrap text-gray-800 font-medium">
                                    {{ \Carbon\Carbon::parse($item->tanggal_keluar)->format('d M Y') }}<br />
                                    <span
                                        class="text-xs text-gray-500 font-normal">{{ \Carbon\Carbon::parse($item->tanggal_keluar)->format('H:i') }}
                                        WIB</span>
                                </td>
                                <td class="py-5 px-6">
                                    <div class="font-bold text-gray-800">{{ $item->nama_obat }}
                                        {{ $item->dosis }}{{ $item->satuan_dosis }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    @if (strpos(strtolower($item->tujuan_pengeluaran), 'kedaluwarsa') !== false)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Kedaluwarsa
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 border border-orange-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                            {{ $item->tujuan_pengeluaran }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-5 px-6 font-bold text-gray-800 text-base">{{ $item->jumlah_keluar }} Item
                                </td>
                                <td class="py-5 px-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200">
                                            {{ strtoupper(substr($item->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $item->nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->peran }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-5 px-6 text-right">
                                    @if ($item->status_otorisasi == 'Menunggu')
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('kepala.pemusnahan.proses', $item->id_keluar) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Ditolak">
                                                <button type="submit"
                                                    class="px-4 py-2 border border-red-500 text-red-600 hover:bg-red-50 font-bold rounded-lg transition-colors">Tolak</button>
                                            </form>

                                            <form action="{{ route('kepala.pemusnahan.proses', $item->id_keluar) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Disetujui">
                                                <button type="submit"
                                                    class="px-4 py-2 bg-teal-600 text-white hover:bg-teal-700 font-bold rounded-lg transition-colors shadow-sm">Setujui</button>
                                            </form>
                                        </div>
                                    @elseif($item->status_otorisasi == 'Disetujui')
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                            Disetujui
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">Tidak ada pengajuan pada kategori
                                    ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between bg-gray-50">
                <span class="text-sm text-gray-500">Menampilkan pengajuan tertunda</span>
            </div>
        </div>
    </div>
@endsection
