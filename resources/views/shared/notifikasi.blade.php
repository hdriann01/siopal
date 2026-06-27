@extends($layout)

@section('content')
    <div class="pb-24">

        @if (session('success'))
            <div class="mb-6 p-4 bg-teal-50 border-l-4 border-primary text-primary-fixed-variant rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-2xl font-display font-bold text-on-surface mb-2 tracking-tight">Pusat Notifikasi</h2>
                <p class="text-on-surface-variant text-sm">
                    @if ($role == 'admin')
                        Pantau log aktivitas keamanan dan sistem apotek.
                    @elseif($role == 'kepala')
                        Pantau status pengajuan persetujuan dan laporan penting.
                    @else
                        Pantau status pengajuan data dan peringatan stok sediaan farmasi.
                    @endif
                </p>
            </div>

            <form action="{{ route('notifikasi.baca-semua') }}" method="POST">
                @csrf
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

        <div class="flex border-b border-outline-variant mb-8 gap-8 overflow-x-auto whitespace-nowrap">
            <button class="filter-btn pb-3 text-sm font-bold border-b-2 border-primary text-primary px-1 transition-all"
                data-filter="Semua">Semua</button>
            @if ($role == 'petugas')
                <button
                    class="filter-btn pb-3 text-sm font-bold text-on-surface-variant hover:text-primary px-1 transition-all"
                    data-filter="Faktur">Status Verifikasi</button>
                <button
                    class="filter-btn pb-3 text-sm font-bold text-on-surface-variant hover:text-primary px-1 transition-all"
                    data-filter="Stok">Peringatan Stok</button>
            @elseif($role == 'kepala')
                <button
                    class="filter-btn pb-3 text-sm font-bold text-on-surface-variant hover:text-primary px-1 transition-all"
                    data-filter="Faktur">Menunggu Persetujuan</button>
            @elseif($role == 'admin')
                <button
                    class="filter-btn pb-3 text-sm font-bold text-on-surface-variant hover:text-primary px-1 transition-all"
                    data-filter="Keamanan">Log Keamanan</button>
            @endif
        </div>

        <div class="flex flex-col gap-4" id="notif-container">

            <div id="empty-filter-message"
                class="hidden flex-col items-center justify-center py-16 px-4 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm text-center transition-all">
                <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[32px] text-outline">filter_list_off</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-2">Tidak Ada Hasil</h3>
                <p class="text-sm text-on-surface-variant max-w-sm">Tidak ada notifikasi yang sesuai dengan kategori filter
                    yang Anda pilih.</p>
            </div>

            @forelse($notifikasiList as $notif)
                @php
                    $isRead = $notif->status_baca == 'Sudah';

                    $jenisAsli = trim($notif->tipe);
                    $jenisLower = strtolower($jenisAsli);

                    $cardClass = 'rounded-xl p-5 flex gap-4 relative overflow-hidden transition-all ';
                    $barClass = '';
                    $iconContainer = '';
                    $iconName = 'notifications';
                    $iconColor = '';
                    $titleColor = 'text-on-surface';
                    $textColor = 'text-on-surface-variant';
                    $timeColor = 'text-primary';

                    if ($isRead) {
                        $cardClass .= 'bg-surface-container-lowest border border-outline-variant opacity-75';
                        $barClass = 'bg-outline-variant';
                        $iconContainer = 'bg-surface-container-high';
                        $iconColor = 'text-on-surface-variant';
                        $titleColor = 'text-on-surface-variant';
                        $timeColor = 'text-outline';

                        if (str_contains($jenisLower, 'faktur') || str_contains($jenisLower, 'setuju')) {
                            $iconName = 'check_circle';
                        } elseif (str_contains($jenisLower, 'stok') || str_contains($jenisLower, 'aman')) {
                            $iconName = 'warning';
                        } else {
                            $iconName = 'info';
                        }
                    } else {
                        if (str_contains($jenisLower, 'stok') || str_contains($jenisLower, 'aman')) {
                            $cardClass .= 'bg-error text-white shadow-md border border-error';
                            $barClass = 'bg-white/30';
                            $iconContainer = 'bg-white/20';
                            $iconColor = 'text-white';
                            $titleColor = 'text-white';
                            $textColor = 'text-white/90';
                            $timeColor = 'text-error-container';
                            $iconName = 'warning';
                        } else {
                            $cardClass .= 'bg-surface-container-lowest border border-outline-variant shadow-sm';
                            $barClass = 'bg-primary';
                            $iconContainer = 'bg-primary-container/20';
                            $iconColor = 'text-primary';
                            $iconName = 'notifications_active';
                        }
                    }
                @endphp

                <div class="{{ $cardClass }} notif-card" data-type="{{ $jenisAsli }}">
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $barClass }}"></div>
                    <div class="mt-1">
                        <div class="w-10 h-10 rounded-full {{ $iconContainer }} flex items-center justify-center">
                            <span
                                class="material-symbols-outlined text-[20px] {{ $iconColor }}">{{ $iconName }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-base {{ $titleColor }}">{{ $notif->judul }}</h3>
                            <span class="text-xs font-bold {{ $timeColor }}">
                                @if (!$isRead && !str_contains($jenisLower, 'stok') && !str_contains($jenisLower, 'aman'))
                                    <span class="bg-primary-container/20 px-2 py-0.5 rounded-full mr-1">Baru</span>
                                @endif
                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm leading-relaxed {{ $textColor }}">{!! $notif->pesan !!}</p>
                    </div>
                </div>

            @empty
                <div
                    class="flex flex-col items-center justify-center py-16 px-4 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm text-center">
                    <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-[40px] text-outline">notifications_off</span>
                    </div>
                    <h3 class="text-lg font-bold text-on-surface mb-2">Belum Ada Notifikasi</h3>
                    <p class="text-sm text-on-surface-variant max-w-sm">Saat ini daftar notifikasi Anda masih kosong.</p>
                </div>
            @endforelse
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('border-b-2 border-primary text-primary').addClass(
                    'text-on-surface-variant hover:text-primary');
                $(this).removeClass('text-on-surface-variant hover:text-primary').addClass(
                    'border-b-2 border-primary text-primary');

                var filterRaw = $(this).data('filter');
                var filterValue = String(filterRaw).trim().toLowerCase();
                var matchCount = 0;

                $('#empty-filter-message').addClass('hidden').removeClass('flex');

                if (filterValue === 'semua') {
                    $('.notif-card').fadeIn(300);
                    matchCount = $('.notif-card').length;
                } else {
                    $('.notif-card').hide();

                    var matchedCards = $('.notif-card').filter(function() {
                        var cardType = String($(this).attr('data-type')).trim().toLowerCase();
                        var cardTitle = String($(this).find('h3').text()).trim().toLowerCase();

                        var matchReverse = (cardType !== '' && filterValue.includes(cardType));

                        return cardType === filterValue || cardTitle.includes(filterValue) ||
                            matchReverse;
                    });

                    matchedCards.fadeIn(300);
                    matchCount = matchedCards.length;
                }

                if (matchCount === 0 && $('.notif-card').length > 0) {
                    $('#empty-filter-message').removeClass('hidden').addClass('flex');
                }
            });
        });
    </script>
@endsection
