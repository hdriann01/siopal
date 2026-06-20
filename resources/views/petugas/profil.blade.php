@extends('layouts.petugas')

@section('content')
<div class="max-w-2xl mx-auto pb-24">

    @if(session('success'))
        <div class="mb-6 p-4 bg-teal-50 border-l-4 border-primary text-primary-fixed-variant rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">

        <div class="p-8 border-b border-outline-variant bg-surface-container-low/50">
            <h2 class="text-2xl font-bold font-display text-on-surface mb-2 tracking-tight">Profil Saya</h2>
            <p class="text-on-surface-variant text-sm max-w-md">Kelola informasi pribadi dan foto profil Anda untuk keperluan operasional apotek.</p>
        </div>

        <div class="p-8">

            <div class="flex flex-col items-center mb-10">
                <div class="relative group cursor-pointer">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-surface shadow-sm ring-1 ring-outline-variant bg-surface-container-high">
                        <img alt="Profile Picture" class="w-full h-full object-cover transition-opacity group-hover:opacity-90" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4oVSTbciMql90_0EB5wh_K7tlMnSjx6fHKOP_G8NXsr9nVcZRQAFMM6Y6niCXyEZQa1O4L3LRs2uKgwgsQkS0n-OA16XE0ky7nNfMFMjPnmhK_9qLTBj9Lq5USnatGgWV3-y3JcSoJYPMubs9GYpz3m4WbquSeTRt0IXzdsy6os0LofrDcx7VjdT2Y6HBhBHflvtta3j43yT8kK2802-Qqqg_8ViKKi4xHiQo82M907mNyt_p2NR5pFrq6ZodEznbm85wSeYj"/>
                    </div>
                    <button aria-label="Upload photo" class="absolute bottom-1 right-1 w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center shadow-md border-2 border-surface hover:bg-primary-container hover:text-on-primary-container transition-colors active:scale-95">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">photo_camera</span>
                    </button>
                </div>
                <span class="mt-4 text-xs text-on-surface-variant font-medium uppercase tracking-wider">Format: JPG, PNG. Maks: 2MB</span>
            </div>

            <form action="{{ route('petugas.update-profil') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface font-label" for="nama_lengkap">Nama Lengkap</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined text-xl">person</span>
                                    <input class="w-full bg-surface-container-low border border-outline-variant rounded-lg py-2.5 pl-10 pr-4 text-on-surface text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none"
                                        id="nama_lengkap"
                                        name="nama_lengkap"
                                        type="text"
                                        value="{{ Auth::user()->nama_lengkap ?? 'Budi Santoso' }}"/>
                            </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface font-label" for="username">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined text-xl">alternate_email</span>
                            <input class="w-full bg-surface-container-low border border-outline-variant rounded-lg py-2.5 pl-10 pr-4 text-on-surface text-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none" id="username" name="username" type="text" value="{{ Auth::user()->username ?? 'budi_apoteker' }}"/>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-outline-variant mt-6">
                    <label class="block text-sm font-semibold text-on-surface font-label mt-4">Peran / Jabatan Sistem</label>
                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-container/20 border border-primary-container text-primary-fixed-variant rounded-lg font-bold text-sm">
                            <span class="material-symbols-outlined text-lg">badge</span>
                            {{ ucfirst(Auth::user()->role ?? 'Petugas Apotek') }}
                        </div>
                        <span class="text-xs text-on-surface-variant italic font-medium">Hanya dapat diubah oleh Administrator.</span>
                    </div>
                </div>

                <div class="pt-8 flex justify-end gap-4 mt-8 border-t border-outline-variant">
                    <a href="{{ route('petugas.dashboard') }}" class="px-6 py-2.5 text-sm font-semibold text-on-surface-variant border border-outline-variant hover:bg-surface-container transition-colors rounded-lg">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary/90 shadow-sm active:scale-[0.98] transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-white" style="font-variation-settings: 'FILL' 1;">save</span>
                        Simpan Profil
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
