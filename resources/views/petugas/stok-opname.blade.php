@extends('layouts.petugas')

@section('content')
    <form action="{{ route('petugas.simpan-opname') }}" method="POST" id="formOpname"
        class="flex flex-col h-full min-h-[calc(100vh-4rem)]">
        @csrf

        <div class="flex-1 flex flex-col gap-6 pb-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-2">
                <div>
                    <h1 class="text-3xl font-display font-bold text-on-surface mb-2 tracking-tight">Audit Stok Opname</h1>
                    <p class="text-on-surface-variant text-sm max-w-2xl">Bandingkan data stok sistem dengan jumlah fisik di
                        rak untuk sinkronisasi data.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <select id="filterRak"
                            class="appearance-none bg-none bg-surface border border-outline-variant text-on-surface text-sm rounded pl-4 pr-10 py-2.5 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary shadow-sm min-w-[200px] cursor-pointer">
                            <option value="">Semua Lokasi Rak</option>
                            @foreach ($rakList as $rak)
                                <option value="{{ $rak }}" {{ $rakPilihan == $rak ? 'selected' : '' }}>Rak
                                    {{ $rak }}</option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
                    </div>
                </div>
            </div>

            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-surface-container-low border-b border-outline-variant text-sm text-on-surface-variant">
                                <th class="py-4 px-6 font-bold w-[25%]">Nama Obat & ID</th>
                                <th class="py-4 px-6 font-bold w-[10%]">Letak Rak</th>
                                <th class="py-4 px-6 font-bold w-[12%]">Stok Sistem</th>
                                <th class="py-4 px-6 font-bold w-[15%]">Stok Fisik</th>
                                <th class="py-4 px-6 font-bold w-[10%]">Selisih</th>
                                <th class="py-4 px-6 font-bold w-[28%]">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-outline-variant/50" id="opnameBody">

                            @forelse($obatList as $obat)
                                <tr class="hover:bg-surface-container-lowest/50 transition-colors group"
                                    id="row-{{ $obat->id_obat }}">
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-on-surface">{{ $obat->nama_obat }}</span>
                                            <span
                                                class="text-xs text-on-surface-variant font-mono mt-0.5">{{ $obat->id_obat }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-container text-on-surface-variant text-xs font-bold border border-outline-variant/30">
                                            <span class="material-symbols-outlined text-[14px]">shelves</span>
                                            {{ $obat->letak_rak ?? 'Tidak diset' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-on-surface-variant font-bold flex items-center gap-2">
                                            <span class="stok-sistem-val"
                                                data-sistem="{{ $obat->total_stok }}">{{ $obat->total_stok }}</span>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <input type="number" name="items[{{ $obat->id_obat }}][stok_fisik]"
                                            class="input-fisik w-24 px-3 py-1.5 bg-surface border border-outline-variant rounded focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm font-bold text-on-surface transition-colors"
                                            placeholder="0" min="0" data-id="{{ $obat->id_obat }}">
                                    </td>
                                    <td class="py-4 px-6" id="selisih-col-{{ $obat->id_obat }}">
                                        <span class="text-on-surface-variant font-bold">-</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <input type="text" name="items[{{ $obat->id_obat }}][keterangan]"
                                            id="ket-{{ $obat->id_obat }}"
                                            class="w-full px-3 py-1.5 bg-surface-container-low border border-transparent rounded text-sm text-on-surface-variant opacity-50 cursor-not-allowed transition-all"
                                            disabled placeholder="Masukkan alasan selisih...">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-on-surface-variant">Tidak ada data obat
                                        yang ditemukan.</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            class="fixed bottom-0 right-0 w-[calc(100%-16rem)] bg-surface border-t border-outline-variant p-6 flex items-center justify-between z-10 shadow-lg">
            <div class="flex items-center gap-3 text-on-surface-variant max-w-6xl mx-auto w-full">
                <span class="material-symbols-outlined text-primary">info</span>
                <p class="text-sm">Hanya baris yang kolom <span class="font-bold text-on-surface">Stok Fisik</span>-nya
                    diisi yang akan diproses oleh sistem.</p>

                <button type="submit"
                    class="ml-auto flex items-center gap-2 bg-primary hover:bg-primary-container text-white px-8 py-3 rounded-lg font-bold transition-colors shadow-sm">
                    <span class="material-symbols-outlined">sync_saved_locally</span>
                    Finalisasi & Update Stok
                </button>
            </div>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {

            $('#filterRak').change(function() {
                let rak = $(this).val();
                let url = new URL(window.location.href);
                if (rak) {
                    url.searchParams.set('rak', rak);
                } else {
                    url.searchParams.delete('rak');
                }
                window.location.href = url.toString();
            });

            $('.input-fisik').on('input', function() {
                let idObat = $(this).data('id');
                let row = $('#row-' + idObat);
                let colSelisih = $('#selisih-col-' + idObat);
                let inputKet = $('#ket-' + idObat);

                let stokSistem = parseInt(row.find('.stok-sistem-val').data('sistem'));
                let stokFisikStr = $(this).val();

                if (stokFisikStr === '') {
                    row.removeClass('bg-error-container/10 bg-tertiary-container/10');
                    $(this).removeClass('border-error border-tertiary text-error text-tertiary');
                    colSelisih.html('<span class="text-on-surface-variant font-bold">-</span>');
                    inputKet.prop('disabled', true).addClass(
                            'opacity-50 cursor-not-allowed bg-surface-container-low border-transparent')
                        .removeClass('bg-surface border-outline-variant').removeAttr('required').val('');
                    return;
                }

                let stokFisik = parseInt(stokFisikStr);
                let selisih = stokFisik - stokSistem;

                if (selisih === 0) {
                    row.removeClass('bg-error-container/10 bg-tertiary-container/10');
                    $(this).removeClass('border-error border-tertiary text-error text-tertiary').addClass(
                        'border-outline-variant');

                    colSelisih.html(
                        '<span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-container text-on-surface-variant font-bold">0</span>'
                    );

                    inputKet.prop('disabled', true).addClass(
                            'opacity-50 cursor-not-allowed bg-surface-container-low border-transparent')
                        .removeClass('bg-surface border-outline-variant').removeAttr('required').val(
                            'Sesuai');
                } else if (selisih < 0) {
                    row.removeClass('bg-tertiary-container/10').addClass('bg-error-container/10');
                    $(this).removeClass('border-outline-variant border-tertiary text-tertiary').addClass(
                        'border-error text-error');

                    colSelisih.html(`
                        <span class="inline-flex items-center gap-1 text-error font-bold bg-error-container/30 px-2 py-1 rounded">
                            <span class="material-symbols-outlined text-sm">arrow_downward</span> ${selisih}
                        </span>
                    `);

                    inputKet.prop('disabled', false).removeClass(
                            'opacity-50 cursor-not-allowed bg-surface-container-low border-transparent')
                        .addClass('bg-surface border-outline-variant').prop('required', true).val('');
                } else {
                    row.removeClass('bg-error-container/10').addClass('bg-tertiary-container/10');
                    $(this).removeClass('border-outline-variant border-error text-error').addClass(
                        'border-tertiary text-tertiary');

                    colSelisih.html(`
                        <span class="inline-flex items-center gap-1 text-tertiary font-bold bg-tertiary-container/20 px-2 py-1 rounded">
                            <span class="material-symbols-outlined text-sm">arrow_upward</span> +${selisih}
                        </span>
                    `);

                    inputKet.prop('disabled', false).removeClass(
                            'opacity-50 cursor-not-allowed bg-surface-container-low border-transparent')
                        .addClass('bg-surface border-outline-variant').prop('required', true).val('');
                }
            });

        });
    </script>
@endsection
