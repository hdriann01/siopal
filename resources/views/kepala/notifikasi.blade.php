@extends('layouts.kepala')

@section('content')
    <div class="w-full space-y-6 pb-24">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-display font-bold text-gray-800 tracking-tight mb-1">Pusat Notifikasi</h2>
                <p class="text-gray-500 text-sm">Pantau daftar persetujuan tertunda dan peringatan dini stok obat.</p>
            </div>
            <a href="{{ route('kepala.notifikasi') }}"
                class="self-start sm:self-auto flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-teal-600 font-bold hover:bg-teal-50 transition-colors shadow-sm text-sm">
                <span class="material-symbols-outlined text-lg">sync</span> Refresh Data
            </a>
        </div>

        <div class="flex gap-2 border-b border-gray-200 mb-6 overflow-x-auto pb-px">
            <a href="{{ route('kepala.notifikasi', ['tab' => 'Semua']) }}"
                class="px-4 py-2 font-bold whitespace-nowrap {{ $activeTab == 'Semua' ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-500 hover:text-gray-800 transition-colors' }}">Semua</a>
            <a href="{{ route('kepala.notifikasi', ['tab' => 'Persetujuan']) }}"
                class="px-4 py-2 font-bold whitespace-nowrap {{ $activeTab == 'Persetujuan' ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-500 hover:text-gray-800 transition-colors' }}">Persetujuan</a>
            <a href="{{ route('kepala.notifikasi', ['tab' => 'Peringatan Stok']) }}"
                class="px-4 py-2 font-bold whitespace-nowrap {{ $activeTab == 'Peringatan Stok' ? 'text-teal-600 border-b-2 border-teal-600' : 'text-gray-500 hover:text-gray-800 transition-colors' }}">Peringatan
                Stok</a>
        </div>

        <div class="flex flex-col gap-4">
            @forelse($notifikasiList as $notif)
                @php
                    /* Logika penentuan warna Tailwind secara dinamis */
                    $borderColor = match ($notif->warna) {
                        'primary' => 'bg-teal-600',
                        'error' => 'bg-red-500',
                        default => 'bg-orange-500',
                    };
                    $iconBgColor = match ($notif->warna) {
                        'primary' => 'bg-teal-50 text-teal-600',
                        'error' => 'bg-red-50 text-red-600',
                        default => 'bg-orange-50 text-orange-600',
                    };
                    $textColor = match ($notif->warna) {
                        'primary' => 'text-teal-600',
                        'error' => 'text-red-600',
                        default => 'text-orange-600',
                    };
                @endphp

                <div
                    class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden flex flex-col sm:flex-row gap-4 items-start sm:items-center hover:bg-gray-50 transition-colors">
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $borderColor }}"></div>

                    <div class="w-10 h-10 rounded-full {{ $iconBgColor }} flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined">{{ $notif->ikon }}</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="text-xs font-bold uppercase tracking-wider {{ $textColor }}">{{ $notif->tipe }}</span>
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                {{ \Carbon\Carbon::parse($notif->waktu)->diffForHumans() }}
                            </span>
                        </div>
                        <h3 class="font-bold text-gray-800 text-lg truncate">{{ $notif->judul }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{!! $notif->pesan !!}</p>
                    </div>

                    <div class="flex gap-2 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                        <a href="{{ $notif->url }}"
                            class="flex-1 sm:flex-none px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold text-sm hover:bg-gray-100 transition-colors shadow-sm text-center">
                            Tindak Lanjut
                        </a>
                    </div>
                </div>
            @empty
                <div
                    class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-12 text-center flex flex-col items-center justify-center">
                    <div
                        class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-gray-400 mb-4 shadow-sm">
                        <span class="material-symbols-outlined text-3xl">notifications_paused</span>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">Semua Terkendali!</h3>
                    <p class="text-gray-500 text-sm">Tidak ada persetujuan tertunda atau peringatan stok saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
