@extends('layouts.petugas')

@section('content')
<div class="pb-24">

    <!-- Page Header -->
    <div class="flex justify-between items-end mb-8">
        <div>
            <h2 class="text-2xl font-display font-bold text-on-surface mb-2 tracking-tight">Pusat Notifikasi</h2>
            <p class="text-on-surface-variant text-sm">Pantau status pengajuan data dan peringatan stok sediaan farmasi.</p>
        </div>

        <!-- BUNGKUS DENGAN FORM AGAR BISA MENGIRIM AKSI -->
        <form action="#" method="POST">
            @csrf
            <!--
              1. Ubah type menjadi "submit"
              2. Tambahkan atribut "disabled" secara dinamis
              3. Ubah kursor menjadi not-allowed saat kosong
            -->
            <button type="submit"
                class="px-4 py-2 text-sm font-bold rounded-lg transition-all
                {{ $hasUnread
                    ? 'bg-primary text-white shadow-sm hover:bg-primary/90 cursor-pointer'
                    : 'bg-surface-container-lowest text-primary border border-primary opacity-50 cursor-not-allowed' }}"
                {{ $hasUnread ? '' : 'disabled' }}>
                Tandai semua dibaca
            </button>
        </form>
    </div>

    <!-- Filter Tabs -->
    <div class="flex border-b border-outline-variant mb-8 gap-8 overflow-x-auto whitespace-nowrap">
        <button class="pb-3 text-sm font-bold text-primary border-b-2 border-primary relative px-1">Semua</button>
        <button class="pb-3 text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors px-1">Status Verifikasi</button>
        <button class="pb-3 text-sm font-bold text-on-surface-variant hover:text-on-surface transition-colors px-1">Peringatan Stok</button>
    </div>

    <!-- Notifications List -->
    <div class="flex flex-col gap-4">

        @forelse($notifikasiList as $notif)
            @php
                // --- LOGIKA PEWARNAAN OTOMATIS ---
                $isRead = $notif->is_read;
                $jenis = $notif->jenis;

                // Variabel bawaan (Default)
                $cardClass = "rounded-xl p-5 flex gap-4 relative overflow-hidden transition-all ";
                $barClass = ""; $iconContainer = ""; $iconName = "notifications"; $iconColor = "";
                $titleColor = "text-on-surface"; $textColor = "text-on-surface-variant"; $timeColor = "text-primary";

                if ($isRead) {
                    // JIKA SUDAH DIBACA: Semuanya diubah jadi abu-abu
                    $cardClass .= "bg-surface-container-lowest border border-outline-variant opacity-75 hover:opacity-100";
                    $barClass = "bg-outline-variant";
                    $iconContainer = "bg-surface-container-high";
                    $iconColor = "text-on-surface-variant";
                    $titleColor = "text-on-surface-variant";
                    $timeColor = "text-outline";

                    if ($jenis == 'Faktur') $iconName = 'check_circle';
                    elseif ($jenis == 'Otorisasi') $iconName = 'cancel';
                    elseif ($jenis == 'Stok Kritis') $iconName = 'warning';
                    else $iconName = 'calendar_today';

                } else {
                    // JIKA BELUM DIBACA:
                    if ($jenis == 'Stok Kritis') {
                        // KONDISI KHUSUS: Merah Full
                        $cardClass .= "bg-error text-white shadow-md border border-error";
                        $barClass = "bg-white/30";
                        $iconContainer = "bg-white/20";
                        $iconColor = "text-white";
                        $titleColor = "text-white";
                        $textColor = "text-white/90";
                        $timeColor = "text-error-container";
                        $iconName = 'warning';
                    } elseif ($jenis == 'Faktur') {
                        $cardClass .= "bg-surface-container-lowest border border-outline-variant shadow-sm hover:shadow-md";
                        $barClass = "bg-primary";
                        $iconContainer = "bg-primary-container/20";
                        $iconColor = "text-primary";
                        $iconName = 'check_circle';
                    } elseif ($jenis == 'Otorisasi') {
                        $cardClass .= "bg-surface-container-lowest border border-outline-variant shadow-sm hover:shadow-md";
                        $barClass = "bg-error";
                        $iconContainer = "bg-error-container/50";
                        $iconColor = "text-error";
                        $timeColor = "text-on-surface-variant";
                        $iconName = 'cancel';
                    } else {
                        $cardClass .= "bg-surface-container-lowest border border-outline-variant shadow-sm hover:shadow-md";
                        $barClass = "bg-outline-variant";
                        $iconContainer = "bg-surface-container-high";
                        $iconColor = "text-on-surface-variant";
                        $timeColor = "text-on-surface-variant";
                        $iconName = 'calendar_today';
                    }
                }
            @endphp

            <!-- Desain Kartu -->
            <div class="{{ $cardClass }}">
                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $barClass }}"></div>
                <div class="mt-1">
                    <div class="w-10 h-10 rounded-full {{ $iconContainer }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px] {{ $iconColor }}">{{ $iconName }}</span>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-base {{ $titleColor }}">{{ $notif->judul }}</h3>
                        <span class="text-xs font-bold {{ $timeColor }}">
                            @if(!$isRead && $jenis != 'Stok Kritis')
                                <span class="bg-primary-container/20 px-2 py-0.5 rounded-full mr-1">Baru</span>
                            @endif
                            {{ $notif->waktu }}
                        </span>
                    </div>
                    <p class="text-sm leading-relaxed {{ $textColor }}">{!! $notif->pesan !!}</p>
                </div>
            </div>

        @empty
            <!-- TAMPILAN JIKA DATA KOSONG -->
            <div class="flex flex-col items-center justify-center py-16 px-4 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm text-center">
                <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[40px] text-outline">notifications_off</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-2">Belum Ada Notifikasi</h3>
                <p class="text-sm text-on-surface-variant max-w-sm">Saat ini daftar notifikasi masih kosong karena belum ada balasan dari Kepala Apotek atau peringatan stok baru.</p>
            </div>
        @endforelse

    </div>

    <!-- Pagination / Load More -->
    @if(count($notifikasiList) > 0)
    <div class="mt-8 flex justify-center">
        <button class="px-6 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-high border border-outline-variant rounded-lg transition-colors">
            Muat Lebih Banyak
        </button>
    </div>
    @endif

</div>
@endsection
