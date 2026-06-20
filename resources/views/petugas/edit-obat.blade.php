@extends('layouts.petugas')

@section('content')
    <div class="flex justify-center items-start pt-4 pb-24">

        <div
            class="bg-surface-container-lowest rounded-[12px] shadow-sm w-full max-w-4xl flex flex-col overflow-hidden border border-outline-variant">

            <div
                class="px-8 py-6 border-b border-outline-variant flex justify-between items-start bg-surface-container-lowest">
                <div>
                    <h2 class="font-display text-2xl font-bold text-on-surface tracking-tight mb-1">Edit Data Obat</h2>
                    <p class="text-sm text-on-surface-variant">Perbarui informasi sediaan farmasi untuk ID: <strong
                            class="text-primary">{{ $obat->id_obat }}</strong></p>
                </div>

                <a href="{{ route('petugas.obat') }}"
                    class="text-outline hover:text-error transition-colors rounded-full p-2 hover:bg-error-container/20">
                    <span class="material-symbols-outlined text-[24px]">close</span>
                </a>
            </div>

            <div class="px-8 py-8 bg-surface-container-lowest overflow-y-auto">

                @if ($errors->any())
                    <div
                        class="mb-6 p-4 bg-error-container/20 border-l-4 border-error text-on-error-container rounded shadow-sm">
                        <ul class="list-disc pl-5 text-sm font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('petugas.obat.update', $obat->id_obat) }}" method="POST"
                    class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                    @csrf
                    @method('PUT') <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2" for="nama_obat">Nama Obat <span
                                class="text-error">*</span></label>
                        <input type="text" id="nama_obat" name="nama_obat"
                            value="{{ old('nama_obat', $obat->nama_obat) }}" required
                            class="block w-full rounded-lg border-outline-variant bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none transition-shadow" />
                    </div>

                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2" for="id_kategori">Kategori Obat <span
                                class="text-error">*</span></label>
                        <div class="relative">
                            <select id="id_kategori" name="id_kategori" required
                                class="block w-full appearance-none bg-none rounded-lg border-outline-variant bg-surface-container-low pl-4 pr-10 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none cursor-pointer transition-shadow">
                                <option disabled value="">Pilih Kategori</option>
                                @foreach ($kategoriList as $kat)
                                    <option value="{{ $kat->id_kategori }}"
                                        {{ old('id_kategori', $obat->id_kategori) == $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[20px]">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2">Kekuatan Dosis <span
                                class="text-error">*</span></label>
                        <div class="flex gap-2">
                            <input type="number" id="dosis" name="dosis" value="{{ old('dosis', $obat->dosis) }}"
                                required
                                class="block w-2/3 rounded-lg border-outline-variant bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none transition-shadow" />

                            <div class="relative w-1/3">
                                <select id="satuan_dosis" name="satuan_dosis" required
                                    class="block w-full appearance-none bg-none rounded-lg border-outline-variant bg-surface-container-low pl-3 pr-8 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none cursor-pointer transition-shadow">
                                    <option disabled value="">Satuan</option>
                                    <option value="mg"
                                        {{ old('satuan_dosis', $obat->satuan_dosis) == 'mg' ? 'selected' : '' }}>mg</option>
                                    <option value="g"
                                        {{ old('satuan_dosis', $obat->satuan_dosis) == 'g' ? 'selected' : '' }}>g</option>
                                    <option value="ml"
                                        {{ old('satuan_dosis', $obat->satuan_dosis) == 'ml' ? 'selected' : '' }}>ml
                                    </option>
                                    <option value="IU"
                                        {{ old('satuan_dosis', $obat->satuan_dosis) == 'IU' ? 'selected' : '' }}>IU
                                    </option>
                                    <option value="%"
                                        {{ old('satuan_dosis', $obat->satuan_dosis) == '%' ? 'selected' : '' }}>%</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[20px]">expand_more</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2" for="bentuk_sediaan">Bentuk Sediaan
                            <span class="text-error">*</span></label>
                        <div class="relative">
                            <select id="bentuk_sediaan" name="bentuk_sediaan" required
                                class="block w-full appearance-none bg-none rounded-lg border-outline-variant bg-surface-container-low pl-4 pr-10 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none cursor-pointer transition-shadow">
                                <option disabled value="">Pilih Bentuk Sediaan</option>
                                <option value="Tablet"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Tablet' ? 'selected' : '' }}>Tablet
                                </option>
                                <option value="Kapsul"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Kapsul' ? 'selected' : '' }}>Kapsul
                                </option>
                                <option value="Sirup"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Sirup' ? 'selected' : '' }}>Sirup
                                </option>
                                <option value="Salep"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Salep' ? 'selected' : '' }}>Salep
                                </option>
                                <option value="Injeksi"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Injeksi' ? 'selected' : '' }}>
                                    Injeksi</option>
                                <option value="Suppositoria"
                                    {{ old('bentuk_sediaan', $obat->bentuk_sediaan) == 'Suppositoria' ? 'selected' : '' }}>
                                    Suppositoria</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                                <span class="material-symbols-outlined text-[20px]">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2" for="letak_rak">Letak Rak</label>
                        <input type="text" id="letak_rak" name="letak_rak"
                            value="{{ old('letak_rak', $obat->letak_rak) }}"
                            class="block w-full rounded-lg border-outline-variant bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none transition-shadow" />
                    </div>

                    <div class="col-span-1">
                        <label class="block text-sm font-bold text-on-surface mb-2" for="batas_stok_min">Batas Stok Minimum
                            <span class="text-error">*</span></label>
                        <input type="number" id="batas_stok_min" name="batas_stok_min"
                            value="{{ old('batas_stok_min', $obat->batas_stok_min) }}" min="0" required
                            class="block w-full rounded-lg border-outline-variant bg-surface-container-low px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-2 focus:ring-primary outline-none transition-shadow" />
                    </div>

                    <div class="col-span-1 md:col-span-2 hidden md:block border-b border-outline-variant my-2"></div>

                    <div class="col-span-1 md:col-span-2 flex justify-end gap-4 mt-2">
                        <a href="{{ route('petugas.obat') }}"
                            class="px-6 py-2.5 text-sm font-bold text-on-surface-variant border border-outline-variant rounded-lg hover:bg-surface-container-high transition-colors focus:outline-none focus:ring-2 focus:ring-outline">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-white bg-primary rounded-lg shadow-sm hover:bg-primary/90 transition-colors focus:outline-none focus:ring-2 focus:ring-primary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-white">edit_note</span>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
