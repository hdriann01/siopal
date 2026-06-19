@extends('layouts.petugas')

@section('content')
<div class="w-full flex-1 overflow-y-auto p-4 lg:p-8 bg-gray-50 flex justify-center items-start pt-10 pb-24">

    <div class="bg-white rounded-[12px] shadow-[0_10px_40px_-10px_rgba(100,116,139,0.15)] w-full max-w-4xl flex flex-col overflow-hidden border border-gray-200">

        <div class="px-8 py-6 border-b border-gray-200 flex justify-between items-start bg-white">
            <div>
                <h2 class="font-display text-2xl font-bold text-gray-800 tracking-tight mb-1">Tambah Sediaan Obat Baru</h2>
                <p class="text-sm text-gray-500">Lengkapi informasi detail obat untuk didaftarkan ke dalam katalog inventaris.</p>
            </div>

            <a href="{{ route('petugas.obat') }}" class="text-gray-400 hover:text-red-500 transition-colors rounded-full p-2 hover:bg-gray-100">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </a>
        </div>

        <div class="px-8 py-8 bg-white overflow-y-auto">

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                    <ul class="list-disc pl-5 text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('petugas.obat.simpan') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                @csrf <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="nama_obat">Nama Obat <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_obat" name="nama_obat" value="{{ old('nama_obat') }}" required placeholder="Contoh: Amoxicillin" class="block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none transition-shadow"/>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="id_kategori">Kategori Obat <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select id="id_kategori" name="id_kategori" required class="block w-full appearance-none rounded-lg border-gray-300 bg-gray-50 px-4 py-3 pr-10 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none cursor-pointer transition-shadow">
                            <option disabled selected value="">Pilih Kategori</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id_kategori }}" {{ old('id_kategori') == $kat->id_kategori ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span>
                        </div>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kekuatan Dosis <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="number" id="dosis" name="dosis" value="{{ old('dosis') }}" required placeholder="Contoh: 500" class="block w-2/3 rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none transition-shadow"/>

                        <div class="relative w-1/3">
                            <select id="satuan_dosis" name="satuan_dosis" required class="block w-full appearance-none rounded-lg border-gray-300 bg-gray-50 px-3 py-3 pr-8 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none cursor-pointer transition-shadow">
                                <option disabled selected value="">Satuan</option>
                                <option value="mg" {{ old('satuan_dosis') == 'mg' ? 'selected' : '' }}>mg</option>
                                <option value="g" {{ old('satuan_dosis') == 'g' ? 'selected' : '' }}>g</option>
                                <option value="ml" {{ old('satuan_dosis') == 'ml' ? 'selected' : '' }}>ml</option>
                                <option value="IU" {{ old('satuan_dosis') == 'IU' ? 'selected' : '' }}>IU</option>
                                <option value="%" {{ old('satuan_dosis') == '%' ? 'selected' : '' }}>%</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                <span class="material-symbols-outlined text-[20px]">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="bentuk_sediaan">Bentuk Sediaan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select id="bentuk_sediaan" name="bentuk_sediaan" required class="block w-full appearance-none rounded-lg border-gray-300 bg-gray-50 px-4 py-3 pr-10 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none cursor-pointer transition-shadow">
                            <option disabled selected value="">Pilih Bentuk Sediaan</option>
                            <option value="Tablet" {{ old('bentuk_sediaan') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="Kapsul" {{ old('bentuk_sediaan') == 'Kapsul' ? 'selected' : '' }}>Kapsul</option>
                            <option value="Sirup" {{ old('bentuk_sediaan') == 'Sirup' ? 'selected' : '' }}>Sirup</option>
                            <option value="Salep" {{ old('bentuk_sediaan') == 'Salep' ? 'selected' : '' }}>Salep</option>
                            <option value="Injeksi" {{ old('bentuk_sediaan') == 'Injeksi' ? 'selected' : '' }}>Injeksi</option>
                            <option value="Suppositoria" {{ old('bentuk_sediaan') == 'Suppositoria' ? 'selected' : '' }}>Suppositoria</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span>
                        </div>
                    </div>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="letak_rak">Letak Rak</label>
                    <input type="text" id="letak_rak" name="letak_rak" value="{{ old('letak_rak') }}" placeholder="Contoh: Rak A-1" class="block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none transition-shadow"/>
                </div>

                <div class="col-span-1">
                    <label class="block text-sm font-bold text-gray-700 mb-2" for="batas_stok_min">Batas Stok Minimum <span class="text-red-500">*</span></label>
                    <input type="number" id="batas_stok_min" name="batas_stok_min" value="{{ old('batas_stok_min') }}" min="0" required placeholder="0" class="block w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-800 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 outline-none transition-shadow"/>
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[16px] text-teal-600">info</span>
                        Sistem akan memberi peringatan jika stok di bawah angka ini.
                    </p>
                </div>

                <div class="col-span-1 md:col-span-2 hidden md:block border-b border-gray-100 my-2"></div>

                <div class="col-span-1 md:col-span-2 flex justify-end gap-4 mt-2">
                    <a href="{{ route('petugas.obat') }}" class="px-6 py-2.5 text-sm font-bold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-teal-600 rounded-lg shadow-sm hover:bg-teal-700 transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Simpan Obat
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
