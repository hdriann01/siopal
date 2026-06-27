<?php

# =====================================================================
# 1. BAGIAN PERSIAPAN (IMPOR KELAS CONTROLLER)
# =====================================================================

# Mengimpor AuthController agar rute di bawah bisa memanggil fungsi Login dan Logout
use App\Http\Controllers\AuthController;
# Mengimpor alat (Facade) Route bawaan Laravel yang bertugas mendaftarkan/membuat alamat URL aplikasi
use Illuminate\Support\Facades\Route;
# Mengimpor DashboardController, yaitu otak utama (Controller) yang menangani hampir semua tampilan halaman SIOPAL
use App\Http\Controllers\DashboardController;


# =====================================================================
# 2. RUTE DASAR & ALAMAT UTAMA (ROOT)
# =====================================================================

# Jika ada orang yang mengakses alamat web paling depan/kosong (misal: http://localhost:8000/),
Route::get('/', function () {
    # Maka sistem akan langsung 'melempar' (redirect) orang tersebut secara paksa ke halaman login.
    return redirect('/login');
});


# =====================================================================
# 3. RUTE AUTENTIKASI (PINTU MASUK & KELUAR)
# =====================================================================

# Rute (GET) untuk sekadar mengambil dan menampilkan desain halaman form HTML login.
# Nama rute 'login' diberikan agar satpam sistem keamanan Laravel mengenali URL ini sebagai pintu masuk utama.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

# Rute (POST) untuk mengirimkan dan memproses data username & password yang diketik pengguna ke otak sistem.
Route::post('/login', [AuthController::class, 'login']);

# Rute (POST) untuk menghancurkan sesi (session) yang sedang aktif di browser dan mengeluarkan pengguna dari aplikasi (Keluar/Logout).
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


# =====================================================================
# 4. GRUP RUTE TERLINDUNGI (KHUSUS PENGGUNA YANG SUDAH LOGIN)
# =====================================================================

