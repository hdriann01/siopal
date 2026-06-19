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

# Jika ada orang mengakses alamat utama aplikasi (misal: localhost:8000/),
# sistem akan langsung 'melemparnya' (redirect) secara otomatis ke halaman login.
Route::get('/', function () {
    return redirect('/login');
});

# =====================================================================
# 3. RUTE AUTENTIKASI (PINTU MASUK & KELUAR)
# =====================================================================

# Rute (GET) untuk menampilkan desain halaman form login.
# Nama 'login' diberikan agar sistem keamanan Laravel mengenali ini sebagai pintu masuk utama.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

# Rute (POST) untuk mengirimkan data username & password yang diketik pengguna ke otak sistem (Controller).
Route::post('/login', [AuthController::class, 'login']);

# Rute (POST) untuk menghancurkan sesi (session) yang sedang aktif dan mengeluarkan pengguna dari aplikasi.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

# =====================================================================
# 4. GRUP RUTE TERLINDUNGI (KHUSUS PENGGUNA YANG SUDAH LOGIN)
# =====================================================================

# Seluruh rute di bawah ini dibungkus middleware 'auth'. Artinya, sembarang orang yang belum login
# dilarang keras mencoba mengakses atau mengetik URL-URL ini secara manual di browser.
Route::middleware(['auth'])->group(function () {

    # --- MENU UTAMA & DASHBOARD ADMIN ---
    # Membuka halaman ringkasan data statistik utama bagi peran Administrator.
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    # --- MODUL MANAJEMEN PENGGUNA ---
    # Rute untuk melihat tabel daftar staf apotek dan melakukan pencarian nama.
    Route::get('/admin/manajemen-user', [DashboardController::class, 'manajemenUser'])->name('admin.manajemen-user');

    # Menampilkan form kosong untuk mendaftarkan pegawai baru ke dalam sistem.
    Route::get('/admin/tambah-user', [DashboardController::class, 'tambahUser'])->name('admin.tambah-user');

    # Menyimpan data pegawai baru tersebut ke dalam database setelah tombol 'Simpan' ditekan.
    Route::post('/admin/tambah-user', [DashboardController::class, 'simpanUser'])->name('admin.simpan-user');

    # Membuka form untuk mengubah data profil staf tertentu berdasarkan {id}-nya.
    Route::get('/admin/edit-user/{id}', [DashboardController::class, 'editUser'])->name('admin.edit-user');

    # Memproses perubahan data staf tersebut ke dalam tabel database (menggunakan metode PUT untuk update).
    Route::put('/admin/update-user/{id}', [DashboardController::class, 'updateUser'])->name('admin.update-user');

    # Membuka form khusus untuk mengganti kata sandi (password) staf yang lupa atau bermasalah.
    Route::get('/admin/reset-password/{id}', [DashboardController::class, 'resetPassword'])->name('admin.reset-password');

    # Memproses penyimpanan kata sandi baru yang sudah dienkripsi ke dalam database.
    Route::put('/admin/update-password/{id}', [DashboardController::class, 'updatePassword'])->name('admin.update-password');

    # Menampilkan layar konfirmasi 'Apakah anda yakin?' sebelum data staf benar-benar dihapus.
    Route::get('/admin/hapus-user/{id}', [DashboardController::class, 'konfirmasiHapus'])->name('admin.konfirmasi-hapus');

    # Menghapus baris data staf tersebut secara permanen dari database (menggunakan metode DELETE).
    Route::delete('/admin/proses-hapus/{id}', [DashboardController::class, 'prosesHapus'])->name('admin.proses-hapus');

    # --- MODUL PUSAT NOTIFIKASI ---
    # Menampilkan daftar seluruh alarm dan peringatan sistem (keamanan akun / peringatan stok obat).
    Route::get('/admin/notifikasi', [DashboardController::class, 'notifikasi'])->name('admin.notifikasi');

    # Mengubah status seluruh notifikasi menjadi 'Sudah Dibaca' sekaligus dalam satu kali klik.
    Route::post('/admin/notifikasi/baca-semua', [DashboardController::class, 'bacaSemuaNotifikasi'])->name('admin.baca-semua-notif');

    # --- MODUL PENGATURAN & PROFIL ---
    # Membuka halaman untuk mengelola identitas apotek dan saklar keamanan global aplikasi.
    Route::get('/admin/pengaturan', [DashboardController::class, 'pengaturan'])->name('admin.pengaturan');

    # Menyimpan pembaruan konfigurasi pengaturan sistem tersebut ke database.
    Route::put('/admin/pengaturan/update', [DashboardController::class, 'updatePengaturan'])->name('admin.update-pengaturan');

    # Melihat detail data profil pribadi dari akun admin yang sedang login saat ini.
    Route::get('/admin/profil', [DashboardController::class, 'profil'])->name('admin.profil');

    # Memperbarui informasi mandiri pengguna (seperti mengganti nama lengkap atau username).
    Route::put('/admin/profil/update', [DashboardController::class, 'updateProfil'])->name('admin.update-profil');

    # --- MODUL LOG AUDIT SISTEM ---
    # Menampilkan tabel riwayat CCTV digital (rekam jejak aktivitas) seluruh pengguna di dalam aplikasi.
    Route::get('/admin/audit-logs', [DashboardController::class, 'auditLogs'])->name('admin.audit-logs');

    # Merender (menghasilkan) laporan riwayat aktivitas tersebut menjadi file dokumen PDF untuk diunduh.
    Route::get('/admin/audit-logs/pdf', [DashboardController::class, 'exportPdfAuditLogs'])->name('admin.audit-logs.pdf');

    # =====================================================================
    # 5. GRUP RUTE KEPALA APOTEK (FOLDER AWAL: KEPALA/)
    # =====================================================================

    # Membungkus seluruh rute agar otomatis memiliki awalan URL '/kepala' dan awalan nama 'kepala.'
    Route::prefix('kepala')->name('kepala.')->group(function () {

        # Halaman dashboard khusus ringkasan manajerial untuk Kepala Apotek.
        Route::get('/dashboard', [DashboardController::class, 'kepala'])->name('dashboard');

        # --- MODUL VERIFIKASI FAKTUR ---
        # Membuka halaman daftar antrean faktur obat masuk yang butuh persetujuan (verifikasi).
        Route::get('/verifikasi', [DashboardController::class, 'verifikasi'])->name('verifikasi');

        # Membuka halaman rincian isi item obat di dalam suatu faktur tertentu (membawa parameter ID Faktur).
        Route::get('/verifikasi/detail/{id_masuk}', [DashboardController::class, 'detailVerifikasi'])->name('verifikasi.detail');

        # Memproses aksi Kepala Apotek saat menekan tombol 'Setuju' atau 'Tolak' pada faktur obat masuk.
        Route::post('/verifikasi/proses/{id_masuk}', [DashboardController::class, 'prosesVerifikasi'])->name('verifikasi.proses');

        # --- MODUL OTORISASI PEMUSNAHAN ---
        # Membuka halaman daftar pengajuan pemusnahan obat yang rusak atau telah kedaluwarsa.
        Route::get('/pemusnahan', [DashboardController::class, 'pemusnahan'])->name('pemusnahan');

        # Memproses keputusan Kepala Apotek (Setuju/Tolak) terhadap pengajuan pemusnahan obat.
        Route::post('/pemusnahan/proses/{id_keluar}', [DashboardController::class, 'prosesPemusnahan'])->name('pemusnahan.proses');

        # --- MODUL LAPORAN & PROFIL ---
        # Membuka halaman pusat untuk mencetak laporan stok sediaan dan riwayat transaksi.
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan');

        # Membuka halaman daftar pemberitahuan/alarm khusus untuk Kepala Apotek.
        Route::get('/notifikasi', [DashboardController::class, 'notifikasiKepala'])->name('notifikasi');

        # Membuka halaman profil pribadi milik Kepala Apotek.
        Route::get('/profil', [DashboardController::class, 'profilKepala'])->name('profil');

        # Menyimpan pembaruan data (nama/username) milik Kepala Apotek ke database.
        Route::post('/profil/update', [DashboardController::class, 'updateProfilKepala'])->name('profil.update');
    });

    # =====================================================================
    # 6. GRUP RUTE PETUGAS APOTEK (FOLDER AWAL: PETUGAS/)
    # =====================================================================

    Route::prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'petugas'])->name('dashboard');

        Route::get('/obat', [DashboardController::class, 'katalogObat'])->name('obat');

        # --- TAMBAHKAN 2 BARIS INI ---
        # ... (Rute petugas lainnya) ...
        Route::get('/obat', [DashboardController::class, 'katalogObat'])->name('obat');
        Route::get('/obat/tambah', [DashboardController::class, 'tambahObat'])->name('obat.tambah');

        # --- TAMBAHKAN BARIS INI UNTUK MEMPROSES PENYIMPANAN DATA FORM ---
        Route::post('/obat/simpan', [DashboardController::class, 'simpanObat'])->name('obat.simpan');

        Route::get('/obat/edit/{id}', [DashboardController::class, 'editObat'])->name('obat.edit');
        # ...
        # -----------------------------

        Route::get('/masuk', [DashboardController::class, 'obatMasuk'])->name('masuk');
        Route::get('/keluar', [DashboardController::class, 'obatKeluar'])->name('keluar');
        Route::get('/opname', [DashboardController::class, 'stokOpname'])->name('opname');
        Route::get('/notifikasi', [DashboardController::class, 'notifikasiPetugas'])->name('notifikasi');
        Route::get('/profil', [DashboardController::class, 'profilPetugas'])->name('profil');
    });
});
