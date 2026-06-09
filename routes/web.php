<?php

# Mengimpor AuthController agar rute bisa menggunakan fungsi login dan logout
use App\Http\Controllers\AuthController;
# Mengimpor facade Route bawaan Laravel untuk mendaftarkan URL aplikasi
use Illuminate\Support\Facades\Route;
# Mengimpor DashboardController agar rute bisa menggunakan fungsi-fungsi halaman aplikasi
use App\Http\Controllers\DashboardController;

# --- RUTE DASAR ---
# Jika pengguna mengakses URL root atau utama web (misal: localhost:8000/),
# sistem akan langsung mengarahkannya (redirect) secara otomatis ke halaman '/login'
Route::get('/', function () {
    return redirect('/login');
});

# --- RUTE AUTENTIKASI (LOGIN & LOGOUT) ---
# Rute (GET) untuk menampilkan antarmuka form login. Diberi nama 'login' agar mudah dikenali oleh middleware autentikasi.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

# Rute (POST) untuk menangani pengiriman data username dan password yang diketik dari form login
Route::post('/login', [AuthController::class, 'login']);

# Rute (POST) untuk menangani proses keluar (logout) dari sistem, menghancurkan sesi yang sedang aktif.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


# --- GRUP RUTE TERLINDUNGI (MIDDLEWARE AUTH) ---
# Semua rute di dalam grup ini WAJIB melewati pengecekan middleware 'auth'.
# Artinya, hanya pengguna yang sudah berhasil login yang diizinkan untuk mengakses URL di bawah ini.
Route::middleware(['auth'])->group(function () {

    # Rute untuk menampilkan halaman dashboard ringkasan utama bagi Administrator
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    # --- MANAJEMEN PENGGUNA ---
    # Rute untuk menampilkan tabel daftar semua pengguna dan menangani fitur pencarian
    Route::get('/admin/manajemen-user', [DashboardController::class, 'manajemenUser'])->name('admin.manajemen-user');

    # Rute (GET) untuk menampilkan form kosong untuk menambah pengguna baru
    Route::get('/admin/tambah-user', [DashboardController::class, 'tambahUser'])->name('admin.tambah-user');

    # Rute (POST) untuk menangani proses penyimpanan data pengguna baru tersebut ke dalam database
    Route::post('/admin/tambah-user', [DashboardController::class, 'simpanUser'])->name('admin.simpan-user');

    # Rute (GET) untuk menampilkan form edit pengguna beserta data lamanya, spesifik berdasarkan {id} pengguna
    Route::get('/admin/edit-user/{id}', [DashboardController::class, 'editUser'])->name('admin.edit-user');

    # Rute (PUT) untuk memproses perubahan (update) data profil pengguna ke tabel database
    Route::put('/admin/update-user/{id}', [DashboardController::class, 'updateUser'])->name('admin.update-user');

    # Rute (GET) untuk menampilkan form reset password khusus pengguna tertentu berdasarkan {id}
    Route::get('/admin/reset-password/{id}', [DashboardController::class, 'resetPassword'])->name('admin.reset-password');

    # Rute (PUT) untuk mengenkripsi dan menyimpan password baru pengguna yang direset ke database
    Route::put('/admin/update-password/{id}', [DashboardController::class, 'updatePassword'])->name('admin.update-password');

    # Rute (GET) untuk menampilkan halaman peringatan sebelum pengguna benar-benar dihapus
    Route::get('/admin/hapus-user/{id}', [DashboardController::class, 'konfirmasiHapus'])->name('admin.konfirmasi-hapus');

    # Rute (DELETE) untuk mengeksekusi penghapusan permanen data baris pengguna dari database
    Route::delete('/admin/proses-hapus/{id}', [DashboardController::class, 'prosesHapus'])->name('admin.proses-hapus');

    # --- PUSAT NOTIFIKASI ---
    # Rute (GET) untuk menampilkan daftar notifikasi sistem, termasuk memfilter tipe notifikasinya
    Route::get('/admin/notifikasi', [DashboardController::class, 'notifikasi'])->name('admin.notifikasi');

    # Rute (POST) untuk mengeksekusi perintah "Tandai semua dibaca" yang mengubah status notifikasi
    Route::post('/admin/notifikasi/baca-semua', [DashboardController::class, 'bacaSemuaNotifikasi'])->name('admin.baca-semua-notif');

    # --- PENGATURAN SISTEM ---
    # Rute (GET) untuk menampilkan antarmuka form pengaturan identitas aplikasi dan keamanan
    Route::get('/admin/pengaturan', [DashboardController::class, 'pengaturan'])->name('admin.pengaturan');

    # Rute (PUT) untuk menyimpan dan menimpa pembaruan konfigurasi pengaturan sistem ke database
    Route::put('/admin/pengaturan/update', [DashboardController::class, 'updatePengaturan'])->name('admin.update-pengaturan');

    # --- PROFIL PRIBADI ---
    # Rute (GET) untuk menampilkan informasi detail profil milik akun yang sedang melakukan login saat ini
    Route::get('/admin/profil', [DashboardController::class, 'profil'])->name('admin.profil');

    # Rute (PUT) untuk memproses pembaruan data (seperti nama & username) dari profil mandiri pengguna
    Route::put('/admin/profil/update', [DashboardController::class, 'updateProfil'])->name('admin.update-profil');

    # --- LOG AUDIT SISTEM ---
    # Rute (GET) untuk menampilkan tabel riwayat aktivitas (CCTV sistem) untuk semua aksi penting
    Route::get('/admin/audit-logs', [DashboardController::class, 'auditLogs'])->name('admin.audit-logs');

    # Rute (GET) khusus untuk merender (generate) file PDF laporan Log Audit dan langsung mendownloadnya
    Route::get('/admin/audit-logs/pdf', [DashboardController::class, 'exportPdfAuditLogs'])->name('admin.audit-logs.pdf');


    // --- GRUP RUTE KEPALA APOTEK ---
    Route::prefix('kepala')->name('kepala.')->group(function () {
        // Rute Dashboard yang sudah ada
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'kepala'])->name('dashboard');

        // Rute-rute baru untuk menu Sidebar (Sementara kita arahkan ke fungsi placeholder)
        Route::get('/validasi', [App\Http\Controllers\DashboardController::class, 'validasi'])->name('validasi');
        Route::get('/stok', [App\Http\Controllers\DashboardController::class, 'stok'])->name('stok');
        Route::get('/laporan', [App\Http\Controllers\DashboardController::class, 'laporan'])->name('laporan');

        // Rute untuk Navbar Atas
        Route::get('/notifikasi', [App\Http\Controllers\DashboardController::class, 'notifikasiKepala'])->name('notifikasi');
        Route::get('/profil', [App\Http\Controllers\DashboardController::class, 'profilKepala'])->name('profil');
    });

    # Rute untuk menampilkan halaman awal (dashboard) khusus untuk wewenang Petugas Apotek
    Route::get('/petugas/dashboard', [DashboardController::class, 'petugas'])->name('petugas.dashboard');
});
