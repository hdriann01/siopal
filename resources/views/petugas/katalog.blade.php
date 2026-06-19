@extends('layouts.petugas')

@section('content')
<div class="w-full flex-1 overflow-y-auto p-4 lg:py-6 lg:px-4 bg-gray-50 pb-24">

    @if(session('success'))
        <div class="mb-4 p-4 bg-teal-50 border-l-4 border-teal-500 text-teal-700 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-display font-bold text-gray-800 mb-1">Inventaris & Katalog Obat</h2>
            <p class="text-gray-500 text-sm">Kelola data master sediaan farmasi dan posisi penyimpanan rak.</p>
        </div>

        <a href="{{ route('petugas.obat.tambah') }}" class="flex items-center gap-2 bg-teal-600 text-white px-4 py-2.5 rounded-lg hover:bg-teal-700 transition-colors shadow-sm font-medium text-sm whitespace-nowrap self-start sm:self-end">
            <span class="material-symbols-outlined text-[20px]">add</span> Tambah Obat Baru
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col h-full">

        <form method="GET" action="{{ route('petugas.obat') }}" class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row gap-4 items-center justify-between">

            <div class="relative w-full md:w-96 flex">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                <input name="search" value="{{ $search }}" class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent" placeholder="Cari nama obat atau ID..." type="text"/>
            </div>

            <div class="flex gap-3 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
                <select name="kategori" onchange="this.form.submit()" class="text-sm bg-white border border-gray-300 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 min-w-[140px] cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id_kategori }}" {{ $kategoriPilihan == $kat->id_kategori ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="hidden">Cari</button>
            </div>
        </form>

        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-500 text-xs uppercase tracking-wider font-bold">
                        <th class="p-4 py-3">ID Obat</th>
                        <th class="p-4 py-3">Nama Obat</th>
                        <th class="p-4 py-3">Kategori</th>
                        <th class="p-4 py-3">Letak Rak</th>
                        <th class="p-4 py-3 text-right">Stok Total</th>
                        <th class="p-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">

                    @forelse($obatList as $obat)
                        @php $isKritis = $obat->total_stok <= $obat->batas_stok_min; @endphp

                        <tr class="hover:bg-gray-50 transition-colors group {{ $isKritis ? 'bg-red-50' : '' }}">
                            <td class="p-4 whitespace-nowrap font-mono text-gray-500 text-xs">{{ $obat->id_obat }}</td>
                            <td class="p-4">
                                <div class="font-bold text-gray-800">{{ $obat->nama_obat }} {{ $obat->dosis }}{{ $obat->satuan_dosis }}</div>
                                <div class="text-xs text-gray-500">{{ $obat->bentuk_sediaan }}</div>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $obat->nama_kategori }}
                                </span>
                            </td>
                            <td class="p-4 whitespace-nowrap text-gray-500 font-medium">{{ $obat->letak_rak }}</td>

                            <td class="p-4 whitespace-nowrap text-right font-bold text-lg {{ $isKritis ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $obat->total_stok }}
                                <span class="text-xs font-normal text-gray-500 ml-1">{{ $obat->bentuk_sediaan }}</span>
                            </td>

                            <td class="p-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">

                                    <a href="{{ route('petugas.obat.edit', $obat->id_obat) }}" class="p-1.5 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-md transition-colors" title="Edit Data">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>

                                    <button type="button" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus Data">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                Tidak ada data obat yang ditemukan.
                            </td>
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
