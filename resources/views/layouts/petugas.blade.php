<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>SIOPAL - {{ $title ?? 'Petugas Apotek' }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#00685f",
                        "primary-container": "#008378",
                        "background": "#f5faf8",
                        "surface": "#f5faf8",
                        "surface-container-low": "#f0f5f2",
                        "on-surface": "#171d1c",
                        "on-surface-variant": "#3d4947",
                        "outline-variant": "#bcc9c6",
                        "error": "#ba1a1a",
                    },
                },
            },
        }
    </script>
    <style>
        body {
            background-color: #f5faf8;
            color: #171d1c;
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0;
        }

        .ambient-shadow {
            box-shadow: 0 4px 15px -3px rgba(100, 116, 139, 0.04);
        }
    </style>
</head>

<body class="flex min-h-screen bg-background">

    <aside
        class="bg-surface-container-low text-primary hidden md:flex flex-col h-screen w-64 fixed left-0 top-0 border-r border-outline-variant py-4 space-y-2 z-40">
        <div class="px-8 mb-8 flex items-center gap-4 -mt-3">
            <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/Logo SIOPAL.png') }}" alt="Logo SIOPAL" class="h-10 w-auto object-contain drop-shadow-sm">
            </a>
        </div>
        <nav class="flex-1 px-2 space-y-1">
            <a class="flex items-center rounded-lg px-4 py-3 mx-2 -mt-6 font-semibold transition-all {{ Request::is('petugas/dashboard') ? 'bg-primary-container text-white hover:bg-primary' : 'text-on-surface-variant hover:bg-teal-50 hover:text-primary' }}"
                href="{{ route('petugas.dashboard') }}">
                <span class="material-symbols-outlined mr-3">dashboard</span> Dashboard
            </a>

            <a class="flex items-center rounded-lg px-4 py-3 mx-2 font-semibold transition-all {{ Request::is('petugas/obat*') ? 'bg-primary-container text-white hover:bg-primary' : 'text-on-surface-variant hover:bg-teal-50 hover:text-primary' }}"
                href="{{ route('petugas.obat') }}">
                <span class="material-symbols-outlined mr-3">medication</span> Katalog Obat
            </a>

            <a class="flex items-center rounded-lg px-4 py-3 mx-2 font-semibold transition-all {{ Request::is('petugas/masuk*') ? 'bg-primary-container text-white hover:bg-primary' : 'text-on-surface-variant hover:bg-teal-50 hover:text-primary' }}"
                href="{{ route('petugas.masuk') }}">
                <span class="material-symbols-outlined mr-3">move_to_inbox</span> Obat Masuk
            </a>

            <a class="flex items-center rounded-lg px-4 py-3 mx-2 font-semibold transition-all {{ Request::is('petugas/keluar*') ? 'bg-primary-container text-white hover:bg-primary' : 'text-on-surface-variant hover:bg-teal-50 hover:text-primary' }}"
                href="{{ route('petugas.keluar') }}">
                <span class="material-symbols-outlined mr-3">outbox</span> Obat Keluar
            </a>

            <a class="flex items-center rounded-lg px-4 py-3 mx-2 font-semibold transition-all {{ Request::is('petugas/opname*') ? 'bg-primary-container text-white hover:bg-primary' : 'text-on-surface-variant hover:bg-teal-50 hover:text-primary' }}"
                href="{{ route('petugas.opname') }}">
                <span class="material-symbols-outlined mr-3">fact_check</span> Stok Opname
            </a>
        </nav>

        <div class="px-4 mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center text-error px-4 py-3 font-semibold transition-all hover:bg-red-50 rounded-lg">
                    <span class="material-symbols-outlined mr-3">logout</span> Keluar
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 md:ml-64 bg-background min-h-screen relative">
        <header
            class="bg-surface flex justify-between items-center h-16 px-6 w-full sticky top-0 z-50 border-b border-outline-variant shadow-sm">
            <button class="md:hidden text-on-surface-variant mr-4">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <div class="flex items-center space-x-4 ml-auto">

                @php
                    $unreadNotifCount = \App\Models\Notifikasi::where('status_baca', 'Belum')->count();
                @endphp

                <a href="{{ route('petugas.notifikasi') }}"
                    class="text-on-surface-variant hover:bg-gray-100 p-2 rounded-full relative transition-colors">
                    <span class="material-symbols-outlined">notifications</span>

                    @if ($unreadNotifCount > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
                    @endif
                </a>

                <div class="flex items-center gap-3 ml-2 border-l pl-4 border-outline-variant">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold text-on-surface">{{ Auth::user()->nama_lengkap }}</p>
                        <p class="text-[10px] text-on-surface-variant italic">{{ Auth::user()->peran }}</p>
                    </div>

                    <a href="{{ route('petugas.profil') }}"
                        class="flex items-center hover:opacity-80 transition-opacity">
                        <img alt="User profile" class="w-8 h-8 rounded-full border border-primary"
                            src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama_lengkap) }}&background=00685f&color=fff">
                    </a>
                </div>
            </div>
        </header>

        <div class="p-6 md:p-8 w-full">
            @yield('content')
        </div>
    </main>

</body>

</html>
