@extends($layout)

@section('content')
    <div class="max-w-4xl mx-auto pb-24 space-y-8">
        <div>
            <h2 class="text-3xl font-display font-bold text-on-surface tracking-tight mb-2">Profil Pengguna</h2>
            <p class="text-on-surface-variant text-sm">Kelola informasi data diri dan kredensial akses sistem Anda.</p>
        </div>

        @if (session('success'))
            <div
                class="p-4 bg-primary-container text-on-primary-container rounded-lg font-bold flex items-center gap-3 shadow-sm">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-error-container text-on-error-container rounded-lg font-bold flex flex-col gap-2 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined">error</span>
                    <span>Terdapat kesalahan:</span>
                </div>
                <ul class="list-disc list-inside ml-8 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section
            class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-[0_4px_15px_-3px_rgba(100,116,139,0.04)] overflow-hidden">
            <div class="px-8 py-5 border-b border-outline-variant/50 bg-surface-container-low flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">manage_accounts</span>
                <h3 class="text-lg font-bold text-on-surface">Data Kredensial</h3>
            </div>

            <form action="{{ $actionUrl }}" method="POST" class="p-8 space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Peran /
                        Akses</label>
                    <input type="text" value="{{ $user->peran }}" disabled
                        class="w-full bg-surface-container-low border border-transparent rounded-lg px-4 py-3 text-sm text-primary font-bold cursor-not-allowed">
                </div>

                <div class="pt-6 border-t border-outline-variant/30">
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Nama
                        Lengkap <span class="text-error">*</span></label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                        required
                        class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Username
                        <span class="text-error">*</span></label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                        class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 text-sm text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="submit"
                        class="flex items-center gap-2 bg-primary hover:bg-primary/90 text-white font-bold px-8 py-3 rounded-lg transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
