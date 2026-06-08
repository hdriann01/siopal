@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto w-full space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-on-surface mb-1">Pusat Notifikasi</h2>
                <p class="text-on-surface-variant font-medium">Pemberitahuan terkait keamanan, aktivitas akun, dan status
                    server.</p>
            </div>

            <form action="{{ route('admin.baca-semua-notif') }}" method="POST" class="self-start sm:self-auto">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-primary bg-white border border-outline-variant rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                    <span class="material-symbols-outlined text-sm">done_all</span>
                    Tandai semua dibaca
                </button>
            </form>
        </div>

        <div class="flex border-b border-gray-200 mb-6">
            <a href="{{ route('admin.notifikasi', ['tab' => 'Semua']) }}"
                class="px-6 py-3 text-sm {{ $activeTab == 'Semua' ? 'font-bold text-primary border-b-2 border-primary' : 'font-semibold text-gray-500 hover:text-gray-800 transition-colors' }}">
                Semua
            </a>
            <a href="{{ route('admin.notifikasi', ['tab' => 'Keamanan']) }}"
                class="px-6 py-3 text-sm {{ $activeTab == 'Keamanan' ? 'font-bold text-primary border-b-2 border-primary' : 'font-semibold text-gray-500 hover:text-gray-800 transition-colors' }}">
                Keamanan
            </a>
            <a href="{{ route('admin.notifikasi', ['tab' => 'Sistem']) }}"
                class="px-6 py-3 text-sm {{ $activeTab == 'Sistem' ? 'font-bold text-primary border-b-2 border-primary' : 'font-semibold text-gray-500 hover:text-gray-800 transition-colors' }}">
                Sistem
            </a>
        </div>

        <div class="flex flex-col gap-3">
            @forelse($notifikasi as $notif)
                <div
                    class="flex gap-4 p-5 rounded-xl border {{ $notif->status_baca == 'Belum' ? 'bg-teal-50 border-teal-100' : 'bg-white border-gray-100' }} transition-colors">

                    <div class="mt-1">
                        @if ($notif->tipe == 'Keamanan')
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                <span class="material-symbols-outlined text-[20px]">security</span>
                            </div>
                        @elseif($notif->tipe == 'Akun')
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[20px]">info</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-gray-900">{{ $notif->judul }}</span>
                            @if ($notif->status_baca == 'Belum')
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">{{ $notif->pesan }}</p>
                        <p class="text-xs font-medium text-gray-400">
                            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-xl border border-gray-100">
                    <span class="material-symbols-outlined text-gray-300 text-5xl mb-3">notifications_paused</span>
                    <p class="text-gray-500 font-medium">Belum ada notifikasi saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
