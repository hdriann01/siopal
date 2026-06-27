<?php

# =====================================================================
# BAGIAN 1: PERSIAPAN (IMPOR KELAS DAN PENGATURAN LOKASI)
# =====================================================================

# Mendefinisikan alamat folder tempat file controller ini berada agar dikenali oleh sistem Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangkap semua data yang dikirim oleh pengguna (baik lewat URL maupun Form HTML)
use Illuminate\Http\Request;
# Mengimpor alat (Auth) untuk mengambil data profil pengguna yang sedang login saat ini
use Illuminate\Support\Facades\Auth;
# Mengimpor alat (DB / Query Builder) untuk mengeksekusi perintah SQL langsung ke database
use Illuminate\Support\Facades\DB;
# Mengimpor model Pengguna sebagai jembatan penghubung ke tabel 'pengguna' di database
use App\Models\Pengguna;
# Mengimpor model LogAudit sebagai jembatan ke tabel 'log_audit' (untuk mencatat riwayat aktivitas)
use App\Models\LogAudit;
# Mengimpor model Notifikasi sebagai jembatan ke tabel 'notifikasi' (sistem pesan/alarm)
use App\Models\Notifikasi;
# Mengimpor model Pengaturan sebagai jembatan ke tabel 'pengaturan' (untuk setelan global web)
use App\Models\Pengaturan;
# Mengimpor alat (Hash) untuk menyandikan/mengacak teks kata sandi (password) agar aman dan tidak bisa dibaca
use Illuminate\Support\Facades\Hash;
# Mengimpor alat (Str) bawaan Laravel untuk memanipulasi teks, seperti membuat kode acak
use Illuminate\Support\Str;
# Mengimpor alat (Pdf) dari library pihak ketiga (DomPDF) untuk mengubah halaman web menjadi dokumen PDF
use Barryvdh\DomPDF\Facade\Pdf;

# Mendeklarasikan class DashboardController yang mewarisi fungsi-fungsi inti dari Controller bawaan Laravel
class DashboardController extends Controller
{
    # =====================================================================
    # BAGIAN 2: FUNGSI-FUNGSI KHUSUS ADMINISTRATOR
    # =====================================================================

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN DASHBOARD UTAMA ADMINISTRATOR ---
    public function admin()
    {
        # Menghitung total baris data yang ada di tabel pengguna
        $totalPengguna = Pengguna::count();
        # Menghitung total keseluruhan jenis obat yang terdaftar di tabel obat
        $totalObat = DB::table('obat')->count();
        # Menghitung total dokumen faktur masuk yang pernah dicatat
        $totalMasuk = DB::table('obat_masuk')->count();
        # Menghitung total dokumen pengeluaran obat yang pernah dicatat
        $totalKeluar = DB::table('obat_keluar')->count();

        # --- LOGIKA UNTUK MEMBUAT GRAFIK TREN TRANSAKSI (7 HARI TERAKHIR) ---
        # Menyiapkan keranjang (array) kosong untuk menampung teks tanggal di sumbu X grafik
        $labelTanggal = [];
        # Menyiapkan keranjang kosong untuk menampung angka jumlah obat masuk di sumbu Y (Garis Hijau)
        $dataMasuk = [];
        # Menyiapkan keranjang kosong untuk menampung angka jumlah obat keluar di sumbu Y (Garis Kuning)
        $dataKeluar = [];

        # Melakukan perulangan hitung mundur dari angka 6 sampai 0 (total 7 putaran untuk 7 hari terakhir)
        for ($i = 6; $i >= 0; $i--) {
            # Mengambil waktu saat ini, kurangi sebanyak $i hari, lalu ubah formatnya menjadi Tahun-Bulan-Tanggal
            $tanggal = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');

            # Mengambil waktu yang sama, tapi formatnya dipendekkan (contoh: "27 Jun") untuk label teks di grafik
            $labelTanggal[] = \Carbon\Carbon::now()->subDays($i)->format('d M');

            # Mencari di tabel 'obat_masuk', hitung ada berapa transaksi yang tanggalnya persis sama dengan $tanggal acuan di atas
            $masuk = DB::table('obat_masuk')->whereDate('tanggal_masuk', $tanggal)->count();
            # Masukkan hasil hitungan tersebut ke dalam keranjang data masuk
            $dataMasuk[] = $masuk;

            # Mencari di tabel 'obat_keluar', hitung ada berapa transaksi yang tanggalnya persis sama dengan $tanggal
            $keluar = DB::table('obat_keluar')->whereDate('tanggal_keluar', $tanggal)->count();
            # Masukkan hasil hitungan tersebut ke dalam keranjang data keluar
            $dataKeluar[] = $keluar;
        }

        # Menampilkan halaman (view) dashboard admin, sekaligus mengirimkan ('passing data') semua keranjang variabel di atas
        return view('admin.dashboard', [
            'title' => 'Dashboard Administrator',
            'totalPengguna' => $totalPengguna,
            'totalObat' => $totalObat,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'labelTanggal' => $labelTanggal, # Dikirim untuk Sumbu X grafik
            'dataMasuk' => $dataMasuk,       # Dikirim untuk Sumbu Y grafik masuk
            'dataKeluar' => $dataKeluar,     # Dikirim untuk Sumbu Y grafik keluar
        ]);
    }

    # --- FUNGSI UNTUK HALAMAN DAFTAR PENGGUNA (BACA DATA & PENCARIAN) ---
    public function manajemenUser(Request $request)
    {
        # Menangkap parameter teks pencarian (search) dari URL jika admin mengetik sesuatu di kotak cari
        $search = $request->query('search');

        # Mengecek apakah variabel $search ada isinya?
        if ($search) {
            # Jika ada, cari data di tabel pengguna di mana nama lengkap ATAU username-nya mengandung huruf tersebut (mirip/like)
            $pengguna = Pengguna::where('nama_lengkap', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->get();
        } else {
            # Jika kotak pencarian kosong, tarik seluruh data pengguna dari database tanpa filter
            $pengguna = Pengguna::all();
        }

        # Menghitung total populasi pengguna saat ini
        $totalPengguna = Pengguna::count();

        # Tampilkan halaman antarmuka manajemen pengguna dan berikan datanya ke file blade (HTML)
        return view('admin.manajemen-user', [
            'title' => 'Manajemen Pengguna',
            'pengguna' => $pengguna,
            'totalPengguna' => $totalPengguna
        ]);
    }

    # --- FUNGSI UNTUK MEMBUKA HALAMAN FORM TAMBAH PENGGUNA ---
    public function tambahUser()
    {
        # Hanya merender file tampilan form tambah pengguna yang masih kosong
        return view('admin.tambah-user', ['title' => 'Tambah Pengguna Baru']);
    }

    # --- FUNGSI UNTUK MENYIMPAN PENGGUNA BARU KE DATABASE (DARI FORM) ---
    public function simpanUser(Request $request)
    {
        # Memvalidasi ketat data yang masuk: wajib diisi (required), tipe teks (string), maksimal batas huruf, dan username tidak boleh kembar (unique)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek', # Peran harus dipilih salah satu dari 3 ini
            'password'     => 'required|min:6', # Password minimal wajib 6 karakter
        ]);

        # Jika lolos validasi, ciptakan (create) baris data baru di tabel pengguna
        Pengguna::create([
            # Membuat ID kustom otomatis: Gabungan kata "USR" dengan 7 huruf/angka acak yang dibesarkan (huruf kapital)
            'id_pengguna'  => 'USR' . strtoupper(Str::random(7)),
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            # Mengacak password asli dari inputan menggunakan bcrypt (Hash) sebelum disimpan ke dalam tabel
            'password'     => Hash::make($request->password),
            'peran'        => $request->peran,
        ]);

