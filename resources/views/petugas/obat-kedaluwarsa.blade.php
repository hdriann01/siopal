@extends('layouts.petugas')

@section('content')
    <div class="space-y-8 pb-24">
        <div>
            <h2 class="text-3xl font-display font-bold text-on-surface tracking-tight mb-2">Peringatan Kedaluwarsa Obat</h2>
            <p class="text-on-surface-variant text-sm max-w-3xl">Daftar batch sediaan farmasi yang telah kedaluwarsa atau
                memasuki masa kritis (≤ 3 bulan). Segera amankan fisik obat dari rak untuk menjaga keselamatan pasien.</p>
        </div>

        <section
            class="bg-white rounded-xl border-l-4 border-l-red-500 border-y border-r border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-red-50/30 flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600">event_busy</span>
                <h3 class="text-lg font-bold text-gray-800">Kontrol Masa Kadaluwarsa (≤ 3 Bulan)</h3>
                <span
                    class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full ml-auto">{{ $akanKedaluwarsa->count() }}
                    Batch</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
                            <th class="p-4 font-bold uppercase tracking-wider text-xs">Nama Obat</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs">Nomor Batch</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs">Tgl Kedaluwarsa</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs text-right">Sisa Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($akanKedaluwarsa as $item)
                            @php
                                $tglED = \Carbon\Carbon::parse($item->tgl_kadaluwarsa)->startOfDay();
                                $hariIni = \Carbon\Carbon::now()->startOfDay();

                                $isExpired = $tglED->isBefore($hariIni);

                                $sisaHari = (int) $hariIni->diffInDays($tglED);
                            @endphp
                            <tr class="{{ $isExpired ? 'bg-red-50/50' : 'hover:bg-red-50/30' }} transition-colors">
                                <td class="p-4 font-bold text-gray-800">{{ $item->nama_obat }}</td>
                                <td class="p-4 text-gray-500 font-mono text-xs">{{ $item->nomor_batch ?? '-' }}</td>
                                <td class="p-4 text-gray-500 font-bold {{ $isExpired ? 'text-red-600' : '' }}">
                                    {{ $tglED->translatedFormat('d F Y') }}
                                </td>
                                <td class="p-4 text-right">
                                    @if ($isExpired)
                                        <span
                                            class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                            Kedaluwarsa (Lewat {{ $sisaHari }} hari)
                                        </span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">
                                            Sisa {{ $sisaHari }} hari
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500 italic">Kondisi aman. Tidak ada
                                    batch obat yang mendekati masa kedaluwarsa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
