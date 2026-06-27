@extends('layouts.petugas')

@section('content')
    <form action="{{ route('petugas.simpan-masuk') }}" method="POST" id="formObatMasuk"
        class="flex flex-col h-full relative pb-24">
        @csrf

        <div class="space-y-8">
            <div class="mb-8">
                <h2 class="text-3xl font-display font-bold text-on-background tracking-tight">Pencatatan Obat Masuk</h2>
                <p class="text-on-surface-variant mt-2 text-sm">Input data faktur dan rincian item sediaan farmasi yang
                    diterima dari PBF.</p>
            </div>

            @if (session('error'))
                <div class="p-4 bg-error-container text-on-error-container rounded-lg font-medium mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant bg-surface-container-low">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        Informasi Faktur
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Nomor Faktur <span
                                class="text-error">*</span></label>
                        <input name="no_faktur" id="no_faktur" required
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest text-on-surface"
                            placeholder="Contoh: INV/2026/04/105" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Nama Supplier (PBF) <span
                                class="text-error">*</span></label>
                        <input name="nama_supplier" id="nama_supplier" required
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest text-on-surface"
                            placeholder="Contoh: PBF Kimia Farma" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Tanggal Terima <span
                                class="text-error">*</span></label>
                        <input name="tanggal_masuk" id="tanggal_masuk" required
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest text-on-surface"
                            type="date" value="{{ date('Y-m-d') }}" />
                    </div>
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant bg-surface-container-low">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_box</span>
                        Input Rincian Item Obat
                    </h3>
                </div>
                <div class="p-6 flex flex-wrap items-end gap-4 bg-surface-container-lowest">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Cari Obat</label>
                        <select id="input_obat"
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface">
                            <option value="" selected disabled>Pilih Obat dari Katalog...</option>
                            @foreach ($obatList as $obat)
                                <option value="{{ $obat->id_obat }}"
                                    data-nama="{{ $obat->nama_obat }} ({{ $obat->dosis }}{{ $obat->satuan_dosis }})">
                                    {{ $obat->nama_obat }} - {{ $obat->dosis }}{{ $obat->satuan_dosis }}
                                    ({{ $obat->bentuk_sediaan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Nomor Batch</label>
                        <input id="input_batch"
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface"
                            placeholder="B-123" type="text" />
                    </div>
                    <div class="w-40">
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Expired Date</label>
                        <input id="input_exp"
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface"
                            type="date" />
                    </div>
                    <div class="w-24">
                        <label class="block text-sm font-bold text-on-surface-variant mb-1.5">Jumlah</label>
                        <input id="input_jumlah"
                            class="w-full rounded-lg border-outline-variant border py-2 px-3 focus:ring-2 focus:ring-primary bg-surface-container-lowest text-on-surface text-center"
                            min="1" type="number" value="1" />
                    </div>
                    <div>
                        <button type="button" id="btnTambahItem"
                            class="bg-primary hover:bg-primary/90 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 h-[42px]">
                            <span class="material-symbols-outlined text-sm">add</span> Tambah
                        </button>
                    </div>
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">list_alt</span>
                        Daftar Item Sementara
                    </h3>
                    <span id="badgeCount"
                        class="bg-primary-container text-on-primary-container text-xs font-bold px-3 py-1 rounded-full">0
                        Item</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="tabelItem">
                        <thead>
                            <tr
                                class="bg-surface-container-lowest border-b border-outline-variant text-sm text-on-surface-variant">
                                <th class="py-3 px-6 font-bold">Nama Obat</th>
                                <th class="py-3 px-6 font-bold">Nomor Batch</th>
                                <th class="py-3 px-6 font-bold">Tgl Kadaluwarsa</th>
                                <th class="py-3 px-6 font-bold text-center">Jumlah</th>
                                <th class="py-3 px-6 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-on-surface" id="keranjangBody">
                            <tr id="rowKosong">
                                <td colspan="5" class="py-8 text-center text-on-surface-variant text-sm italic">
                                    Belum ada item obat yang ditambahkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="hiddenInputsContainer"></div>
            </section>
        </div>

        <footer
            class="bg-surface-container-lowest border-t border-outline-variant p-6 fixed bottom-0 right-0 w-[calc(100%-16rem)] z-10 shadow-lg">
            <div class="flex justify-between items-center max-w-6xl mx-auto">
                <p class="text-sm text-on-surface-variant italic flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">info</span>
                    Data akan disimpan sebagai draf sampai disetujui oleh Kepala Apotek.
                </p>
                <button type="submit" id="btnSubmitForm"
                    class="bg-primary hover:bg-primary/90 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center gap-2">
                    Kirim Verifikasi Ke Kepala Apotek
                    <span class="material-symbols-outlined">send</span>
                </button>
            </div>
        </footer>
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            let itemIndex = 0;

            $('#btnTambahItem').click(function() {
                let idObat = $('#input_obat').val();
                let namaObat = $('#input_obat option:selected').data('nama');
                let batch = $('#input_batch').val();
                let exp = $('#input_exp').val();
                let jumlah = $('#input_jumlah').val();

                if (!idObat || !batch || !exp || jumlah < 1) {
                    alert(
                        'Mohon lengkapi semua kolom rincian item (Obat, Batch, Exp Date, Jumlah) sebelum menambahkan!'
                    );
                    return;
                }

                $('#rowKosong').hide();

                let newRow = `
                    <tr class="border-b border-outline-variant/50 hover:bg-surface-container transition-colors" id="row-${itemIndex}">
                        <td class="py-3 px-6 font-bold text-primary">${namaObat}</td>
                        <td class="py-3 px-6 text-sm">${batch}</td>
                        <td class="py-3 px-6 text-sm">${exp}</td>
                        <td class="py-3 px-6 text-center font-bold">${jumlah}</td>
                        <td class="py-3 px-6 text-center">
                            <button type="button" class="btnHapus text-error hover:text-error-container transition-colors p-1" data-id="${itemIndex}">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </td>
                    </tr>
                `;

                let hiddenInputs = `
                    <div id="hidden-${itemIndex}">
                        <input type="hidden" name="items[${itemIndex}][id_obat]" value="${idObat}">
                        <input type="hidden" name="items[${itemIndex}][nomor_batch]" value="${batch}">
                        <input type="hidden" name="items[${itemIndex}][tgl_kadaluwarsa]" value="${exp}">
                        <input type="hidden" name="items[${itemIndex}][jumlah_masuk]" value="${jumlah}">
                    </div>
                `;

                $('#keranjangBody').append(newRow);
                $('#hiddenInputsContainer').append(hiddenInputs);

                $('#input_obat').val('');
                $('#input_batch').val('');
                $('#input_exp').val('');
                $('#input_jumlah').val('1');

                itemIndex++;
                updateBadge();
            });

            $(document).on('click', '.btnHapus', function() {
                let id = $(this).data('id');
                $('#row-' + id).remove();
                $('#hidden-' + id).remove();
                updateBadge();
            });

            function updateBadge() {
                let count = $('#keranjangBody tr[id^="row-"]').length;
                $('#badgeCount').text(count + ' Item');

                if (count === 0) {
                    $('#rowKosong').show();
                }
            }

            $('#formObatMasuk').submit(function(e) {
                let count = $('#keranjangBody tr[id^="row-"]').length;
                if (count === 0) {
                    e.preventDefault();
                    alert('Keranjang faktur masih kosong! Silakan tambahkan minimal 1 item obat.');
                }
            });
        });
    </script>
@endsection
