@extends('layouts.petugas')

@section('content')
    <form action="{{ route('petugas.simpan-keluar') }}" method="POST" id="formObatKeluar"
        class="flex flex-col h-full relative pb-24">
        @csrf

        <div class="space-y-8">
            <div>
                <h2 class="text-3xl font-display font-bold text-on-surface tracking-tight mb-2">Pencatatan Obat Keluar</h2>
                <p class="text-on-surface-variant text-sm max-w-2xl">Catat pengeluaran stok obat untuk resep, distribusi,
                    atau pemusnahan. Pastikan data batch dan jumlah sesuai sebelum menyimpan transaksi.</p>
            </div>

            @if (session('error'))
                <div class="p-4 bg-error-container text-on-error-container rounded-lg font-medium mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary">assignment</span>
                    <h3 class="text-lg font-bold text-on-surface">Informasi Transaksi</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Tujuan
                            Pengeluaran <span class="text-error">*</span></label>
                        <div class="relative">
                            <select name="tujuan_pengeluaran" required
                                class="w-full appearance-none bg-none bg-surface border border-outline-variant rounded-lg pl-4 pr-10 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer">
                                <option value="" selected disabled>Pilih Tujuan...</option>
                                <option value="Resep">Resep Pasien</option>
                                <option value="Pemusnahan/Rusak">Pemusnahan / Obat Rusak</option>
                                <option value="Distribusi Internal">Distribusi Unit Internal</option>
                                <option value="Retur PBF">Retur ke Supplier (PBF)</option>
                            </select>
                            <span
                                class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">arrow_drop_down</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Referensi /
                            No. Resep</label>
                        <input name="referensi"
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                            placeholder="Opsional" type="text" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Tanggal
                            Keluar <span class="text-error">*</span></label>
                        <input name="tanggal_keluar" required
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all appearance-none"
                            type="date" value="{{ date('Y-m-d') }}" />
                    </div>
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-outline-variant/30">
                    <span class="material-symbols-outlined text-primary">medication</span>
                    <h3 class="text-lg font-bold text-on-surface">Tambah Item Keluar</h3>
                </div>
                <div class="flex flex-col lg:flex-row gap-4 items-end">
                    <div class="w-full lg:w-2/5 space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Pilih Obat
                            (Stok Tersedia)</label>
                        <select id="input_obat" onchange="autoFillBatch()"
                            class="w-full bg-surface border border-outline-variant rounded-lg pl-4 pr-10 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer">
                            <option value="" selected disabled>Pilih Obat...</option>
                            @foreach ($obatList as $obat)
                                <option value="{{ $obat->id_obat }}" data-nama="{{ $obat->nama_obat }}"
                                    data-sisa="{{ $obat->total_stok }}"
                                    data-satuan="{{ $obat->satuan_dosis ?? '' }} {{ $obat->bentuk_sediaan ?? '' }}"
                                    data-batch="{{ $obat->batch_rekomendasi ?? '-' }}">
                                    {{ $obat->nama_obat }} - (Sisa Stok: {{ $obat->total_stok }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full lg:w-1/4 space-y-2">
                        <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nomor
                            Batch</label>
                        <input id="input_batch"
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                            placeholder="Contoh: B-1234" type="text" />
                    </div>
                    <div class="w-full lg:w-1/6 space-y-2">
                        <label
                            class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider">Jumlah</label>
                        <input id="input_jumlah"
                            class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all font-bold"
                            min="1" placeholder="0" type="number" />
                    </div>
                    <div class="w-full lg:w-auto">
                        <button type="button" id="btnTambahItem"
                            class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white font-medium px-6 py-2.5 rounded-lg transition-colors shadow-sm h-[42px]">
                            <span class="material-symbols-outlined text-sm">add</span> Tambah
                        </button>
                    </div>
                </div>
            </section>

            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div
                    class="p-6 pb-4 border-b border-outline-variant/30 flex justify-between items-center bg-surface-bright">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-on-surface-variant">list_alt</span>
                        <h3 class="text-base font-bold text-on-surface">Daftar Item</h3>
                    </div>
                    <span id="badgeCount"
                        class="text-xs font-bold bg-surface-container text-on-surface-variant px-3 py-1 rounded-full">0 Item
                        Ditambahkan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-lowest border-b border-outline-variant/40">
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                                    Nama Obat</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">
                                    Batch</th>
                                <th
                                    class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider text-right">
                                    Jumlah Keluar</th>
                                <th
                                    class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider text-center w-24">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="keranjangBody" class="text-sm divide-y divide-outline-variant/20">
                            <tr id="rowKosong">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                                            <span
                                                class="material-symbols-outlined text-3xl text-on-surface-variant">inventory_2</span>
                                        </div>
                                        <p class="text-on-surface font-bold mb-1">Belum ada item ditambahkan</p>
                                        <p class="text-sm text-on-surface-variant max-w-sm">Silakan gunakan form di atas
                                            untuk mencari dan menambahkan obat ke daftar pengeluaran.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="hiddenInputsContainer"></div>
            </section>
        </div>

        <div
            class="fixed bottom-0 right-0 w-[calc(100%-16rem)] bg-surface/80 backdrop-blur-md border-t border-outline-variant/30 p-4 px-8 z-10 flex justify-end">
            <div class="max-w-6xl w-full mx-auto flex justify-between items-center">
                <span class="text-sm text-on-surface-variant font-bold">Pastikan data yang dimasukkan sudah benar sebelum
                    menyimpan.</span>
                <button type="submit" id="btnSubmitForm"
                    class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold px-8 py-3 rounded-lg transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">security</span> Simpan & Kurangi Stok
                </button>
            </div>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            let itemIndex = 0;

            $('#btnTambahItem').click(function() {
                let idObat = $('#input_obat').val();
                let namaObat = $('#input_obat option:selected').data('nama');
                let stokTersedia = parseInt($('#input_obat option:selected').data('sisa'));
                let satuan = $('#input_obat option:selected').data('satuan');
                let batch = $('#input_batch').val() || '-';
                let jumlah = parseInt($('#input_jumlah').val());

                if (!idObat || !jumlah || jumlah < 1) {
                    alert('Mohon pilih Obat dan masukkan jumlah pengeluaran yang valid!');
                    return;
                }

                if (jumlah > stokTersedia) {
                    alert('Gagal: Jumlah pengeluaran melebihi sisa stok yang ada (Sisa: ' + stokTersedia +
                        ')!');
                    return;
                }

                $('#rowKosong').hide();

                let newRow = `
                    <tr class="hover:bg-surface-container-lowest/50 transition-colors" id="row-${itemIndex}">
                        <td class="px-6 py-4 font-bold text-on-surface">${namaObat}</td>
                        <td class="px-6 py-4 font-bold text-on-surface">${batch}</td>
                        <td class="px-6 py-4 text-right font-bold text-on-surface text-primary">${jumlah} <span class="text-xs font-normal text-on-surface-variant ml-1">${satuan}</span></td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" class="btnHapus text-error hover:bg-error-container/30 p-1.5 rounded-md transition-colors" data-id="${itemIndex}">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </td>
                    </tr>
                `;

                let hiddenInputs = `
                    <div id="hidden-${itemIndex}">
                        <input type="hidden" name="items[${itemIndex}][id_obat]" value="${idObat}">
                        <input type="hidden" name="items[${itemIndex}][nomor_batch]" value="${batch}">
                        <input type="hidden" name="items[${itemIndex}][jumlah_keluar]" value="${jumlah}">
                    </div>
                `;

                $('#keranjangBody').append(newRow);
                $('#hiddenInputsContainer').append(hiddenInputs);

                $('#input_obat').val('');
                $('#input_batch').val('');
                $('#input_jumlah').val('');

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
                $('#badgeCount').text(count + ' Item Ditambahkan');
                if (count === 0) $('#rowKosong').show();
            }

            $('#formObatKeluar').submit(function(e) {
                let count = $('#keranjangBody tr[id^="row-"]').length;
                if (count === 0) {
                    e.preventDefault();
                    alert(
                        'Keranjang masih kosong! Silakan tambahkan minimal 1 item obat yang akan dikeluarkan.'
                    );
                }
            });
        });
    </script>
    <script>
        function autoFillBatch() {
            const selectObat = document.getElementById('input_obat');
            const inputBatch = document.getElementById('input_batch');

            const selectedOption = selectObat.options[selectObat.selectedIndex];
            const batchOtomatis = selectedOption.getAttribute('data-batch');

            if (batchOtomatis) {
                inputBatch.value = batchOtomatis;
            } else {
                inputBatch.value = '';
            }
        }
    </script>
@endsection
