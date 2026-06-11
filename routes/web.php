<?php

# =====================================================================
# 1. BAGIAN PERSIAPAN (IMPOR KELAS CONTROLLER)
# =====================================================================

# Mengimpor AuthController agar rute bisa memanggil fungsi Login dan Logout
use App\Http\Controllers\AuthController;
# Mengimpor facade Route bawaan Laravel untuk mendaftarkan alamat URL aplikasi
use Illuminate\Support\Facades\Route;
# Mengimpor DashboardController untuk menangani semua tampilan halaman utama aplikasi
use App\Http\Controllers\DashboardController;

# =====================================================================
# 2. RUTE DASAR & ALAMAT UTAMA
# =====================================================================

# Jika ada orang mengakses alamat utama (misal: localhost:8000/),
# sistem akan langsung 'melemparnya' (redirect) secara otomatis ke halaman login.
Route::get('/', function () {
    return redirect('/login');
});

# =====================================================================
# 3. RUTE AUTENTIKASI (PINTU MASUK & KELUAR)
# =====================================================================

# Rute (GET) untuk menampilkan desain halaman form login.
# Nama 'login' diberikan agar sistem Laravel mengenali ini sebagai pintu masuk utama.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

# Rute (POST) untuk mengirimkan data username & password yang diketik user ke otak sistem (Controller).
Route::post('/login', [AuthController::class, 'login']);

# Rute (POST) untuk menghancurkan sesi (session) dan mengeluarkan pengguna dari aplikasi.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

# =====================================================================
# 4. GRUP RUTE TERLINDUNGI (KHUSUS PENGGUNA YANG SUDAH LOGIN)
# =====================================================================

