@extends('layouts.petugas')

@section('content')
    <div class="space-y-8 pb-24">
        <div>
            <h2 class="text-3xl font-display font-bold text-on-surface tracking-tight mb-2">Peringatan Stok Menipis</h2>
            <p class="text-on-surface-variant text-sm max-w-3xl">Daftar sediaan farmasi yang telah menyentuh atau berada di
                bawah batas stok minimal. Segera lakukan pengadaan (restok) untuk mencegah kekosongan obat.</p>
        </div>

        <section
            class="bg-white rounded-xl border-l-4 border-l-amber-500 border-y border-r border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-amber-50/30 flex items-center gap-3">
                <span class="material-symbols-outlined text-amber-600">warning</span>
                <h3 class="text-lg font-bold text-gray-800">Logistik Kritis</h3>
                <span
                    class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full ml-auto">{{ $stokMenipis->count() }}
                    Item</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
                            <th class="p-4 font-bold uppercase tracking-wider text-xs">Nama Obat</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs">Letak Rak</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs text-center">Batas Minimal</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs text-center">Stok Saat Ini</th>
                            <th class="p-4 font-bold uppercase tracking-wider text-xs text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @forelse($stokMenipis as $obat)
                            <tr class="hover:bg-amber-50/50 transition-colors">
                                <td class="p-4 font-bold text-gray-800">{{ $obat->nama_obat }}</td>
                                <td class="p-4 text-gray-500">{{ $obat->letak_rak ?? 'Belum diset' }}</td>
                                <td class="p-4 text-center text-gray-500">{{ $obat->batas_stok_min }}
                                    {{ $obat->bentuk_sediaan }}</td>
                                <td
                                    class="p-4 text-center font-bold {{ $obat->total_stok == 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ $obat->total_stok }} {{ $obat->bentuk_sediaan }}
                                </td>
                                <td class="p-4 text-right">
                                    @if ($obat->total_stok == 0)
                                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded">Stok
                                            Kosong</span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-1 rounded">Segera
                                            Restok</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500 italic">Kondisi aman. Tidak ada obat
                                    yang stoknya menipis.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