# Seluruh rute di dalam blok (group) ini dibungkus oleh pelindung (middleware) bernama 'auth'.
# Artinya: Sembarang orang yang belum login, dilarang keras mencoba mengakses URL-URL ini dengan mengetiknya manual di browser.
Route::middleware(['auth'])->group(function () {

    # =================================================================
    # A. AREA KHUSUS ADMINISTRATOR
    # =================================================================

    # --- MODUL DASHBOARD ---
    # Membuka halaman ringkasan data statistik utama bagi peran Administrator.
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    # --- MODUL MANAJEMEN PENGGUNA ---
    # Membuka halaman tabel daftar seluruh staf apotek, sekalian untuk fitur pencarian nama.
    Route::get('/admin/manajemen-user', [DashboardController::class, 'manajemenUser'])->name('admin.manajemen-user');

    # Membuka halaman form kosong untuk mendaftarkan staf/pegawai baru.
    Route::get('/admin/tambah-user', [DashboardController::class, 'tambahUser'])->name('admin.tambah-user');

    # Memproses/menyimpan data formulir pegawai baru tersebut ke dalam database setelah tombol 'Simpan' ditekan.
    Route::post('/admin/tambah-user', [DashboardController::class, 'simpanUser'])->name('admin.simpan-user');

    # Membuka form edit yang sudah terisi data lama milik profil staf tertentu berdasarkan {id}-nya.
    Route::get('/admin/edit-user/{id}', [DashboardController::class, 'editUser'])->name('admin.edit-user');

    # Memproses (menimpa) data staf lama dengan data baru hasil editan ke dalam tabel database.
    Route::put('/admin/update-user/{id}', [DashboardController::class, 'updateUser'])->name('admin.update-user');

    # Membuka form khusus untuk mereset kata sandi (password) staf yang bermasalah.
    Route::get('/admin/reset-password/{id}', [DashboardController::class, 'resetPassword'])->name('admin.reset-password');

    # Memproses penyimpanan kata sandi baru yang sudah dienkripsi (diacak) ke dalam database.
    Route::put('/admin/update-password/{id}', [DashboardController::class, 'updatePassword'])->name('admin.update-password');

    # Menampilkan layar peringatan (Konfirmasi Hapus) sebelum data staf benar-benar dilenyapkan.
    Route::get('/admin/hapus-user/{id}', [DashboardController::class, 'konfirmasiHapus'])->name('admin.konfirmasi-hapus');

    # Menghapus baris data staf tersebut secara permanen dari tabel database (menggunakan aksi DELETE).
    Route::delete('/admin/proses-hapus/{id}', [DashboardController::class, 'prosesHapus'])->name('admin.proses-hapus');

    # --- MODUL PENGATURAN & PROFIL ---
    # Membuka form pengaturan untuk mengelola identitas apotek dan saklar global aplikasi.
    Route::get('/admin/pengaturan', [DashboardController::class, 'pengaturan'])->name('admin.pengaturan');

    # Menyimpan pembaruan konfigurasi pengaturan sistem tersebut ke dalam database.
    Route::put('/admin/pengaturan/update', [DashboardController::class, 'updatePengaturan'])->name('admin.update-pengaturan');

    # Melihat detail data profil pribadi dari akun admin yang sedang dipakai.
    Route::get('/admin/profil', [DashboardController::class, 'profil'])->name('admin.profil');

    # --- MODUL LOG AUDIT SISTEM ---
    # Menampilkan tabel CCTV digital (rekam jejak riwayat aktivitas) dari seluruh pengguna di aplikasi.
    Route::get('/admin/audit-logs', [DashboardController::class, 'auditLogs'])->name('admin.audit-logs');

    # Merender (mencetak) laporan riwayat aktivitas tersebut menjadi file dokumen PDF agar bisa didownload.
    Route::get('/admin/audit-logs/pdf', [DashboardController::class, 'exportPdfAuditLogs'])->name('admin.audit-logs.pdf');


    # =================================================================
    # B. RUTE GLOBAL (FASILITAS BERSAMA UNTUK SEMUA PERAN)
    # =================================================================

    # Membuka halaman pusat notifikasi (bisa diakses Admin, Kepala, maupun Petugas).
    Route::get('/notifikasi', [DashboardController::class, 'pusatNotifikasi'])->name('notifikasi.global');

    # Mengeksekusi tombol "Tandai Semua Dibaca" agar titik merah pada ikon lonceng menghilang.
    Route::post('/notifikasi/baca-semua', [DashboardController::class, 'bacaSemuaNotifikasi'])->name('notifikasi.baca-semua');

    # Rute Universal Penampung Form Profil: Siapapun (Admin/Kepala/Petugas) yang mengedit namanya sendiri, datanya diproses di rute ini.
    Route::post('/simpan-profil-global', [DashboardController::class, 'simpanProfilGlobal']);

    # Rute untuk mengunduh laporan stok inventaris ke dalam bentuk file Microsoft Excel (CSV).
    Route::get('/export-excel-laporan', [DashboardController::class, 'exportLaporanExcel']);


    # =================================================================
    # C. GRUP RUTE KEPALA APOTEK (FOLDER & NAMA AWAL: KEPALA)
    # =================================================================

    # Prefix 'kepala' membuat semua URL di dalamnya otomatis diawali '/kepala/...'
    # Name 'kepala.' membuat pemanggilan nama rutenya di HTML otomatis diawali 'kepala....'
    Route::prefix('kepala')->name('kepala.')->group(function () {

        # Halaman dashboard (beranda) khusus ringkasan manajerial untuk Kepala Apotek.
        Route::get('/dashboard', [DashboardController::class, 'kepala'])->name('dashboard');

        # --- MODUL VERIFIKASI FAKTUR MASUK ---
        # Membuka halaman daftar antrean faktur obat datang yang butuh tanda tangan/persetujuan (verifikasi).
        Route::get('/verifikasi', [DashboardController::class, 'verifikasi'])->name('verifikasi');

        # Membuka halaman yang menampilkan rincian barang/obat apa saja yang ada di dalam faktur tersebut.
        Route::get('/verifikasi/detail/{id_masuk}', [DashboardController::class, 'detailVerifikasi'])->name('verifikasi.detail');

        # Memproses ketokan palu Kepala Apotek (Setuju / Tolak) terhadap faktur obat masuk tersebut.
        Route::post('/verifikasi/proses/{id_masuk}', [DashboardController::class, 'prosesVerifikasi'])->name('verifikasi.proses');

        # --- MODUL OTORISASI PEMUSNAHAN BARANG RUSAK ---
        # Membuka halaman daftar permohonan petugas untuk membuang/memusnahkan obat yang rusak atau kedaluwarsa.
        Route::get('/pemusnahan', [DashboardController::class, 'pemusnahan'])->name('pemusnahan');

        # Memproses keputusan (Setuju/Tolak) Kepala Apotek terhadap surat pengajuan pemusnahan obat tersebut.
        Route::post('/pemusnahan/proses/{id_keluar}', [DashboardController::class, 'prosesPemusnahan'])->name('pemusnahan.proses');

        # --- MODUL LAPORAN & PROFIL ---
        # Membuka halaman pusat untuk mengecek laporan ketersediaan stok fisik secara keseluruhan.
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan');

        # Membuka halaman informasi profil pribadi khusus untuk antarmuka (layout) Kepala Apotek.
        Route::get('/profil', [DashboardController::class, 'profilKepala'])->name('profil');
    });


    # =================================================================
    # D. GRUP RUTE PETUGAS APOTEK (FOLDER & NAMA AWAL: PETUGAS)
    # =================================================================

    # Semua URL di dalam blok ini otomatis diawali dengan '/petugas/...'
    Route::prefix('petugas')->name('petugas.')->group(function () {

        # Membuka halaman dashboard operasional yang menampilkan aktivitas terbaru untuk Petugas.
        Route::get('/dashboard', [DashboardController::class, 'petugas'])->name('dashboard');

        # --- MODUL KATALOG/MASTER OBAT ---
        # Membuka halaman buku induk inventaris (Katalog) yang menampilkan daftar seluruh obat.
        Route::get('/obat', [DashboardController::class, 'katalogObat'])->name('obat');

        # Membuka halaman formulir kosong untuk mendaftarkan jenis/master obat baru ke dalam katalog.
        Route::get('/obat/tambah', [DashboardController::class, 'tambahObat'])->name('obat.tambah');

        # Memproses penyimpanan data obat baru tersebut ke dalam tabel database.
        Route::post('/obat/simpan', [DashboardController::class, 'simpanObat'])->name('obat.simpan');

        # Membuka form edit dengan data lama dari obat tertentu (berdasarkan ID) yang ingin diubah rinciannya.
        Route::get('/obat/edit/{id}', [DashboardController::class, 'editObat'])->name('obat.edit');

        # Memproses (menimpa) data obat lama tersebut dengan data baru hasil editan (menggunakan metode PUT).
        Route::put('/obat/update/{id}', [DashboardController::class, 'updateObat'])->name('obat.update');

        # Menghapus permanen master obat tertentu dari katalog database.
        Route::delete('/obat/hapus/{id}', [DashboardController::class, 'hapusObat'])->name('obat.hapus');

        # --- MODUL TRANSAKSI GUDANG & RESEP ---
        # Membuka form pencatatan kedatangan barang baru dari Supplier (Faktur Obat Masuk).
        Route::get('/masuk', [DashboardController::class, 'obatMasuk'])->name('masuk');

        # Menyimpan draf catatan kedatangan barang tadi ke tabel transaksi masuk (Menunggu acc Kepala Apotek).
        Route::post('/masuk', [DashboardController::class, 'simpanObatMasuk'])->name('simpan-masuk');

        # Membuka form kasir/penyerahan obat untuk ditebus pasien atau pemusnahan (Obat Keluar).
        Route::get('/keluar', [DashboardController::class, 'obatKeluar'])->name('keluar');

        # Memproses pemotongan stok obat akibat transaksi penyerahan obat/resep tersebut.
        Route::post('/keluar', [DashboardController::class, 'simpanObatKeluar'])->name('simpan-keluar');

        # --- MODUL AUDIT STOK FISIK ---
        # Membuka form lembar kerja untuk mencocokkan stok di komputer vs jumlah fisik di rak nyata (Stok Opname).
        Route::get('/opname', [DashboardController::class, 'stokOpname'])->name('opname');

        # Menyimpan angka selisih hasil hitung fisik tersebut dan merevisi angka stok di database.
        Route::post('/opname', [DashboardController::class, 'simpanStokOpname'])->name('simpan-opname');

        # --- MODUL PERINGATAN (WARNING) ---
        # Membuka halaman daftar obat yang sisa kepingannya sudah menyentuh batas kritis/akan habis.
        Route::get('/stok-menipis', [DashboardController::class, 'stokMenipis'])->name('stok-menipis');

        # Membuka halaman daftar batch/kardus obat yang tanggal masa pakainya sudah kedaluwarsa atau kurang dari 3 bulan.
        Route::get('/obat-kedaluwarsa', [DashboardController::class, 'obatKedaluwarsa'])->name('obat-kedaluwarsa');

        # --- MODUL PROFIL ---
        # Membuka halaman profil pribadi khusus untuk antarmuka (layout) Petugas Apotek.
        Route::get('/profil', [DashboardController::class, 'profilPetugas'])->name('profil');
    });
});
