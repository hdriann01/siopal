@extends('layouts.petugas')

@section('content')
    <div class="pb-24">

        @if (session('success'))
            <div class="mb-4 p-4 bg-teal-50 border-l-4 border-teal-500 text-teal-700 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h2 class="text-3xl font-display font-bold text-on-surface mb-2">Inventaris & Katalog Obat</h2>
                <p class="text-on-surface-variant text-sm">Kelola data master sediaan farmasi dan posisi penyimpanan rak.</p>
            </div>

            <a href="{{ route('petugas.obat.tambah') }}"
                class="flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-lg hover:bg-primary/90 transition-colors shadow-sm font-medium text-sm whitespace-nowrap self-start sm:self-end">
                <span class="material-symbols-outlined text-[20px] text-white">add</span> Tambah Obat Baru
            </a>
        </div>

        <div
            class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden flex flex-col h-full">

            <form method="GET" action="{{ route('petugas.obat') }}"
                class="p-4 border-b border-outline-variant bg-surface-container-low flex flex-col md:flex-row gap-4 items-center justify-between">

                <div class="relative w-full md:w-96 flex">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
                    <input name="search" value="{{ $search }}"
                        class="w-full pl-9 pr-4 py-2 text-sm bg-surface-container-lowest border border-outline-variant text-on-surface placeholder:text-on-surface-variant/70 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Cari nama obat atau ID..." type="text" />
                </div>

                <div class="flex gap-3 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">

                    <div class="relative">
                        <select name="kategori" onchange="this.form.submit()"
                            class="text-sm bg-surface-container-lowest border border-outline-variant rounded-lg pl-3 pr-10 py-2 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary min-w-[140px] cursor-pointer appearance-none bg-none shadow-sm">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat->id_kategori }}"
                                    {{ $kategoriPilihan == $kat->id_kategori ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span>
                        </div>
                    </div>

                    <div class="relative">
                        <select name="rak"
                            class="text-sm bg-surface-container-lowest border border-outline-variant rounded-lg pl-3 pr-10 py-2 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary min-w-[140px] cursor-pointer appearance-none bg-none shadow-sm">
                            <option value="">Semua Rak</option>
                            <option value="A">Rak A</option>
                            <option value="B">Rak B</option>
                            <option value="C">Rak C</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span>
                        </div>
                    </div>

                    <button type="button"
                        class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors flex items-center justify-center bg-surface-container-lowest shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">filter_list</span>
                    </button>

                    <button type="submit" class="hidden">Cari</button>
                </div>
            </form>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr
                            class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant text-xs uppercase tracking-wider font-bold">
                            <th class="p-4 py-3">ID Obat</th>
                            <th class="p-4 py-3">Nama Obat</th>
                            <th class="p-4 py-3">Kategori</th>
                            <th class="p-4 py-3">Letak Rak</th>
                            <th class="p-4 py-3 text-right">Stok Total</th>
                            <th class="p-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant text-sm text-on-surface">

                        @forelse($obatList as $obat)
                            @php $isKritis = $obat->total_stok <= $obat->batas_stok_min; @endphp

                            <tr
                                class="hover:bg-surface-container transition-colors group {{ $isKritis ? 'bg-error-container/30' : '' }}">
                                <td class="p-4 whitespace-nowrap font-mono text-on-surface-variant text-xs">
                                    {{ $obat->id_obat }}</td>
                                <td class="p-4">
                                    <div class="font-bold text-on-surface">{{ $obat->nama_obat }}
                                        {{ $obat->dosis }}{{ $obat->satuan_dosis }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ $obat->bentuk_sediaan }}</div>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-surface-container-highest text-on-surface-variant border border-outline-variant">
                                        {{ $obat->nama_kategori }}
                                    </span>
                                </td>
                                <td class="p-4 whitespace-nowrap text-on-surface-variant font-medium">
                                    {{ $obat->letak_rak }}</td>

                                <td
                                    class="p-4 whitespace-nowrap text-right font-bold text-lg {{ $isKritis ? 'text-error' : 'text-on-surface' }}">
                                    {{ $obat->total_stok }}
                                    <span
                                        class="text-xs font-normal text-on-surface-variant ml-1">{{ $obat->bentuk_sediaan }}</span>
                                </td>

                                <td class="p-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">

                                        <a href="{{ route('petugas.obat.edit', $obat->id_obat) }}"
                                            class="p-1.5 text-outline hover:text-primary hover:bg-primary-container/20 rounded-md transition-colors"
                                            title="Edit Data">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>

                                        <!-- Tombol Hapus memanggil fungsi JS -->
                                        <button type="button"
                                            onclick="openDeleteModal('{{ $obat->id_obat }}', '{{ $obat->nama_obat }}')"
                                            class="p-1.5 text-outline hover:text-error hover:bg-error-container/50 rounded-md transition-colors"
                                            title="Hapus Data">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-on-surface-variant">
                                    Tidak ada data obat yang ditemukan.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-outline-variant bg-surface-container-low">
                {{ $obatList->appends(request()->query())->links() }}
            </div>

        </div>
    </div>

    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden bg-on-surface/40 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity duration-300 opacity-0">
        <div id="deleteModalContent"
            class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-error-container mb-5">
                    <span class="material-symbols-outlined text-[32px] text-error"
                        style="font-variation-settings: 'FILL' 1;">warning</span>
                </div>

                <h3 class="text-xl font-bold text-on-surface mb-2">Hapus Obat?</h3>
                <p class="text-sm text-on-surface-variant mb-6">
                    Apakah Anda yakin ingin menghapus <strong id="deleteObjectName" class="text-on-surface"></strong> dari
                    katalog? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex gap-3 justify-center">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-colors font-semibold text-sm">
                        Batal
                    </button>
                    <form id="deleteForm" method="POST" action="" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full px-4 py-2.5 rounded-lg bg-error text-on-error hover:bg-error/90 transition-colors font-semibold text-sm">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(idObat, namaObat) {
            const modal = document.getElementById('deleteModal');
            const modalContent = document.getElementById('deleteModalContent');
            const deleteForm = document.getElementById('deleteForm');
            const deleteObjectName = document.getElementById('deleteObjectName');

            deleteObjectName.textContent = namaObat;

            let baseUrl = "{{ route('petugas.obat.hapus', ':id') }}";
            deleteForm.action = baseUrl.replace(':id', idObat);

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = document.getElementById('deleteModalContent');

            modal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
@endsection