# Seluruh rute di bawah ini dibungkus middleware 'auth', artinya orang yang belum login
# dilarang keras mencoba mengakses URL-URL ini secara manual.
Route::middleware(['auth'])->group(function () {

    # --- MENU UTAMA & DASHBOARD ADMIN ---
    # Membuka halaman ringkasan data statistik utama bagi peran Administrator.
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    # --- MODUL MANAJEMEN PENGGUNA ---
    # Rute untuk melihat tabel daftar staf apotek dan melakukan pencarian nama.
    Route::get('/admin/manajemen-user', [DashboardController::class, 'manajemenUser'])->name('admin.manajemen-user');

    # Menampilkan form kosong untuk mendaftarkan pegawai baru ke sistem.
    Route::get('/admin/tambah-user', [DashboardController::class, 'tambahUser'])->name('admin.tambah-user');

    # Menyimpan data pegawai baru tersebut ke dalam database setelah tombol 'Simpan' ditekan.
    Route::post('/admin/tambah-user', [DashboardController::class, 'simpanUser'])->name('admin.simpan-user');

    # Membuka form untuk mengubah data staf tertentu berdasarkan ID-nya.
    Route::get('/admin/edit-user/{id}', [DashboardController::class, 'editUser'])->name('admin.edit-user');

    # Memproses perubahan data staf tersebut ke dalam tabel database (metode PUT).
    Route::put('/admin/update-user/{id}', [DashboardController::class, 'updateUser'])->name('admin.update-user');

    # Membuka form khusus untuk mengganti password staf yang lupa atau bermasalah.
    Route::get('/admin/reset-password/{id}', [DashboardController::class, 'resetPassword'])->name('admin.reset-password');

    # Memproses penyimpanan password baru yang sudah dienkripsi (Hash) ke database.
    Route::put('/admin/update-password/{id}', [DashboardController::class, 'updatePassword'])->name('admin.update-password');

    # Menampilkan layar konfirmasi 'Apakah anda yakin?' sebelum data benar-benar dihapus.
    Route::get('/admin/hapus-user/{id}', [DashboardController::class, 'konfirmasiHapus'])->name('admin.konfirmasi-hapus');

    # Menghapus baris data staf tersebut secara permanen dari database (metode DELETE).
    Route::delete('/admin/proses-hapus/{id}', [DashboardController::class, 'prosesHapus'])->name('admin.proses-hapus');

    # --- MODUL PUSAT NOTIFIKASI ---
    # Menampilkan daftar seluruh alarm dan peringatan sistem (keamanan/stok).
    Route::get('/admin/notifikasi', [DashboardController::class, 'notifikasi'])->name('admin.notifikasi');

    # Mengubah status seluruh notifikasi menjadi 'Sudah Dibaca' sekaligus.
    Route::post('/admin/notifikasi/baca-semua', [DashboardController::class, 'bacaSemuaNotifikasi'])->name('admin.baca-semua-notif');

    # --- MODUL PENGATURAN & PROFIL ---
    # Mengelola identitas apotek dan saklar keamanan global aplikasi.
    Route::get('/admin/pengaturan', [DashboardController::class, 'pengaturan'])->name('admin.pengaturan');

    # Menyimpan pembaruan konfigurasi pengaturan sistem.
    Route::put('/admin/pengaturan/update', [DashboardController::class, 'updatePengaturan'])->name('admin.update-pengaturan');

    # Melihat data profil pribadi dari akun yang sedang login saat ini.
    Route::get('/admin/profil', [DashboardController::class, 'profil'])->name('admin.profil');

    # Memperbarui informasi mandiri pengguna (seperti ganti nama/username).
    Route::put('/admin/profil/update', [DashboardController::class, 'updateProfil'])->name('admin.update-profil');

    # --- MODUL LOG AUDIT SISTEM ---
    # Menampilkan riwayat rekam jejak aktivitas (CCTV digital) seluruh pengguna.
    Route::get('/admin/audit-logs', [DashboardController::class, 'auditLogs'])->name('admin.audit-logs');

    # Mencetak data riwayat aktivitas tersebut ke dalam dokumen PDF untuk didownload.
    Route::get('/admin/audit-logs/pdf', [DashboardController::class, 'exportPdfAuditLogs'])->name('admin.audit-logs.pdf');

    # =====================================================================
    # 5. GRUP RUTE KEPALA APOTEK (FOLDER AWAL: KEPALA/)
    # =====================================================================

    # Seluruh rute di grup ini memiliki awalan URL /kepala/ (misal: /kepala/dashboard).
    Route::prefix('kepala')->name('kepala.')->group(function () {
        # Halaman dashboard khusus untuk manajer (Kepala Apotek).
        Route::get('/dashboard', [DashboardController::class, 'kepala'])->name('dashboard');
        # Rute-rute modul yang akan dikerjakan selanjutnya (placeholder).
        Route::get('/validasi', [DashboardController::class, 'validasi'])->name('validasi');
        Route::get('/stok', [DashboardController::class, 'stok'])->name('stok');
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan');
        Route::get('/notifikasi', [DashboardController::class, 'notifikasiKepala'])->name('notifikasi');
        Route::get('/profil', [DashboardController::class, 'profilKepala'])->name('profil');
    });

    # =====================================================================
    # 6. GRUP RUTE PETUGAS APOTEK (FOLDER AWAL: PETUGAS/)
    # =====================================================================

    # Seluruh rute di grup ini memiliki awalan URL /petugas/ (misal: /petugas/obat).
    Route::prefix('petugas')->name('petugas.')->group(function () {
        # Halaman dashboard operasional harian.
        Route::get('/dashboard', [DashboardController::class, 'petugas'])->name('dashboard');
        # Rute-rute modul operasional yang akan dikerjakan selanjutnya (placeholder).
        Route::get('/obat', [DashboardController::class, 'katalogObat'])->name('obat');
        Route::get('/masuk', [DashboardController::class, 'obatMasuk'])->name('masuk');
        Route::get('/keluar', [DashboardController::class, 'obatKeluar'])->name('keluar');
        Route::get('/opname', [DashboardController::class, 'stokOpname'])->name('opname');
        Route::get('/notifikasi', [DashboardController::class, 'notifikasiPetugas'])->name('notifikasi');
        Route::get('/profil', [DashboardController::class, 'profilPetugas'])->name('profil');
    });
});