        # Menambahkan catatan sejarah/riwayat ke tabel log audit mengenai tindakan penambahan ini
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna, # Mencatat siapa ID aktor yang melakukan tindakan ini
            'aktivitas'   => "Menambahkan pengguna baru: " . $request->nama_lengkap, # Deskripsi aktivitasnya
            'alamat_ip'   => $request->ip(), # Merekam alamat jaringan (IP Address) perangkat aktor
            'status'      => 'Success', # Status tindakan berhasil
            'created_at'  => now(), # Dicatat pada waktu detik ini juga
        ]);

        # Alihkan (redirect) halaman kembali ke tabel manajemen user dengan membawa pesan sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    # --- FUNGSI UNTUK MEMBUKA HALAMAN FORM EDIT PENGGUNA TERTENTU ---
    public function editUser(string $id)
    {
        # Cari data pengguna tunggal berdasarkan ID yang diklik. Jika ID salah/tidak ada, langsung hentikan dan tampilkan Error 404 (Not Found)
        $user = Pengguna::findOrFail($id);

        # Tampilkan form edit dan kirimkan data profil lama si pengguna agar input form terisi otomatis
        return view('admin.edit-user', [
            'title' => 'Edit Pengguna',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN (MENIMPA) DATA PENGGUNA YANG DIEDIT ---
    public function updateUser(Request $request, string $id)
    {
        # Pastikan data pengguna yang mau diubah benar-benar ada di tabel
        $user = Pengguna::findOrFail($id);

        # Validasi aturan input. Pada bagian unique username, kita beri pengecualian agar dia tetap bisa menggunakan username lamanya sendiri (berdasarkan id_pengguna)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $id . ',id_pengguna',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
        ]);

        # Jalankan fungsi perbarui (update) untuk menimpa data lama dengan data baru dari form
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'peran'        => $request->peran,
        ]);

        # Catat aktivitas perubahan profil ini ke dalam buku Log Audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data profil pengguna: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke halaman daftar pengguna
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MEMBUKA FORM RESET PASSWORD ---
    public function resetPassword(string $id)
    {
        # Temukan data pengguna yang passwordnya ingin di-reset oleh admin
        $user = Pengguna::findOrFail($id);

        # Buka halaman form khusus reset password dan bawa datanya
        return view('admin.reset-password', [
            'title' => 'Reset Password',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PASSWORD HASIL RESET ---
    public function updatePassword(Request $request, string $id)
    {
        # Temukan data penggunanya terlebih dahulu
        $user = Pengguna::findOrFail($id);

        # Validasi: kolom password wajib diisi, min 6 huruf, dan atutan 'confirmed' artinya inputan 'password' dan 'password_confirmation' harus diketik sama persis
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        # Timpa password lamanya dengan password baru yang sudah diacak ulang menggunakan algoritma Hash
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        # Catat bahwa admin telah memaksa reset sandi orang ini ke tabel Log
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Mereset password pengguna: " . $user->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke daftar pengguna dengan alert hijau
        return redirect()->route('admin.manajemen-user')->with('success', 'Password pengguna berhasil direset!');
    }

    # --- FUNGSI UNTUK MEMBUKA HALAMAN PERINGATAN SEBELUM HAPUS DATA ---
    public function konfirmasiHapus(string $id)
    {
        # Temukan akun mana yang menjadi target penghapusan
        $user = Pengguna::findOrFail($id);

        # Tampilkan layar konfirmasi (Are you sure?) yang berisi detail akun target
        return view('admin.hapus-user', [
            'title' => 'Konfirmasi Hapus',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENGEKSEKUSI PENGHAPUSAN PERMANEN DARI DATABASE ---
    public function prosesHapus(string $id, Request $request)
    {
        # Cari dan pastikan pengguna yang divonis hapus ada di database
        $user = Pengguna::findOrFail($id);

        # Menyelamatkan nama pengguna ke dalam variabel sementara agar namanya tetap bisa kita tulis ke dalam Log setelah datanya hancur
        $namaYangDihapus = $user->nama_lengkap;

        # Hancurkan / hapus (delete) baris data pengguna tersebut secara permanen dari tabel
        $user->delete();

        # Catat pembunuhan karakter ini ke dalam log audit dengan mengambil nama sementaranya tadi
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menghapus permanen pengguna: " . $namaYangDihapus,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan admin ke tabel manajemen dengan pesan sukses dihapus
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil dihapus permanen.');
    }

    # --- FUNGSI UNTUK MENAMPILKAN TABEL DAFTAR RIWAYAT AKTIVITAS (LOG AUDIT) ---
    public function auditLogs(Request $request)
    {
        # Menangkap parameter filter (dropdown) peran/role jika admin memilihnya (misal: "Hanya tampilkan log Petugas")
        $role = $request->query('role');

        # Menyusun kerangka kueri: Ambil data log dan tarik juga profil penggunanya (dengan relasi 'with'), lalu urutkan dari jam terbaru (descending)
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');

        # Jika admin memilih filter peran tertentu...
        if ($role) {
            # Tambahkan syarat pada kueri: Hanya ambil baris log di mana profil penggunanya memiliki 'peran' yang sesuai dengan pilihan admin
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Eksekusi dan tarik hasil akhirnya dari database (get)
        $logs = $query->get();

        # Kirim hasil pencarian log ini ke halaman tampilan (view) audit-logs
        return view('admin.audit-logs', [
            'title' => 'Log Audit Sistem',
            'logs' => $logs
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN FORM PENGATURAN GLOBAL APLIKASI ---
    public function pengaturan()
    {
        # Mengambil baris pertama (satu-satunya baris) dari tabel pengaturan, karena pengaturan sifatnya tunggal/global
        $pengaturan = Pengaturan::first();

        # Tampilkan form pengaturan dan kirim data setelan yang aktif saat ini
        return view('admin.pengaturan', [
            'title' => 'Pengaturan Sistem',
            'pengaturan' => $pengaturan
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PERUBAHAN SETELAN APLIKASI ---
    public function updatePengaturan(Request $request)
    {
        # Ambil kembali baris pertama dari tabel pengaturan
        $pengaturan = Pengaturan::first();

        # Pastikan kolom teks wajib diisi
        $request->validate([
            'nama_apotek' => 'required|string|max:100',
            'alamat_apotek' => 'required|string',
        ]);

        # Timpa (update) pengaturan yang ada.
        # Khusus untuk input tipe Checkbox (kotak centang): Jika ada centangnya (has) maka nilainya jadi 1 (Nyala/True), jika tidak dicentang maka jadi 0 (Mati/False)
        $pengaturan->update([
            'nama_apotek'         => $request->nama_apotek,
            'alamat_apotek'       => $request->alamat_apotek,
            'wajib_password_kuat' => $request->has('wajib_password_kuat') ? 1 : 0,
            'auto_logout'         => $request->has('auto_logout') ? 1 : 0,
            'log_audit_global'    => $request->has('log_audit_global') ? 1 : 0,
        ]);

        # Catat ke Log bahwa ada admin yang mengubah konfigurasi inti aplikasi
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => 'Memperbarui Pengaturan Sistem Aplikasi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke halaman sebelumnya (back) dengan notifikasi sukses
        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN PROFIL KHUSUS ROLE ADMIN ---
    public function profil()
    {
        # Lempar ke file tampilan profil global (shared.profil) dengan membawa identitas layout admin agar sidebar yang muncul adalah sidebar admin
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.admin',
            'actionUrl' => url('/simpan-profil-global') # Alamat form diarahkan ke fungsi update profil global yang ada di bagian bawah
        ]);
    }

    # --- FUNGSI UNTUK MENGUBAH DATA LOG AUDIT MENJADI FILE CETAK PDF ---
    public function exportPdfAuditLogs(Request $request)
    {
        # Tangkap pilihan filter peran saat ini (agar laporan yang dicetak persis sama isinya dengan yang sedang dilihat admin di layar)
        $role = $request->query('role');

        # Ulangi logika pencarian seperti fungsi auditLogs()
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');
        if ($role) {
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Dapatkan data log hasil filternya
        $logs = $query->get();

        # Perintahkan library eksternal (DomPDF) untuk merender sebuah file blade HTML khusus (pdf-audit-logs.blade.php) menjadi format dokumen PDF
        $pdf = Pdf::loadView('admin.pdf-audit-logs', [
            'logs' => $logs,
            'role' => $role # Kirim filter perannya untuk dijadikan sub-judul cetakan di dokumen
        ]);

        # Paksa peramban (browser) milik pengguna untuk langsung mengunduh (download) file PDF tersebut dengan nama yang sudah ditentukan
        return $pdf->download('Laporan_Audit_Log_SIOPAL.pdf');
    }


    # =====================================================================
    # BAGIAN 3: FUNGSI-FUNGSI KHUSUS KEPALA APOTEK
    # =====================================================================

    # --- FUNGSI UNTUK MENAMPILKAN DASHBOARD UTAMA KEPALA APOTEK ---
    public function kepala()
    {
        # --- 1. LOGIKA UNTUK WIDGET KARTU DI ATAS (KPI HEADERS) ---
        # Menghitung ada berapa faktur barang masuk yang butuh diperiksa (statusnya Draft)
        $fakturMenunggu = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->count();

        # Menghitung ada berapa pengajuan pemusnahan barang rusak yang butuh tanda tangan/persetujuan (Menunggu)
        $pemusnahanMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'Pemusnahan/Rusak')
            ->count();

        # Menghitung ada berapa jumlah jenis obat yang stoknya sedang sekarat (total_stok lebih kecil atau sama dengan angka minimal amannya)
        $obatKritis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->count();


        # --- 2. LOGIKA UNTUK GRAFIK/PETA KEDALUWARSA (DEFECTA) ---
        # Mengunci waktu/tanggal per hari ini untuk dasar perhitungan
        $hariIni = \Carbon\Carbon::now();
        # Menghitung tanggal tepat 3 bulan dari hari ini
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3);
        # Menghitung tanggal tepat 6 bulan dari hari ini
        $enamBulan = \Carbon\Carbon::now()->addMonths(6);

        # Menghitung batch obat yang SUDAH LEWAT masa kedaluwarsanya (tgl kadaluwarsa lebih kecil dari hari ini)
        $kadaluwarsa = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '<', $hariIni)->count();
        # Menghitung batch obat KRITIS: masa kedaluwarsanya tersisa dalam rentang hari ini sampai 3 bulan ke depan
        $kritis = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$hariIni, $tigaBulan])->count();
        # Menghitung batch obat PERINGATAN: masa kedaluwarsanya tersisa dalam rentang 3 bulan lebih 1 hari, sampai 6 bulan ke depan
        $peringatan = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$tigaBulan->copy()->addDay(), $enamBulan])->count();
        # Menghitung batch obat AMAN: masa kedaluwarsanya masih di atas 6 bulan
        $aman = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '>', $enamBulan)->count();


        # --- 3. LOGIKA UNTUK TABEL DAFTAR TUGAS (TO-DO LIST) ---
        # Menarik 5 data faktur masuk terbaru yang menumpuk di status 'Draft' untuk ditampilkan secara kilat di dashboard
        $fakturPending = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(5)
            ->get();


        # --- 4. KIRIM SEMUA DATA STATISTIK KE HALAMAN TAMPILAN (VIEW) ---
        return view('kepala.dashboard', [
            'title' => 'Dashboard Kepala Apotek',
            'fakturMenunggu' => $fakturMenunggu,
            'pemusnahanMenunggu' => $pemusnahanMenunggu,
            'obatKritis' => $obatKritis,
            'fakturPending' => $fakturPending,
            'dataDefecta' => [$aman, $peringatan, $kritis, $kadaluwarsa] # Memasukkan 4 status kedaluwarsa ke dalam 1 array terpusat untuk dipakai di grafik
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN TABEL VALIDASI/VERIFIKASI FAKTUR ---
    public function verifikasi()
    {
        # 1. Menghitung total seluruh faktur yang nasibnya masih menggantung (Draft)
        $totalMenunggu = DB::table('obat_masuk')->where('status_verifikasi', 'Draft')->count();

        # 2. Menghitung jumlah faktur berlabel "Urgent" (Mendesak)
        # Tentukan batas waktu darurat yaitu 3 bulan ke depan
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');

        # Gabungkan (join) tabel faktur dan rinciannya, lalu hitung ada berapa faktur unik (distinct) berstatus draft yang ternyata menyelundupkan obat yang masa kadaluwarsanya pendek (<= 3 bulan)
        $urgentCount = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->where('obat_masuk.status_verifikasi', 'Draft')
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulan)
            ->distinct('obat_masuk.id_masuk')
            ->count('obat_masuk.id_masuk');

        # 3. Menarik daftar lengkap faktur yang masih Draf untuk dicetak ke tabel
        $fakturList = DB::table('obat_masuk')
            ->select(
                'obat_masuk.*',
                # Menyelipkan sub-query (pencarian anak di dalam kolom) untuk menghitung langsung berapa macam obat (item) yang ada di dalam faktur ini
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk) as jumlah_item'),
                # Menyelipkan sub-query untuk mendeteksi apakah di dalam keranjang faktur ini terdapat minimal 1 barang urgent (kadaluwarsa <= 3 bulan)
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk AND tgl_kadaluwarsa <= "' . $tigaBulan . '") as ada_urgent')
            )
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'asc') # Diurutkan dari tanggal masuk yang paling tua (siapa yang antre duluan)
            ->get();

        return view('kepala.verifikasi', [
            'title' => 'Verifikasi Faktur',
            'totalMenunggu' => $totalMenunggu,
            'urgentCount' => $urgentCount,
            'fakturList' => $fakturList
        ]);
    }

    # --- FUNGSI UNTUK MELIHAT DETAIL ISI KERANJANG SEBUAH FAKTUR ---
    public function detailVerifikasi(string $id_masuk)
    {
        # 1. Ambil kulit/kepala dokumen fakturnya (Tanggal, Supplier, dll) dan gabungkan dengan nama pembuatnya
        $faktur = DB::table('obat_masuk')
            ->join('pengguna', 'obat_masuk.id_pengguna', '=', 'pengguna.id_pengguna')
            ->select('obat_masuk.*', 'pengguna.nama_lengkap')
            ->where('id_masuk', $id_masuk)
            ->first();

        # Keamanan: Cegah error blank page dengan menghentikan sistem jika id_masuk yang ada di URL dicari dan tidak ditemukan
        if (!$faktur) {
            abort(404, 'Dokumen faktur tidak ditemukan di dalam sistem.');
        }

        # 2. Ambil seluruh data isian/rincian item obat yang ada di dalam dokumen faktur tersebut
        $detailObat = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.dosis', 'obat.bentuk_sediaan')
            ->where('detail_masuk.id_masuk', $id_masuk)
            ->get();

        # Kirim kepala faktur dan rinciannya ke file tampilan
        return view('kepala.detail_verifikasi', [
            'title' => 'Detail Verifikasi Faktur',
            'faktur' => $faktur,
            'detailObat' => $detailObat
        ]);
    }

    # --- FUNGSI SAKTI UNTUK MENGEKSEKUSI KEPUTUSAN KEPALA APOTEK (SETUJU/TOLAK FAKTUR) ---
    public function prosesVerifikasi(Request $request, string $id_masuk)
    {
        # 1. Lindungi sistem dari manipulasi input. Pastikan nilai (value) tombol yang ditekan hanya mengirim teks "Disetujui" atau "Ditolak"
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        # Menyimpan status keputusan ke dalam variabel
        $statusBaru = $request->status;

        # 2. Cari kepala faktur di database sesuai ID transaksinya
        $faktur = DB::table('obat_masuk')->where('id_masuk', $id_masuk)->first();

        # Keamanan ganda: Tolak proses jika faktur tersebut dihapus tiba-tiba atau jika Kepala Apotek mencoba memproses ulang faktur yang sudah pernah disetujui sebelumnya
        if (!$faktur || $faktur->status_verifikasi != 'Draft') {
            return redirect()->route('kepala.verifikasi')->with('error', 'Faktur tidak valid atau sudah pernah diproses.');
        }

        # 3. MULAI PROSES TRANSAKSI DATABASE (PENTING!)
        # DB::transaction membungkus semua operasi ke dalam satu gelembung anti gagal. Jika di tengah proses baris 390 sampai 415 lampu mati/koneksi putus, maka SEMUA data di tabel obat, log, dan notif akan dikembalikan (Rollback) seperti semula, mencegah data hancur separuh.
        DB::transaction(function () use ($id_masuk, $statusBaru, $faktur) {

            # A. Timpa (update) status 'Draft' di tabel obat_masuk menjadi 'Disetujui' atau 'Ditolak'
            DB::table('obat_masuk')
                ->where('id_masuk', $id_masuk)
                ->update(['status_verifikasi' => $statusBaru]);

            # B. JIKA DISETUJUI, MAKA SAHKAN BARANG MASUK KE RAK APOTEK UTAMA
            if ($statusBaru == 'Disetujui') {
                # Ambil daftar rincian barang-barangnya dari tabel detail
                $detailFaktur = DB::table('detail_masuk')->where('id_masuk', $id_masuk)->get();

                # Lakukan perulangan satu per satu item
                foreach ($detailFaktur as $item) {
                    # Tambahkan angka jumlah masuk ke dalam kolom 'total_stok' milik tabel induk (tabel obat). Perintah increment() digunakan agar angka sebelumnya tidak ditimpa, melainkan dijumlahkan otomatis oleh SQL.
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->increment('total_stok', $item->jumlah_masuk);
                }
            }

            # C. Catat keputusan dewa ini ke buku rekam jejak Log Audit
            $kataKerja = ($statusBaru == 'Disetujui') ? 'Menyetujui' : 'Menolak';
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => $kataKerja . " faktur masuk dengan No: " . $faktur->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);

            # D. Tembak Notifikasi Balasan ke Akun Petugas Apotek
            $judulNotif = ($statusBaru == 'Disetujui') ? 'Faktur Disetujui' : 'Faktur Ditolak';
            # Atur teks pesan berdasarkan keputusannya (Beri teks tebal pada nomor faktur agar mencolok)
            $pesanNotif = ($statusBaru == 'Disetujui')
                ? 'Faktur <strong>' . $faktur->no_faktur . '</strong> telah diverifikasi. Stok otomatis bertambah ke dalam inventaris.'
                : 'Pengajuan faktur <strong>' . $faktur->no_faktur . '</strong> dikembalikan. Silakan periksa kembali kecocokan fisik barang dengan nota cetak.';

            # Simpan surat pesannya ke tabel notifikasi
            DB::table('notifikasi')->insert([
                'untuk_role'  => 'petugas', # Arahkan lonceng alarmnya ke semua orang yang ber-role petugas
                'tipe'        => 'Faktur',
                'judul'       => $judulNotif,
                'pesan'       => $pesanNotif,
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);
        }); # <-- Ini adalah dinding penutup perlindungan DB::transaction

        # 4. Beri informasi sukses di layar Kepala Apotek dan kembalikan dia ke tabel antrean
        $pesanNotif = ($statusBaru == 'Disetujui')
            ? 'Faktur disetujui! Stok obat berhasil ditambahkan ke dalam inventaris.'
            : 'Faktur telah ditolak dan diarsipkan.';

        return redirect()->route('kepala.verifikasi')->with('success', $pesanNotif);
    }

    # --- FUNGSI UNTUK HALAMAN OTORISASI PEMUSNAHAN OBAT (BARANG RUSAK/KADALUWARSA) ---
    public function pemusnahan(Request $request)
    {
        # 1. Menangkap parameter tab yang diklik di HTML (contoh URL: ?tab=Disetujui). Jika tidak diklik, bawaannya adalah tab 'Menunggu'
        $tab = $request->query('tab', 'Menunggu');

        # 2. Menghitung angka bulatan (badge) pada tab biru untuk menunjukkan berapa antrean yang belum diproses
        $totalMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'like', '%Pemusnahan%')
            ->count();

        # 3. Meracik kueri pencarian tabel Obat Keluar
        # Join tabel berlapis: Sambungkan riwayat keluar -> nama pembuatnya -> rincian barang keluarnya -> master obatnya
        $query = DB::table('obat_keluar')
            ->join('pengguna', 'obat_keluar.id_pengguna', '=', 'pengguna.id_pengguna')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select(
                'obat_keluar.id_keluar',
                'obat_keluar.tanggal_keluar',
                'obat_keluar.status_otorisasi',
                'obat_keluar.tujuan_pengeluaran',
                'pengguna.nama_lengkap',
                'pengguna.peran',
                'obat.nama_obat',
                'obat.dosis',
                'obat.satuan_dosis',
                'detail_keluar.jumlah_keluar'
            )
            # Batasi khusus untuk mencari transaksi yang tujuan pengeluarannya mengandung kata 'Pemusnahan' (Abaikan data resep pasien biasa)
            ->where('obat_keluar.tujuan_pengeluaran', 'like', '%Pemusnahan%');

        # 4. Filter Lanjutan Berdasarkan Tab yang Sedang Aktif Dilihat
        if ($tab == 'Menunggu') {
            $query->where('obat_keluar.status_otorisasi', 'Menunggu');
        } elseif ($tab == 'Disetujui') {
            $query->where('obat_keluar.status_otorisasi', 'Disetujui');
        } elseif ($tab == 'Ditolak') {
            $query->where('obat_keluar.status_otorisasi', 'Ditolak');
        }
        # (Jika tab bernama 'Semua', abaikan kriteria di atas agar semua status muncul)

        # Urutkan berdasarkan waktu transaksi terbaru
        $query->orderBy('obat_keluar.tanggal_keluar', 'desc');

        # Tarik eksekusi kueri di atas dari Database
        $pengajuanList = $query->get();

        return view('kepala.pemusnahan', [
            'title' => 'Otorisasi Pemusnahan',
            'totalMenunggu' => $totalMenunggu,
            'pengajuanList' => $pengajuanList,
            'activeTab' => $tab # Kirim identitas tab ke tampilan agar HTML bisa mewarnai tab yang sedang di-klik (Active State)
        ]);
    }

    # --- FUNGSI UNTUK MENGEKSEKUSI PEMUSNAHAN OBAT ---
    public function prosesPemusnahan(Request $request, string $id_keluar)
    {
        # Hanya izinkan klik tombol yang melempar tulisan Disetujui atau Ditolak
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        $statusBaru = $request->status;

        # Ambil data surat permohonan pengeluarannya
        $pengajuan = DB::table('obat_keluar')->where('id_keluar', $id_keluar)->first();

        # Keamanan: Tolak perintah jika data bodong atau sudah pernah dieksekusi sebelumnya
        if (!$pengajuan || $pengajuan->status_otorisasi != 'Menunggu') {
            return redirect()->route('kepala.pemusnahan')->with('error', 'Data tidak valid.');
        }

        # Bungkus lagi ke dalam pelindung transaksi database
        DB::transaction(function () use ($id_keluar, $statusBaru, $pengajuan) {

            # Update stempel pengajuannya menjadi sah atau ditolak
            DB::table('obat_keluar')
                ->where('id_keluar', $id_keluar)
                ->update(['status_otorisasi' => $statusBaru]);

            # JIKA DISETUJUI, KITA HARUS MEMBUANG OBAT DARI RAK
            if ($statusBaru == 'Disetujui') {
                $detailKeluar = DB::table('detail_keluar')->where('id_keluar', $id_keluar)->get();
                foreach ($detailKeluar as $item) {
                    # Kurangi (decrement) stok di tabel induk obat dengan besaran jumlah yang diajukan pemusnahan
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->decrement('total_stok', $item->jumlah_keluar);
                }
            }

            # Catat aktivitas pembakaran/penyelamatan aset ini ke Log
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => ($statusBaru == 'Disetujui' ? 'Menyetujui' : 'Menolak') . " pemusnahan obat ID: " . $id_keluar,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        $pesan = $statusBaru == 'Disetujui' ? 'Pemusnahan disetujui, stok telah dikurangi.' : 'Pengajuan pemusnahan ditolak.';
        return redirect()->route('kepala.pemusnahan')->with('success', $pesan);
    }

    # --- FUNGSI UNTUK PUSAT LAPORAN DAN PANTAUAN STOK (KEPALA APOTEK) ---
    public function laporan(Request $request)
    {
        # 1. Tangkap parameter klik dropdown kategori (Bawaannya adalah memunculkan 'semua' data)
        $kategoriPilihan = $request->query('kategori', 'semua');

        # 2. Hitung statistik untuk mengisi 3 Kartu KPI di paling atas halaman
        # Total jenis barang
        $totalItem = DB::table('obat')->count();

        # Total item kritis: Jika sisa stok sudah sama atau lebih kecil dari peringatan batas bahayanya (batas_stok_min)
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # Total item yang punya tumor/masalah (ada rincian batch di dalam faktur masuknya yang tanggalnya sudah melewati hari ini)
        $expiredCount = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<', now())
            ->distinct('id_obat')
            ->count('id_obat');

        # 3. Menarik daftar master kategori yang ada (Obat Bebas, Keras, Narkotika, dll) untuk mengisi kotak pilihan Dropdown Filter
        $kategoriList = DB::table('kategori')->get();

        # 4. Meracik kueri pencarian inventaris Obat beserta nama Kategorinya
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Terapkan filter penyaringan pada kueri jika Kepala Apotek tadi mengeklik sebuah kategori spesifik
        if ($kategoriPilihan != 'semua') {
            $query->where('obat.id_kategori', $kategoriPilihan);
        }

        # Urutkan berdasarkan nama obat A-Z, lalu bagi penampilannya menjadi sistem halaman (Pagination) di mana 1 halaman hanya diisi 10 obat
        $obatList = $query->orderBy('obat.nama_obat', 'asc')->paginate(10);

        return view('kepala.laporan', [
            'title' => 'Laporan Stok & Inventaris',
            'totalItem' => $totalItem,
            'stokKritis' => $stokKritis,
            'expiredCount' => $expiredCount,
            'kategoriList' => $kategoriList,
            'kategoriPilihan' => $kategoriPilihan,
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI UNTUK HALAMAN PROFIL KHUSUS KEPALA APOTEK ---
    public function profilKepala()
    {
        # Sama seperti admin, hanya saja kita lempar menggunakan layout kepala agar tampilan sisinya cocok
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.kepala',
            'actionUrl' => url('/simpan-profil-global')
        ]);
    }

    # =====================================================================
    # BAGIAN 4: FUNGSI-FUNGSI KHUSUS PETUGAS APOTEK (BAGIAN GUDANG/DEPAN)
    # =====================================================================

    # --- FUNGSI UNTUK MENAMPILKAN DASHBOARD OPERASIONAL (PETUGAS) ---
    public function petugas()
    {
        # 1. Statistik Dasar: Berapa jenis obat yang kita kelola saat ini?
        $totalObat = DB::table('obat')->count();

        # 2. Peringatan Dini: Berapa jenis obat yang stoknya butuh segera di order ke PBF?
        $stokMenipis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # 3. Peringatan Dini: Berapa batch kedatangan barang (kardus/dus) yang masa hidupnya tinggal <= 6 Bulan?
        $enamBulanKeDepan = \Carbon\Carbon::now()->addMonths(6);
        $akanKedaluwarsa = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<=', $enamBulanKeDepan)
            ->count();

        # --- LOGIKA UNTUK MENYUSUN TABEL TIMELINE "AKTIVITAS TERBARU" ---
        # A. Mengambil 5 aktivitas riwayat masuk terbaru yang sah (Disetujui)
        $masuk = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            # Samakan nama kolomnya (alias 'as') agar nanti saat digabungkan formatnya seragam
            ->select('obat_masuk.tanggal_masuk as tanggal', 'obat.nama_obat', 'detail_masuk.jumlah_masuk as jumlah', DB::raw("'Masuk' as tipe"))
            ->where('obat_masuk.status_verifikasi', 'Disetujui')
            ->orderBy('obat_masuk.tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        # B. Mengambil 5 aktivitas riwayat potong stok (keluar) terbaru
        $keluar = DB::table('obat_keluar')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select('obat_keluar.tanggal_keluar as tanggal', 'obat.nama_obat', 'detail_keluar.jumlah_keluar as jumlah', DB::raw("'Keluar' as tipe"))
            ->where('obat_keluar.status_otorisasi', 'Disetujui')
            ->orderBy('obat_keluar.tanggal_keluar', 'desc')
            ->limit(5)
            ->get();

        # C. Mengambil 5 jejak sinkronisasi rak (Stok Opname) dari log audit, lalu di-parsing teksnya
        $opname = DB::table('log_audit')
            ->select('created_at as tanggal', 'aktivitas')
            ->where('aktivitas', 'like', 'Stok Opname: Sinkronisasi%') # Hanya tarik log yang judulnya dimulai kata Stok Opname...
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            # Loop setiap isi datanya (map) untuk menerjemahkan bahasanya menjadi format yang pas dengan tabel $masuk dan $keluar
            ->map(function ($item) {
                # Membongkar teks log panjang menggunakan mesin Regex (pencarian pola teks kompleks)
                # Contoh Log Asli: "...Sinkronisasi Paracetamol dari 50 menjadi 45 (Selisih: -5)..."
                # Simbol (.*?) bertugas menelan Nama Obatnya, sedangkan ([\-\d]+) bertugas menelan angka selisihnya
                preg_match('/Sinkronisasi (.*?) dari \d+ menjadi \d+ \(Selisih: ([-\d]+)\)/', $item->aktivitas, $matches);

                # Tarik hasil tangkapan mesin regex tersebut
                $nama_obat = $matches[1] ?? 'Penyesuaian Stok (Opname)'; # Tangkapan pertama: Nama Obat
                $selisih = (int) ($matches[2] ?? 0);                     # Tangkapan kedua: Angka Selisih (+ atau -)

                # Ubah kerangka datanya agar menyamar (meniru) kolom data masuk dan keluar (sebagai Object)
                return (object) [
                    'tanggal'   => $item->tanggal,
                    'nama_obat' => $nama_obat,
                    'jumlah'    => abs($selisih), # Gunakan fungsi matematika absolute (abs) untuk mencopot tanda negatif (-) pada angka selisihnya
                    'tipe'      => $selisih > 0 ? 'Opname (+)' : 'Opname (-)', # Beri label penyesuaian penambahan atau kehilangan
                ];
            });

        # D. Gabungkan (concat) array masuk, keluar, dan opname tadi menjadi 1 antrean panjang,
        # lalu urutkan ulang mereka dari yang jamnya paling anyar/baru (sortByDesc), kemudian ambil 5 pemenang teratas (take).
        $aktivitasTerbaru = $masuk->concat($keluar)->concat($opname)->sortByDesc('tanggal')->take(5);

        return view('petugas.dashboard', [
            'title' => 'Dashboard Operasional',
            'totalObat' => $totalObat,
            'stokMenipis' => $stokMenipis,
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'aktivitasTerbaru' => $aktivitasTerbaru
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN DAFTAR OBAT YANG SEKARAT ---
    public function stokMenipis()
    {
        # Ambil daftar obat yang sisa kepingannya lebih kecil (atau pas) dengan garis kuning/minimalnya
        $stokMenipis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->orderBy('total_stok', 'asc') # Diurutkan dari yang sisanya paling merana (kosong / 0)
            ->get();

        return view('petugas.stok-menipis', [
            'title' => 'Peringatan Stok Menipis',
            'stokMenipis' => $stokMenipis,
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN DAFTAR BATCH OBAT MEMBUSUK ---
    public function obatKedaluwarsa()
    {
        # Cari tanggal pasca 3 bulan ke depan
        $tigaBulanKeDepan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');

        # Cek nota masuk, cari barang yang waktu Expired-nya sudah lebih kecil (lebih tua) dari garis batas 3 bulan tersebut
        $akanKedaluwarsa = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.satuan_dosis', 'obat.bentuk_sediaan')
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulanKeDepan)
            ->orderBy('detail_masuk.tgl_kadaluwarsa', 'asc') # Taruh obat yang sudah kedaluwarsa parah di paling atas list
            ->get();

        return view('petugas.obat-kedaluwarsa', [
            'title' => 'Peringatan Obat Kedaluwarsa',
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'hariIni' => $hariIni
        ]);
    }

    # --- FUNGSI BUKU INDUK KATALOG OBAT ---
    public function katalogObat(Request $request)
    {
        # Ambil ketikan petugas dari URL (?search=...&kategori=...)
        $search = $request->query('search');
        $kategoriId = $request->query('kategori');

        # Rancang cetakan kuerinya (hubungkan Obat dengan label golongannya)
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Syarat filter jika mencari via teks nama/kode
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('obat.nama_obat', 'like', '%' . $search . '%')
                    ->orWhere('obat.id_obat', 'like', '%' . $search . '%');
            });
        }

        # Syarat filter via combobox/dropdown kategori (Sirup, Keras, dll)
        if ($kategoriId) {
            $query->where('obat.id_kategori', $kategoriId);
        }

        # Urutkan abjad A-Z dan aktifkan Pagination (Membelah halaman menjadi 10 baris obat saja agar *loading* cepat)
        $obatList = $query->orderBy('obat.nama_obat', 'asc')->paginate(10);

        # Panggil semua jenis kategori yang ada di tabel master kategori untuk dilempar sebagai isi combobox filter HTML
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.katalog', [
            'title' => 'Inventaris & Katalog Obat',
            'obatList' => $obatList,
            'kategoriList' => $kategoriList,
            'search' => $search,
            'kategoriPilihan' => $kategoriId
        ]);
    }

    # --- FUNGSI HALAMAN KOSONG PENCATATAN FAKTUR DATANG DARI PBF/DISTRIBUTOR ---
    public function obatMasuk()
    {
        # Petugas butuh daftar obat saat input barang, jadi kirimkan list-nya
        $obatList = DB::table('obat')->orderBy('nama_obat', 'asc')->get();

        return view('petugas.obat-masuk', [
            'title' => 'Catat Obat Masuk',
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI BESAR UNTUK MENYIMPAN FAKTUR OBAT DATANG ---
    public function simpanObatMasuk(Request $request)
    {
        # 1. Pastikan kolom dasar dan kepala faktur terisi
        # Kolom 'items' adalah array (daftar jamak) yang dikirim oleh JavaScript di form, yang menampung rincian keranjang. Syarat min:1 memastikan tidak ada nota kosong yang terkirim.
        $request->validate([
            'no_faktur'     => 'required|string|max:100',
            'nama_supplier' => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'items'         => 'required|array|min:1',
        ]);

        # 2. Pakai tameng sakti (Transaction) untuk menghindari kebocoran data jika ada *crash*
        DB::transaction(function () use ($request) {

            # A. Logika Menciptakan ID Faktur Internal secara pintar (Generate Auto-Increment Kustom)
            # Tarik 1 nota paling terakhir
            $lastFaktur = DB::table('obat_masuk')->orderBy('id_masuk', 'desc')->first();
            # Siapkan ID dasar pertama jika tabelnya ternyata masih kosong melompong
            $newIdMasuk = 'INVM-001';

            # Jika nota sebelumnya ternyata ada isinya...
            if ($lastFaktur) {
                # Cincang ID lama (misal: "INVM-008"). Potong/buang 5 karakter di depannya sehingga tersisa angka murni "008", ubah (cast) ke integer menjadi 8.
                $lastNumber = (int) substr($lastFaktur->id_masuk, 5);
                # Tambah 1 menjadi 9. Lalu pakaikan fungsi str_pad agar kembali menjadi 3 digit "009", lalu tempel kembali kepalanya sehingga menjadi "INVM-009".
                $newIdMasuk = 'INVM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # B. Simpan dokumen kepalanya (wajah faktur) ke tabel obat_masuk
            DB::table('obat_masuk')->insert([
                'id_masuk'          => $newIdMasuk,
                'no_faktur'         => $request->no_faktur,
                'nama_supplier'     => $request->nama_supplier,
                'tanggal_masuk'     => $request->tanggal_masuk,
                'id_pengguna'       => Auth::user()->id_pengguna, # Mencatat sidik jari si petugas peracik nota
                'status_verifikasi' => 'Draft' # Paksa statusnya gantung, wajib nunggu ttd Kepala
            ]);

            # C. Simpan ususnya (isi keranjang / items array) ke dalam tabel rincian (detail_masuk) secara massal
            $detailData = []; # Siapkan wadah karung beras
            foreach ($request->items as $item) {
                # Masukkan data per baris obat ke dalam karung
                $detailData[] = [
                    'id_masuk'        => $newIdMasuk, # Ikat semuanya menggunakan ID faktur kepalanya yang baru (Foreign Key)
                    'id_obat'         => $item['id_obat'],
                    'jumlah_masuk'    => $item['jumlah_masuk'],
                    'tgl_kadaluwarsa' => $item['tgl_kadaluwarsa'],
                    'nomor_batch'     => $item['nomor_batch'],
                ];
            }
            # Guyur seluruh isi karung ke dalam tabel database (teknik Batch Insert ini jauh lebih efisien dan kencang daripada melooping insert satu per satu)
            DB::table('detail_masuk')->insert($detailData);

            # D. Kirim Alarm/Pemberitahuan kepada Kepala Apotek bahwa ia punya PR mengecek nota
            DB::table('notifikasi')->insert([
                'untuk_role'  => 'kepala',
                'tipe'        => 'Faktur',
                'judul'       => 'Verifikasi Faktur Baru',
                'pesan'       => 'Terdapat draf faktur masuk <strong>' . $request->no_faktur . '</strong> yang menunggu persetujuan Anda.',
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);

            # E. Catat pengabdian petugas ini ke tabel rekam jejak Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menginput draf faktur masuk baru No: " . $request->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # 3. Lempar petugas kembali ke halaman formnya dengan wajah tersenyum hijau
        return back()->with('success', 'Faktur berhasil disimpan sebagai Draf dan telah dikirim ke Kepala Apotek untuk diverifikasi!');
    }

    # --- FUNGSI HALAMAN FORM RESEP ATAU PENGELUARAN OBAT ---
    public function obatKeluar()
    {
        # MENGGUNAKAN LOGIKA CERDAS F.E.F.O (First Expired, First Out)!
        # Ambil seluruh obat di tabel yang total kepingannya (total_stok) lebih dari nol (ada wujudnya).
        # Lalu kita selipkan mata-mata (sub-query): "Tolong sekalian cek di gudang riwayat barang masuk (detail_masuk), cari obat yang ID-nya sama dengan ini, urutkan tanggal kadaluwarsanya dari yang paling hancur/dekat, lalu culik/ambil cukup 1 saja nama kardus Batch-nya (LIMIT 1) untuk dijadikan 'batch_rekomendasi' ke petugas."
        $obatList = DB::table('obat')
            ->select('obat.*', DB::raw('(SELECT nomor_batch FROM detail_masuk WHERE detail_masuk.id_obat = obat.id_obat ORDER BY tgl_kadaluwarsa ASC LIMIT 1) as batch_rekomendasi'))
            ->where('total_stok', '>', 0)
            ->orderBy('nama_obat', 'asc')
            ->get();

        return view('petugas.obat-keluar', [
            'title' => 'Catat Obat Keluar',
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI BESAR UNTUK MENGEKSEKUSI PENGELUARAN OBAT (DAN PENGURANGAN STOK) ---
    public function simpanObatKeluar(Request $request)
    {
        # Validasi struktur dokumen surat keluarnya
        $request->validate([
            'tujuan_pengeluaran' => 'required|string|max:150',
            'referensi'          => 'nullable|string|max:100', # (Boleh kosong/null karena kadang resep tidak bernomor)
            'tanggal_keluar'     => 'required|date',
            'items'              => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            # 1. Bikin/Racik nomor tiket antrean untuk Obat Keluar (Contoh: OUTM-007) dengan metode potong angka seperti di atas
            $lastFaktur = DB::table('obat_keluar')->orderBy('id_keluar', 'desc')->first();
            $newIdKeluar = 'OUTM-001';
            if ($lastFaktur) {
                $lastNumber = (int) substr($lastFaktur->id_keluar, 5);
                $newIdKeluar = 'OUTM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # 2. Cetak sampul dokumennya ke tabel obat_keluar
            DB::table('obat_keluar')->insert([
                'id_keluar'          => $newIdKeluar,
                'tanggal_keluar'     => $request->tanggal_keluar,
                'tujuan_pengeluaran' => $request->tujuan_pengeluaran,
                'id_pengguna'        => Auth::user()->id_pengguna,
                # LOGIKA PENGAMAN STOK OTOMATIS:
                # Jika tujuan keluarnya adalah dibakar (mengandung kata 'musnah' dengan fungsi str_contains & strtolower), maka stempelnya paksa 'Menunggu' Acc Kepala Apotek.
                # Jika tujuannya dipakai menebus resep pasien normal, beri stempel 'Disetujui' karena pasien butuh sekarang juga, tak perlu tunggu tanda tangan Kepala.
                'status_otorisasi'   => str_contains(strtolower($request->tujuan_pengeluaran), 'musnah') ? 'Menunggu' : 'Disetujui',
            ]);

            # 3. Tulis isi resepnya (items keranjang HTML) ke tabel rincian (detail_keluar) & Lakukan sunat Stok!
            $detailData = [];
            foreach ($request->items as $item) {
                # Memasukkan per baris obat ke array
                $detailData[] = [
                    'id_keluar'     => $newIdKeluar,
                    'id_obat'       => $item['id_obat'],
                    'jumlah_keluar' => $item['jumlah_keluar'],
                ];

                # POTONG LANGSUNG!
                # (Hanya jika ini tebusan resep pasien biasa, BUKAN surat pemusnahan barang)
                if (!str_contains(strtolower($request->tujuan_pengeluaran), 'musnah')) {
                    DB::table('obat')
                        ->where('id_obat', $item['id_obat'])
                        ->decrement('total_stok', $item['jumlah_keluar']); # Fungsi decrement mengiris rak sesuai jumlah angka $item tersebut
                }
            }
            # Simpan massal isi usus ke tabel rincian
            DB::table('detail_keluar')->insert($detailData);

            # 4. Jika tadi transaksinya adalah PEMUSNAHAN, maka wajarkan untuk memanggil Boss dengan mengirim Notifikasi
            if (str_contains(strtolower($request->tujuan_pengeluaran), 'musnah')) {
                DB::table('notifikasi')->insert([
                    'untuk_role'  => 'kepala',
                    'tipe'        => 'Faktur',
                    'judul'       => 'Otorisasi Pemusnahan Obat',
                    'pesan'       => 'Terdapat pengajuan pemusnahan obat <strong>' . $newIdKeluar . '</strong> yang menunggu otorisasi Anda.',
                    'status_baca' => 'Belum',
                    'created_at'  => now(),
                ]);
            }

            # 5. Catat kelakuan petugas apotek pada buku Log Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Mencatat obat keluar No: " . $newIdKeluar . " (" . $request->tujuan_pengeluaran . ")",
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # Alihkan ke dashboard utama petugas saat transaksi kelar
        return redirect()->route('petugas.dashboard')->with('success', 'Transaksi pengeluaran obat berhasil dicatat!');
    }

    # --- FUNGSI UNTUK MEMBUKA LEMBAR AUDIT FISIK (STOK OPNAME / HITUNG MANUAL) ---
    public function stokOpname(Request $request)
    {
        # 1. Cek apabila petugas sedang memfilter Rak tertentu (misal: hari ini khusus hitung Rak B saja)
        $rakPilihan = $request->query('rak');

        # 2. Tarik semua data nama rak yang unik dan tidak boleh kosong (distinct) dari tabel Obat untuk bahan Dropdown Combobox di HTML
        $rakList = DB::table('obat')->select('letak_rak')->whereNotNull('letak_rak')->where('letak_rak', '!=', '')->distinct()->pluck('letak_rak');

        # 3. Siapkan kueri dasar untuk memanggil semua obat
        $query = DB::table('obat')->orderBy('nama_obat', 'asc');

        # Jika difilter raknya, tambah syarat di mana nama raknya sesuai
        if ($rakPilihan) {
            $query->where('letak_rak', $rakPilihan);
        }
        $obatList = $query->get();

        return view('petugas.stok-opname', [
            'title' => 'Audit Stok Opname',
            'obatList' => $obatList,
            'rakList' => $rakList,
            'rakPilihan' => $rakPilihan
        ]);
    }

    # --- FUNGSI UNTUK MENYAMAKAN DATA SISTEM DENGAN PERHITUNGAN MATA PETUGAS DI RAK (OPNAME) ---
    public function simpanStokOpname(Request $request)
    {
        # Cek formnya minimal ada daftar items-nya atau tidak
        $request->validate([
            'items' => 'required|array',
        ]);

        # Bungkus lagi demi keamanan mutlak
        DB::transaction(function () use ($request) {
            # Bikin indikator apakah petugas tadi cuma klik tombol "Simpan" tapi tidak ngetik satu angka selisih pun?
            $jumlahPerubahan = 0;

            # Mulai mengecek ratusan obat kiriman form satu per satu (Looping)
            foreach ($request->items as $id_obat => $data) {

                # ABAIKAN baris ini (Lewati pakai 'continue') jika petugas apotek membiarkan kolom teks kotak Stok Fisik-nya kosong blong.
                # (Hal ini membebaskan petugas agar tidak harus mengetik ulang angka ribuan obat yang kebetulan memang pas)
                if (!isset($data['stok_fisik']) || $data['stok_fisik'] === '') {
                    continue;
                }

                # Tangkap angka ketikan petugas, pastikan tipenya angka bersih (integer)
                $stokFisik = (int) $data['stok_fisik'];
                # Intip tabel database, lihat angka versi si komputer
                $obat = DB::table('obat')->where('id_obat', $id_obat)->first();

                # PENCOCOKAN:
                # Jika barisnya ketemu, DAN TERNYATA angka hitungan petugas BEDA dengan angka komputer (total_stok)
                if ($obat && $obat->total_stok != $stokFisik) {

                    # 1. Hitung kerugian atau keuntungan kelebihan (Fisik - Komputer)
                    $selisih = $stokFisik - $obat->total_stok;
                    # Tangkap catatan alasannya (Jika hilang dicuri / ketumpahan teh dll)
                    $keterangan = $data['keterangan'] ?? 'Tanpa keterangan';

                    # 2. PAKSA! Timpa (update) angka di kolom komputer agar tunduk menjadi sama (mengikuti) angka hitungan mata fisik manusia (stokFisik)
                    DB::table('obat')->where('id_obat', $id_obat)->update([
                        'total_stok' => $stokFisik
                    ]);

                    # 3. Bukukan penyesuaian/kecurangan rak ini ke dalam Log Audit agar Direktur tau obat apa saja yang sering raib dari etalase
                    LogAudit::create([
                        'id_pengguna' => Auth::user()->id_pengguna,
                        'aktivitas'   => "Stok Opname: Sinkronisasi " . $obat->nama_obat . " dari " . $obat->total_stok . " menjadi " . $stokFisik . " (Selisih: " . $selisih . "). Ket: " . $keterangan,
                        'alamat_ip'   => request()->ip(),
                        'status'      => 'Success',
                        'created_at'  => now(),
                    ]);

                    # Tambahkan rekor perubahan angka bahwa ada sekurang-kurangnya 1 barang yang berhasil diamandemen
                    $jumlahPerubahan++;
                }
            }

            # JIKA TERNYATA: Setelah di-*scan* komputer, nol (tidak ada) barang yang berbeda angkanya (Semua sinkron sempurna),
            # maka simpan pesan "Selesai tanpa selisih" lewat flash session.
            if ($jumlahPerubahan == 0) {
                session()->flash('info', 'Tidak ada selisih yang ditemukan. Stok sistem sudah sesuai dengan fisik.');
            }
        });

        # Jika punya pesan flash 'info' kustom, maka arahkan pulang
        if (session()->has('info')) {
            return redirect()->route('petugas.dashboard');
        }

        # Jika sukses potong-tambah di banyak jenis, tampilkan perayaan sukses normal
        return redirect()->route('petugas.dashboard')->with('success', 'Audit Stok Opname selesai! Data stok telah disinkronkan dengan fisik.');
    }

    # --- FUNGSI UNTUK PROFIL KHUSUS PANGKAT PETUGAS APOTEK ---
    public function profilPetugas()
    {
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.petugas',
            'actionUrl' => url('/simpan-profil-global')
        ]);
    }

    # --- FUNGSI UNTUK MEMBUKA FORM PENDAFTARAN BUKU OBAT (MASTER) BARU ---
    public function tambahObat()
    {
        # Petugas butuh tau daftar kode/label Kategori Obat (Bebas, dsb) untuk bahan pilihan Dropdown (Combobox) HTML
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.tambah-obat', [
            'title' => 'Tambah Obat Baru',
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN MASTER BUKU OBAT BARU KE RAK MAYA ---
    public function simpanObat(Request $request)
    {
        # 1. Cegah form bodong, pastikan angka diketik dengan tipe Number/Integer
        $request->validate([
            'nama_obat'      => 'required|string|max:100',
            'id_kategori'    => 'required|string',
            'dosis'          => 'required|numeric',
            'satuan_dosis'   => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak'      => 'nullable|string|max:50', # Rak bebas mau dibiarkan tak diisi (nullable)
            'batas_stok_min' => 'required|integer|min:0', # Gembok batas minimum (Alarm peringatannya)
        ]);

        # 2. Ciptakan ID Unik Obat dengan Metode Cincang seperti ID Faktur sebelumnya (Misal: OBT008 menjadi OBT009)
        $lastObat = DB::table('obat')->orderBy('id_obat', 'desc')->first();
        $newId = 'OBT001';
        if ($lastObat) {
            $lastNumber = (int) substr($lastObat->id_obat, 3);
            $newId = 'OBT' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        # 3. Benamkan formulirnya ke rahim tabel induk (tabel 'obat')
        DB::table('obat')->insert([
            'id_obat'        => $newId,
            'id_kategori'    => $request->id_kategori,
            'nama_obat'      => $request->nama_obat,
            'dosis'          => $request->dosis,
            'satuan_dosis'   => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak'      => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
            # PERHATIAN: Obat yang baru dibuat akta kelahirannya pasti diawali stok NOL.
            # Karena ia baru boleh bertambah jumlahnya melalui surat Faktur Obat Masuk resmi nantinya.
            'total_stok'     => 0,
        ]);

        # 4. Catat kelakuan sang pembuat di Log
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menambahkan master obat baru: " . $request->nama_obat,
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('petugas.obat')->with('success', 'Obat baru berhasil ditambahkan ke katalog!');
    }

    # --- FUNGSI UNTUK MEMBUKA FORM AMANDEMEN/EDIT (BESERTA MASA LALU DATA OBAT) ---
    public function editObat(string $id)
    {
        # Tarik data asli obat ber-ID khusus ini dari palung data
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        # Jika diusili dari luar (ID tidak ditemukan di Database), tolak ke beranda tabel katalog
        if (!$obat) {
            return redirect()->route('petugas.obat')->withErrors('Data obat tidak ditemukan.');
        }

        # Bawa pancingan combobox daftar Kategori lagi
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.edit-obat', [
            'title' => 'Edit Data Obat',
            'obat' => $obat,
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI UNTUK MENIMPA/MENGUBUR DATA BUKU OBAT LAMA ---
    public function updateObat(Request $request, string $id)
    {
        # Validasi kembali formulir baru seperti standar form tambah
        $request->validate([
            'nama_obat'      => 'required|string|max:100',
            'id_kategori'    => 'required|string',
            'dosis'          => 'required|numeric',
            'satuan_dosis'   => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak'      => 'nullable|string|max:50',
            'batas_stok_min' => 'required|integer|min:0',
        ]);

        # Ganti isian lama di baris terkait dengan isian baru (update)
        DB::table('obat')->where('id_obat', $id)->update([
            'id_kategori'    => $request->id_kategori,
            'nama_obat'      => $request->nama_obat,
            'dosis'          => $request->dosis,
            'satuan_dosis'   => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak'      => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
        ]);

        # Catat pembaharuan profil obat ini di Log
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data master obat: " . $request->nama_obat . " (ID: " . $id . ")",
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('petugas.obat')->with('success', 'Data obat berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENGHANGUSKAN (HAPUS PERMANEN) OBAT DARI KATALOG MASTER ---
    public function hapusObat(string $id, Request $request)
    {
        # 1. Pastikan objek obat targetnya memang ada nyawanya di database
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        if (!$obat) {
            return back()->with('error', 'Gagal: Data obat tidak ditemukan di database.');
        }

        # Mengamankan julukan (nama obat) sebelum ID-nya hancur, untuk kepentingan isi tulisan Log nantinya
        $namaObat = $obat->nama_obat;

        # Menggunakan perlindungan TRY - CATCH untuk menjinakkan ERROR DATABASE
        try {
            # 2. EKSEKUSI: Bakar baris master obat ber-ID ini dari muka tabel (delete)
            DB::table('obat')->where('id_obat', $id)->delete();

            # 3. Jika berhasil dibakar tanpa bentrok error, catat pembakaran tersebut
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menghapus master obat dari katalog: " . $namaObat,
                'alamat_ip'   => $request->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);

            return back()->with('success', 'Data obat "' . $namaObat . '" berhasil dihapus dari katalog!');

            # 4. TAPI TUNGGU DULU, ADA CATCH!
            # Secara Ilmu Database, jika obat ini ID-nya ternyata sedang nyangkut atau dipakai sebagai relasi/tamu di dalam tabel transaksi Faktur Masuk/Keluar (Foreign Key Constraint)...
            # Maka sistem SQL MySQL akan menolak pemusnahan ini secara brutal (Karena jika si master hancur, nanti nota riwayat lama di tabel transaksi jadi anak yatim / Yatim Piatu Constraint Error).
            # CATCH ini akan menangkap serangan Error SQL (QueryException) tersebut, mengubahnya jadi pesan empuk, dan menolak aksi delete tanpa membuat aplikasinya hancur.
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal: Obat "' . $namaObat . '" tidak bisa dihapus karena memiliki riwayat transaksi di sistem.');
        }
    }

    # --- FUNGSI UNTUK MENAMPILKAN KOTAK PUSAT NOTIFIKASI DI POJOK KANAN ATAS GLOBALLY ---
    public function pusatNotifikasi()
    {
        # 1. Sidik jari siapa aktor yang sedang duduk menatap layar komputer saat ini?
        $user = Auth::user();

        # Menangkap data 'peran' si akun, ubah tulisannya jadi huruf kecil semua untuk kelancaran logika if
        $nilaiRole = $user->peran ?? '';
        $roleRaw = strtolower($nilaiRole);

        # Mengubah berbagai kemungkinan nama peran menjadi kode singkatan seragam (Normalisasi String)
        if ($roleRaw === 'admin' || $roleRaw === 'administrator' || $roleRaw === '1') {
            $role = 'admin';
        } elseif (str_contains($roleRaw, 'kepala') || $roleRaw === '2') {
            $role = 'kepala';
        } else {
            $role = 'petugas';
        }

        # 2. Menentukan rumah tema Layout CSS/Sidebar mana yang akan melingkupi kotak notif ini,
        #    sehingga lonceng notifikasi bisa dipakai menyatu (Reusable) di 3 aktor tanpa cacat desain
        $layout = 'layouts.' . $role;

        # 3. Tarik surat notifikasi MURNI HANYA UNTUK KAUM (ROLE) aktor bersangkutan dari laci database
        $notifikasiList = DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->orderBy('created_at', 'desc')
            ->get();

        # 4. Scan surat-surat tersebut (dengan method Collection ->contains), apakah minimal ada satu (1) saja surat yang Status Bacanya masih perawan ('Belum')?
        # Ini akan menghasilkan nilai TRUE atau FALSE.
        $hasUnread = $notifikasiList->contains('status_baca', 'Belum');

        # 5. Lempar semua bahan-bahan tadi ke dalam file HTML global tunggal (shared/notifikasi.blade.php)
        return view('shared.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'layout' => $layout,
            'role' => $role,
            'notifikasiList' => $notifikasiList,
            'hasUnread' => $hasUnread # Kalau TRUE, HTML akan memunculkan titik merah di atas ikon loncengnya
        ]);
    }

    # --- FUNGSI AMAN: MENGUBAH STAMPEL SEMUA SURAT MENJADI "SUDAH DIBACA" (MARK ALL AS READ) ---
    public function bacaSemuaNotifikasi()
    {
        $user = Auth::user();

        # Ulangi logika pengenalan jabatannya seperti di atas
        $nilaiRole = $user->peran ?? '';
        $roleRaw = strtolower($nilaiRole);

        if ($roleRaw === 'admin' || $roleRaw === 'administrator' || $roleRaw === '1') {
            $role = 'admin';
        } elseif (str_contains($roleRaw, 'kepala') || $roleRaw === '2') {
            $role = 'kepala';
        } else {
            $role = 'petugas';
        }

        # PERBAIKAN FATAL: Menimpa (Update) isi kolom Status Baca menjadi 'Sudah',
        # TAPI DENGAN SYARAT KETAT! ->where('untuk_role', $role)
        # Artinya, jika Petugas klik baca semua, dia tidak akan ikut me-read (membaca) milik Kepala Apotek yang masih belum dibaca. Keren kan?
        DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->where('status_baca', 'Belum')
            ->update(['status_baca' => 'Sudah']);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    # --- FUNGSI SATU UNTUK SEMUA: MENYIMPAN UBAHAN NAMA PROFIL (UBAH MANDIRI) ---
    public function simpanProfilGlobal(Request $request)
    {
        $user = Auth::user();
        # Dapatkan ID-nya (Entah dia masuk pakai nama tabel yang mana, antisipasi versi variabelnya)
        $idPengguna = $user->id_pengguna ?? $user->id;

        # 1. Sama dengan form edit: Wajib diisi, batas maksimal, namun dengan penangkal unique id diri sendiri
        # (sehingga dia tidak dicurigai sistem mau mencuri username lamanya sendiri)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $idPengguna . ',id_pengguna',
        ]);

        # 2. Update data namanya di tabel identitas pengguna
        DB::table('pengguna')->where('id_pengguna', $idPengguna)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # 3. Catat bahwa orang ini ganti nama/profil ke log audit
        LogAudit::create([
            'id_pengguna' => $idPengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi secara mandiri',
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # 4. Lempar kembali ke halaman mana pun profil dia berada
        return back()->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    # --- FUNGSI HEBAT: MENGUNDUH OTOMATIS DATA RAK OBAT MENJADI FILE EXCEL (CSV) KEPALA APOTEK ---
    public function exportLaporanExcel()
    {
        # Membuat nama file otomatis dan dinamis berdasarkan detik di-klik (Contoh: Laporan_Stok_SIOPAL_2026-06-27_18-12.csv)
        $namaFile = 'Laporan_Stok_SIOPAL_' . date('Y-m-d_H-i') . '.csv';

        # Menarik paksa seluruh populasi obat beserta sisa stoknya secara terurut sesuai huruf depan nama obat
        $dataObat = DB::table('obat')
            ->select('id_obat', 'id_kategori', 'nama_obat', 'dosis', 'satuan_dosis', 'bentuk_sediaan', 'letak_rak', 'batas_stok_min', 'total_stok')
            ->orderBy('nama_obat', 'asc')
            ->get();

        # Membuat paket pembungkus surat (HTTP Header) untuk memberi mandat (perintah) kepada browser Chrome/Edge pengguna agar:
        # "Hei browser, ini bukan halaman web lho (text/csv), tolong langsung unduhkan saja sebagai attachment bernama $namaFile, dan jangan disimpan di cache-mu!"
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$namaFile",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        # Mendeklarasikan pekerja pembuat file-nya menggunakan metode Callback/Closure (Membungkus logika dalam variabel fungsi $callback)
        $callback = function () use ($dataObat) {
            # Buka gerbang saluran tulisan (stream) ke tempat buangan output server (php://output) dengan status 'w' (write / tulis)
            $file = fopen('php://output', 'w');

            # KUNCI SUKSES: Memasukkan deret Baris Judul Tabel Kolom di atas (Header Excel).
            # Kita menggunakan perintah fputcsv yang disisipi karakter TITIK KOMA (;) diujungnya, bukan koma (,).
            # Mengapa? Karena Excel format standar Indonesia/Eropa menggunakan titik koma untuk memisahkan sel di dalam file CSV, jika memakai koma semua teks akan menumpuk di 1 sel hancur lebur!
            fputcsv($file, ['ID Obat', 'Nama Obat', 'Kategori', 'Sediaan', 'Letak Rak', 'Batas Minimal', 'Stok Saat Ini', 'Status'], ';');

            # Cincang satu per satu rincian database ke dalam baris-baris Excel
            foreach ($dataObat as $obat) {
                # Cek kecerdasan buatan kecil: Jika stok <= batas minimal, langsung buat teks sel berbunyi (Perlu Restok), jika tidak, tulis 'Aman'
                $status = $obat->total_stok <= $obat->batas_stok_min ? 'Menipis (Perlu Restok)' : 'Aman';

                # Cetak masuk rincian ke sel Excelnya sesuai urutan judul Header kolom di atas, dengan dipisah titik koma lagi.
                fputcsv($file, [
                    $obat->id_obat,
                    $obat->nama_obat,
                    $obat->id_kategori,
                    $obat->dosis . ' ' . $obat->satuan_dosis . ' ' . $obat->bentuk_sediaan, # Gabungkan teks (concatenate) jadi "500 mg Tablet" dalam 1 sel
                    $obat->letak_rak,
                    $obat->batas_stok_min,
                    $obat->total_stok,
                    $status
                ], ';');
            }
            # Jika sudah habis perulangannya, matikan saluran/tutup file-nya (close)
            fclose($file);
        };

        # Lakukan tembakan akhir Stream perpaduan isi callback dengan surat paksaan download Header tadi ke depan layar komputer Kepala Apotek (dengan status HTTP sukses / 200)
        return response()->stream($callback, 200, $headers);
    }
}
