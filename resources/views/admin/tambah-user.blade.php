@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-center min-h-[80vh]">
        <div class="bg-white rounded-xl shadow-sm border border-outline-variant w-full max-w-lg flex flex-col z-50">

            <div class="flex items-start justify-between p-6 border-b border-gray-100">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Tambah Pengguna Baru</h2>
                    <p class="text-sm text-gray-500 mt-1">Masukkan detail informasi staf untuk memberikan akses ke dalam
                        sistem.</p>
                </div>
            </div>

            <form action="{{ route('admin.simpan-user') }}" method="POST">
                @csrf

                <div class="p-6 space-y-5">
                    @if ($errors->any())
                        <div class="bg-red-50 text-red-500 p-3 rounded-lg text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700" for="nama_lengkap">Nama Lengkap</label>
                        <input name="nama_lengkap" id="nama_lengkap" type="text" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow"
                            placeholder="Masukkan nama asli staf">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700" for="username">Username</label>
                        <input name="username" id="username" type="text" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow"
                            placeholder="Buat username untuk login">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700" for="peran">Peran</label>
                        <div class="relative">
                            <select name="peran" id="peran" required
                                class="bg-none w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 appearance-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow">
                                <option disabled selected value="">Pilih Peran</option>
                                <option value="Petugas Apotek">Petugas Apotek</option>
                                <option value="Kepala Apotek">Kepala Apotek</option>
                                <option value="Administrator">Administrator</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <span class="material-symbols-outlined text-[20px]">arrow_drop_down</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
                        <div class="relative">
                            <input name="password" id="password" type="password" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-4 pr-10 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow"
                                placeholder="Buat kata sandi minimal 6 karakter">

                            <!-- ID togglePassword ditambahkan di sini -->
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-primary transition-colors">
                                <!-- ID eyeIcon ditambahkan di sini -->
                                <span id="eyeIcon" class="material-symbols-outlined text-[20px]">visibility_off</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-100 bg-gray-50/50 rounded-b-xl">
                    <a href="{{ route('admin.manajemen-user') }}"
                        class="px-5 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700 shadow-sm transition-colors">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    eyeIcon.textContent = 'visibility';
                    this.classList.remove('text-gray-400');
                    this.classList.add('text-primary');
                } else {
                    eyeIcon.textContent =
                        'visibility_off';
                    this.classList.remove('text-primary');
                    this.classList.add('text-gray-400');
                }
            });
        });
    </script>
@endsection
