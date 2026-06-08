<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIOPAL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-medical-teal {
            background-color: #0D9488;
        }

        .text-medical-teal {
            color: #0D9488;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-10 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-medical-teal tracking-tight">SIOPAL</h1>
            <p class="text-gray-500 text-sm mt-2">Sistem Informasi Stok Obat Apotek Internal</p>
        </div>

        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Selamat Datang</h2>
            <p class="text-gray-500 text-sm">Silakan masuk ke akun Anda untuk mengelola sediaan farmasi.</p>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 text-red-500 p-3 rounded-lg text-sm mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition"
                        placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    <input type="password" name="password"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition"
                        placeholder="Masukkan kata sandi" required>
                </div>
                <div class="text-right mt-2">
                    <a href="#" class="text-sm text-medical-teal hover:underline">Lupa Kata Sandi?</a>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-medical-teal text-white font-semibold py-2 rounded-lg hover:bg-teal-700 transition duration-300 shadow-md">
                Masuk ke Sistem
            </button>
        </form>

        <p class="text-center text-gray-400 text-xs mt-8">
            Masalah login? Hubungi Administrator.
        </p>
    </div>

</body>

</html>
