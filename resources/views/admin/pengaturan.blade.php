@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">Pengaturan Sistem</h2>
        <p class="text-gray-600 max-w-2xl text-sm leading-relaxed">
            Konfigurasi parameter aplikasi, kebijakan keamanan, dan pemeliharaan basis data. Perubahan pada halaman ini berdampak global.
        </p>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 text-sm font-medium border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.update-pengaturan') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">store</span>
                            Identitas Aplikasi
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Nama Apotek</label>
                            <input name="nama_apotek" value="{{ old('nama_apotek', $pengaturan->nama_apotek) }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-gray-800 focus:ring-2 focus:ring-primary focus:border-primary transition-shadow text-sm" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Alamat & Kontak (Kop Laporan)</label>
                            <textarea name="alamat_apotek" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-gray-800 focus:ring-2 focus:ring-primary focus:border-primary transition-shadow text-sm resize-none" rows="3" required>{{ old('alamat_apotek', $pengaturan->alamat_apotek) }}</textarea>
                        </div>
                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-teal-700 transition-colors shadow-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">database</span>
                            Pemeliharaan Data
                        </h3>
                    </div>
                    <div class="p-0">
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-0.5">Database SQL</h4>
                                <p class="text-xs text-gray-500">Cadangkan seluruh struktur dan isi database saat ini.</p>
                            </div>
                            <button type="button" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-teal-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                                <span class="material-symbols-outlined text-[18px]">cloud_download</span> Backup SQL
                            </button>
                        </div>
                        <div class="px-6 py-5 flex items-center justify-between hover:bg-red-50/50 transition-colors bg-red-50/30">
                            <div>
                                <h4 class="text-sm font-semibold text-red-600 mb-0.5">Data Transaksi</h4>
                                <p class="text-xs text-red-500/80">Tindakan destruktif: Hapus semua riwayat transaksi.</p>
                            </div>
                            <button type="button" class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-600 hover:text-white transition-colors flex items-center gap-2 whitespace-nowrap">
                                <span class="material-symbols-outlined text-[18px]">warning</span> Reset Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">security</span>
                            Kebijakan Keamanan
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-800 mb-0.5">Wajibkan Sandi Kuat</h4>
                                <p class="text-xs text-gray-500 leading-snug">Mengharuskan huruf besar, angka, & simbol.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                                <input type="checkbox" name="wajib_password_kuat" class="sr-only peer" {{ $pengaturan->wajib_password_kuat ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-800 mb-0.5">Otomatis Logout</h4>
                                <p class="text-xs text-gray-500 leading-snug">Sesi berakhir setelah 30 menit pasif.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                                <input type="checkbox" name="auto_logout" class="sr-only peer" {{ $pengaturan->auto_logout ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-gray-800 mb-0.5">Log Audit Global</h4>
                                <p class="text-xs text-gray-500 leading-snug">Rekam semua perubahan oleh pengguna.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-1">
                                <input type="checkbox" name="log_audit_global" class="sr-only peer" {{ $pengaturan->log_audit_global ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 text-sm">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500 text-[18px]">info</span>
                        Informasi Sistem
                    </h4>
                    <div class="space-y-2 text-gray-500 text-xs">
                        <div class="flex justify-between"><span>Versi SIOPAL</span> <span class="font-mono text-gray-800 font-medium">v2.4.1-stable</span></div>
                        <div class="flex justify-between"><span>Terakhir Diperbarui</span> <span class="text-gray-800 font-medium">{{ \Carbon\Carbon::parse($pengaturan->updated_at)->format('d M Y, H:i') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
