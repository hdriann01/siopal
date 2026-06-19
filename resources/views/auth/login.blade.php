<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - SIOPAL</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo Browser SIOPAL.png') }}"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#00685f",
                        "on-primary": "#ffffff",
                        "primary-container": "#008378",
                        "on-primary-container": "#f4fffc",
                        "primary-fixed": "#89f5e7",
                        "primary-fixed-dim": "#6bd8cb",
                        "on-primary-fixed": "#00201d",
                        "on-primary-fixed-variant": "#005049",
                        "surface": "#f5faf8",
                        "on-surface": "#171d1c",
                        "surface-variant": "#dee4e1",
                        "on-surface-variant": "#3d4947",
                        "outline": "#6d7a77",
                        "outline-variant": "#bcc9c6",
                        "background": "#f5faf8",
                        "on-background": "#171d1c",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f5f2",
                        "surface-container": "#eaefed",
                        "surface-container-high": "#e4e9e7"
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-on-surface min-h-screen">
    <main class="flex h-screen w-full overflow-hidden">

        <section class="hidden lg:flex lg:w-1/2 relative bg-primary items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img alt="Pharmacy Backdrop" class="w-full h-full object-cover blur-sm scale-110" src="{{ asset('images/Background Login.jpg') }}"/>

                <div class="absolute inset-0 bg-primary/40 mix-blend-multiply"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-primary/60 to-transparent"></div>
            </div>

            <div class="relative z-10 p-12 text-on-primary max-w-lg">
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ asset('images/Logo SIOPAL.png') }}" alt="Logo SIOPAL" class="h-16 w-auto object-contain drop-shadow-md">
                </div>

                <h2 class="text-3xl font-bold mb-4 leading-tight">Presisi dalam Setiap Sediaan Farmasi.</h2>
                <p class="text-lg text-primary-fixed leading-relaxed opacity-90">
                    Sistem Manajemen Inventaris Obat Internal yang dirancang untuk kecepatan, akurasi, dan keamanan data apotek Anda.
                </p>
                <div class="mt-12 flex gap-6">
                    <div class="flex flex-col">
                        <span class="text-3xl font-bold text-white">100%</span>
                        <span class="text-sm text-primary-fixed-dim">Terintegrasi</span>
                    </div>
                    <div class="h-10 w-[1px] bg-primary-fixed/30 self-center"></div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-bold text-white">24/7</span>
                        <span class="text-sm text-primary-fixed-dim">Pemantauan</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="w-full lg:w-1/2 flex items-center justify-center bg-background p-6 md:p-12">
            <div class="w-full max-w-md bg-surface-container-lowest p-10 rounded-xl shadow-[0_15px_35px_-10px_rgba(100,116,139,0.08)] border border-outline-variant">

                <div class="mb-10 text-center lg:text-left">
                    <div class="flex items-center justify-center lg:justify-start gap-2 mb-4">
                        <img src="{{ asset('images/Logo SIOPAL.png') }}" alt="Logo SIOPAL" class="h-10 w-auto object-contain">
                    </div>
                    <p class="text-xs font-bold tracking-wider text-on-surface-variant/70 uppercase">Sistem Informasi Stok Obat Apotek Internal</p>
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h3 class="text-2xl font-bold text-on-surface mb-2">Selamat Datang</h3>
                    <p class="text-on-surface-variant font-medium text-sm">Silakan masuk ke akun Anda untuk mengelola sediaan farmasi.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                        <span class="material-symbols-outlined text-red-600 text-[20px]">error</span>
                        <p class="text-sm text-red-700 font-medium">Username atau Password yang Anda masukkan tidak sesuai.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf <div class="space-y-2">
                        <label class="text-sm font-bold text-on-surface-variant flex items-center gap-2" for="username">
                            <span class="material-symbols-outlined text-[18px]">person</span> Nama Pengguna
                        </label>
                        <div class="relative">
                            <input class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/60 text-gray-800" id="username" name="username" placeholder="Masukkan username Anda" type="text" value="{{ old('username') }}" required autofocus/>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="text-sm font-bold text-on-surface-variant flex items-center gap-2" for="password">
                                <span class="material-symbols-outlined text-[18px]">lock</span> Kata Sandi
                            </label>
                            <a class="text-sm font-bold text-primary hover:underline transition-all" href="#">Lupa Kata Sandi?</a>
                        </div>
                        <div class="relative">
                            <input class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/60 text-gray-800" id="password" name="password" placeholder="••••••••" type="password" required/>
                        </div>
                    </div>

                    <button class="w-full bg-primary text-on-primary py-3.5 px-6 rounded-lg font-bold hover:bg-primary-container active:scale-[0.98] transition-all shadow-md flex items-center justify-center gap-2" type="submit">
                        Masuk ke Sistem
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-12 flex flex-col items-center gap-4">
                    <div class="flex items-center gap-2 px-4 py-2 bg-surface-container rounded-full">
                        <span class="material-symbols-outlined text-primary text-[18px]">verified_user</span>
                        <p class="text-xs font-medium text-on-surface-variant">Koneksi Aman Terenkripsi</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
