<?php

# =====================================================================
# BAGIAN 1: PERSIAPAN (IMPOR KELAS DAN PENGATURAN LOKASI)
# =====================================================================

# Mendefinisikan namespace (alamat folder) tempat controller ini berada agar dikenali oleh sistem Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangkap data ketikan/inputan dari form atau URL
use Illuminate\Http\Request;
# Mengimpor alat (Auth) untuk mendapatkan data pengguna yang sedang login saat ini
use Illuminate\Support\Facades\Auth;
# Mengimpor alat (DB) untuk melakukan pencarian/perintah langsung ke database tanpa model
use Illuminate\Support\Facades\DB;
# Mengimpor model Pengguna untuk berinteraksi dengan tabel 'pengguna' di database
use App\Models\Pengguna;
# Mengimpor model LogAudit untuk berinteraksi dengan tabel 'log_audit' (rekam jejak)
use App\Models\LogAudit;
# Mengimpor model Notifikasi untuk berinteraksi dengan tabel 'notifikasi' (alarm sistem)
use App\Models\Notifikasi;
# Mengimpor model Pengaturan untuk berinteraksi dengan tabel 'pengaturan' (konfigurasi aplikasi)
use App\Models\Pengaturan;
# Mengimpor alat (Hash) untuk mengenkripsi/mengacak teks password demi keamanan
use Illuminate\Support\Facades\Hash;
# Mengimpor alat pembantu (Str) untuk menghasilkan teks acak (random string)
use Illuminate\Support\Str;
# Mengimpor alat pembuat PDF (Pdf) dari library DomPDF untuk mengekspor laporan
use Barryvdh\DomPDF\Facade\Pdf;

# Mendeklarasikan class DashboardController yang mewarisi fitur Controller bawaan Laravel
class DashboardController extends Controller
{
    # =====================================================================
    # BAGIAN 2: FUNGSI-FUNGSI KHUSUS ADMINISTRATOR
    # =====================================================================

    # --- FUNGSI UNTUK MENAMPILKAN DASHBOARD UTAMA ADMINISTRATOR ---
    public function admin()
    {
        # Menghitung total keseluruhan data untuk ditampilkan di kotak atas (Statistik Angka Biasa)
        $totalPengguna = Pengguna::count();             # Berapa banyak akun pengguna?
        $totalObat = DB::table('obat')->count();        # Berapa banyak jenis obat di katalog?
        $totalMasuk = DB::table('obat_masuk')->count(); # Berapa banyak nota masuk?
        $totalKeluar = DB::table('obat_keluar')->count(); # Berapa banyak nota keluar?

        # --- LOGIKA UNTUK MEMBUAT GRAFIK TREN (7 HARI TERAKHIR) ---
        $labelTanggal = []; # Menyiapkan keranjang untuk tulisan tanggal di bawah grafik (Sumbu X)
        $dataMasuk = [];    # Menyiapkan keranjang untuk titik angka obat masuk (Sumbu Y)
        $dataKeluar = [];   # Menyiapkan keranjang untuk titik angka obat keluar (Sumbu Y)

        # Melakukan hitung mundur dari 6 hari yang lalu sampai hari ini (total 7 hari)
        for ($i = 6; $i >= 0; $i--) {
            # Mengambil tanggal acuan persisnya (format: Tahun-Bulan-Hari)
            $tanggal = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');

            # Mengubah format tanggal menjadi lebih pendek (contoh: "04 Jun") untuk label grafik
            $labelTanggal[] = \Carbon\Carbon::now()->subDays($i)->format('d M');

            # Menghitung ada berapa transaksi masuk pada tanggal acuan tersebut
            $masuk = DB::table('obat_masuk')->whereDate('tanggal_masuk', $tanggal)->count();
            $dataMasuk[] = $masuk; # Memasukkan hasilnya ke dalam keranjang data masuk

            # Menghitung ada berapa transaksi keluar pada tanggal acuan tersebut
            $keluar = DB::table('obat_keluar')->whereDate('tanggal_keluar', $tanggal)->count();
            $dataKeluar[] = $keluar; # Memasukkan hasilnya ke dalam keranjang data keluar
        }

        # Menampilkan halaman dashboard admin dan mengirimkan semua data yang sudah dihitung di atas
        return view('admin.dashboard', [
            'title' => 'Dashboard Administrator',
            'totalPengguna' => $totalPengguna,
            'totalObat' => $totalObat,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'labelTanggal' => $labelTanggal, # Mengirim data Sumbu X grafik
            'dataMasuk' => $dataMasuk,       # Mengirim data garis hijau grafik
            'dataKeluar' => $dataKeluar,     # Mengirim data garis kuning grafik
        ]);
    }

    # --- FUNGSI UNTUK HALAMAN DAFTAR PENGGUNA (BACA DATA & PENCARIAN) ---
    public function manajemenUser(Request $request)
    {
        # Menangkap kata kunci yang diketik admin di kotak pencarian
        $search = $request->query('search');

        # Mengecek apakah admin mengetik sesuatu?
        if ($search) {
            # Jika ada, cari data pengguna yang nama atau username-nya mengandung kata kunci tersebut
            $pengguna = Pengguna::where('nama_lengkap', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->get();
        } else {
            # Jika kotak pencarian kosong, ambil semua data pengguna tanpa terkecuali
            $pengguna = Pengguna::all();
        }

        # Menghitung total jumlah seluruh pengguna di sistem
        $totalPengguna = Pengguna::count();

        # Tampilkan halaman tabel pengguna beserta datanya
        return view('admin.manajemen-user', [
            'title' => 'Manajemen Pengguna',
            'pengguna' => $pengguna,
            'totalPengguna' => $totalPengguna
        ]);
    }

    # --- FUNGSI UNTUK MEMBUKA FORM TAMBAH PENGGUNA ---
    public function tambahUser()
    {
        # Cukup tampilkan halaman form kosong
        return view('admin.tambah-user', ['title' => 'Tambah Pengguna Baru']);
    }

    # --- FUNGSI UNTUK MENYIMPAN PENGGUNA BARU KE DATABASE ---
    public function simpanUser(Request $request)
    {
        # Mengecek aturan main: wajib diisi, tidak boleh lebih dari 100 huruf, dan username harus unik (belum ada yang pakai)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
            'password'     => 'required|min:6', # Password minimal harus 6 karakter
        ]);

        # Menyimpan data baru tersebut ke dalam tabel 'pengguna'
        Pengguna::create([
            # Membuat ID otomatis (Contoh: "USR" ditambah 7 karakter acak kapital seperti "USRX1Y2Z3")
            'id_pengguna'  => 'USR' . strtoupper(Str::random(7)),
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            # Mengacak teks password asli menjadi kode rahasia yang tidak bisa dibaca siapapun
            'password'     => Hash::make($request->password),
            'peran'        => $request->peran,
        ]);

        # Mencatat ke buku log bahwa admin ini baru saja menambahkan pengguna baru
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna, # Siapa yang melakukan?
            'aktivitas'   => "Menambahkan pengguna baru: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke halaman daftar pengguna sambil membawa pesan sukses warna hijau
        return redirect()->route('admin.manajemen-user')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    # --- FUNGSI UNTUK MEMBUKA FORM EDIT PENGGUNA TERTENTU ---
    public function editUser(string $id)
    {
        # Cari pengguna berdasarkan ID-nya, jika tidak ada, sistem akan otomatis error 404
        $user = Pengguna::findOrFail($id);

        # Tampilkan form edit dan kirimkan data lama pengguna tersebut agar mengisi kolom secara otomatis
        return view('admin.edit-user', [
            'title' => 'Edit Pengguna',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MEMPERBARUI (UPDATE) DATA PENGGUNA ---
    public function updateUser(Request $request, string $id)
    {
        # Cari pengguna yang datanya mau ditimpa/diperbarui
        $user = Pengguna::findOrFail($id);

        # Cek aturan main. Pengecualian pada username: dia boleh pakai namanya sendiri, tapi tidak boleh pakai milik orang lain
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $id . ',id_pengguna',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
        ]);

        # Timpa data lama di database dengan data baru dari form
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'peran'        => $request->peran,
        ]);

        # Mencatat aktivitas edit ini ke tabel log audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data profil pengguna: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke halaman tabel pengguna
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MEMBUKA FORM RESET PASSWORD ---
    public function resetPassword(string $id)
    {
        # Cari pengguna mana yang mau di-reset passwordnya
        $user = Pengguna::findOrFail($id);

        # Tampilkan form khusus reset password
        return view('admin.reset-password', [
            'title' => 'Reset Password',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PASSWORD HASIL RESET ---
    public function updatePassword(Request $request, string $id)
    {
        # Cari penggunanya
        $user = Pengguna::findOrFail($id);

        # Syarat wajib: password minimal 6 karakter, dan inputan 'password' harus sama persis dengan 'password_confirmation'
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        # Simpan password barunya dengan keadaan diacak (Hash)
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        # Catat aktivitas peresetan sandi ini
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Mereset password pengguna: " . $user->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('admin.manajemen-user')->with('success', 'Password pengguna berhasil direset!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN PERINGATAN HAPUS DATA ---
    public function konfirmasiHapus(string $id)
    {
        $user = Pengguna::findOrFail($id);

        # Tampilkan layar konfirmasi apakah admin benar-benar yakin ingin menghapus
        return view('admin.hapus-user', [
            'title' => 'Konfirmasi Hapus',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENGEKSEKUSI PENGHAPUSAN PERMANEN ---
    public function prosesHapus(string $id, Request $request)
    {
        # Cari pengguna yang divonis hapus
        $user = Pengguna::findOrFail($id);

        # Simpan nama lengkapnya ke variabel sementara (karena setelah dihapus, kita tidak bisa memanggil namanya lagi)
        $namaYangDihapus = $user->nama_lengkap;

        # Hapus baris data pengguna tersebut secara permanen dari tabel
        $user->delete();

        # Catat pembunuhan karakter ini (penghapusan data) ke log audit menggunakan nama yang sudah kita amankan tadi
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menghapus permanen pengguna: " . $namaYangDihapus,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil dihapus permanen.');
    }

    # --- FUNGSI UNTUK MENAMPILKAN TABEL RIWAYAT LOG AUDIT ---
    public function auditLogs(Request $request)
    {
        # Mengecek apakah admin sedang memilih filter peran dari kotak dropdown (misal: hanya mau lihat log Petugas)
        $role = $request->query('role');

        # Siapkan perintah pencarian: Ambil semua data log BERSAMA data profil penggunanya, urutkan dari jam terbaru
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');

        # Jika admin memilih sebuah peran di dropdown...
        if ($role) {
            # ...tambahkan filter tambahan: hanya cari log yang data tabel penggunanya memiliki peran sesuai pilihan
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Ambil hasil akhirnya dari database
        $logs = $query->get();

        # Tampilkan halaman log audit
        return view('admin.audit-logs', [
            'title' => 'Log Audit Sistem',
            'logs' => $logs
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN PUSAT NOTIFIKASI ---
    public function notifikasi(Request $request)
    {
        # Mengecek tab apa yang sedang diklik (Semua, Keamanan, atau Sistem). Default-nya adalah 'Semua'
        $tab = $request->query('tab', 'Semua');

        # Siapkan perintah: ambil semua peringatan dari yang paling baru
        $query = Notifikasi::orderBy('created_at', 'desc');

        # Jika tab bukan 'Semua', saring tipe notifikasinya sesuai tab yang diklik
        if ($tab == 'Keamanan') {
            $query->where('tipe', 'Keamanan');
        } elseif ($tab == 'Sistem') {
            $query->where('tipe', 'Sistem');
        }

        # Eksekusi pencarian
        $notifikasi = $query->get();

        # Hitung ada berapa notifikasi yang statusnya masih 'Belum' dibaca (untuk memunculkan angka merah)
        $belumDibaca = Notifikasi::where('status_baca', 'Belum')->count();

        # Tampilkan halamannya
        return view('admin.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'notifikasi' => $notifikasi,
            'belumDibaca' => $belumDibaca,
            'activeTab' => $tab # Memberitahu halaman HTML tab mana yang harus diberi warna aktif
        ]);
    }

    # --- FUNGSI UNTUK TOMBOL "TANDAI SEMUA DIBACA" ---
    public function bacaSemuaNotifikasi()
    {
        # Cari seluruh baris di tabel notifikasi yang statusnya 'Belum', lalu paksa ubah menjadi 'Sudah'
        Notifikasi::where('status_baca', 'Belum')->update(['status_baca' => 'Sudah']);

        # Refresh halaman
        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    # --- FUNGSI UNTUK MENAMPILKAN PENGATURAN GLOBAL SISTEM ---
    public function pengaturan()
    {
        # Ambil pengaturan baris pertama (karena tabel pengaturan memang hanya berisi 1 baris saklar global)
        $pengaturan = Pengaturan::first();

        return view('admin.pengaturan', [
            'title' => 'Pengaturan Sistem',
            'pengaturan' => $pengaturan
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PENGATURAN YANG DIUBAH ADMIN ---
    public function updatePengaturan(Request $request)
    {
        $pengaturan = Pengaturan::first();

        # Pastikan nama dan alamat tidak dikosongkan
        $request->validate([
            'nama_apotek' => 'required|string|max:100',
            'alamat_apotek' => 'required|string',
        ]);

        # Simpan perubahan.
        # Logika Checkbox HTML: Jika dicentang (has), nilainya 1. Jika tidak, nilainya 0 (false)
        $pengaturan->update([
            'nama_apotek'         => $request->nama_apotek,
            'alamat_apotek'       => $request->alamat_apotek,
            'wajib_password_kuat' => $request->has('wajib_password_kuat') ? 1 : 0,
            'auto_logout'         => $request->has('auto_logout') ? 1 : 0,
            'log_audit_global'    => $request->has('log_audit_global') ? 1 : 0,
        ]);

        # Catat ke log bahwa admin telah merombak konfigurasi aplikasi
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => 'Memperbarui Pengaturan Sistem Aplikasi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENGEDIT PROFIL DIRI SENDIRI ---
    public function profil()
    {
        # Ambil data diri akun yang sedang login saat ini
        $user = Auth::user();

        return view('admin.profil', [
            'title' => 'Profil Saya',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PROFIL DIRI SENDIRI ---
    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        # Validasi seperti biasa, abaikan nama unik jika itu miliknya sendiri
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $user->id_pengguna . ',id_pengguna',
        ]);

        # Cari tabel pengguna berdasarkan ID dirinya, lalu perbarui teksnya
        $userModel = Pengguna::findOrFail($user->id_pengguna);
        $userModel->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # Catat aktivitas bahwa dia baru saja mengubah datanya sendiri
        LogAudit::create([
            'id_pengguna' => $user->id_pengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENGHASILKAN PDF DARI LOG AUDIT ---
    public function exportPdfAuditLogs(Request $request)
    {
        # Tangkap filter peran (agar PDF yang dicetak sama dengan apa yang di-filter di layar)
        $role = $request->query('role');

        # Susun query yang sama dengan tabel audit logs
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');
        if ($role) {
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Eksekusi pencarian
        $logs = $query->get();

        # Panggil alat Pdf untuk mencetak file 'admin/pdf-audit-logs.blade.php' ke dalam bentuk dokumen
        $pdf = Pdf::loadView('admin.pdf-audit-logs', [
            'logs' => $logs,
            'role' => $role # Kirim data peran untuk dijadikan judul cetakan di atas kertas
        ]);

        # Lempar dokumen PDF tersebut agar di-download oleh browser dengan nama file kustom
        return $pdf->download('Laporan_Audit_Log_SIOPAL.pdf');
    }


    # =====================================================================
    # BAGIAN 3: FUNGSI-FUNGSI KHUSUS KEPALA APOTEK
    # =====================================================================

    # --- FUNGSI UNTUK DASHBOARD KEPALA APOTEK ---
    public function kepala()
    {
        # 1. Menghitung jumlah Faktur Obat Masuk yang berstatus 'Draft' (Artinya butuh di-ACC)
        $menungguMasuk = DB::table('obat_masuk')->where('status_verifikasi', 'Draft')->count();

        # 2. Menghitung jumlah Permintaan Obat Keluar/Rusak yang berstatus 'Menunggu' (Artinya butuh di-ACC)
        $menungguKeluar = DB::table('obat_keluar')->where('status_otorisasi', 'Menunggu')->count();

        # 3. Menghitung jumlah Obat Kritis dengan mengecek apakah total stoknya sudah di bawah batas minimum
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # --- LOGIKA UNTUK GRAFIK PETA KEDALUWARSA (DEFECTA) ---
        # Menentukan acuan waktu menggunakan alat Carbon
        $hariIni = \Carbon\Carbon::now();                  # Waktu persis saat ini
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3);  # Waktu untuk 3 bulan ke depan
        $enamBulan = \Carbon\Carbon::now()->addMonths(6);  # Waktu untuk 6 bulan ke depan

        # Menghitung Batch Kedaluwarsa: Jika tanggal kedaluwarsa sudah lebih kecil (lewat) dari hari ini
        $kadaluwarsa = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '<', $hariIni)->count();

        # Menghitung Batch Kritis: Jika tanggal kedaluwarsanya berjarak antara hari ini hingga 3 bulan ke depan
        $kritis = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$hariIni, $tigaBulan])->count();

        # Menghitung Batch Peringatan: Jika ED-nya antara 3 bulan lebih sehari, sampai 6 bulan ke depan
        # (addDay digunakan agar jarak harinya tidak tumpang tindih dengan yang Kritis)
        $peringatan = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$tigaBulan->copy()->addDay(), $enamBulan])->count();

        # Menghitung Batch Aman: Jika tanggal ED-nya masih lebih jauh dari 6 bulan ke depan
        $aman = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '>', $enamBulan)->count();

        # 5. Mengambil 5 faktur terbaru yang berstatus Draft untuk ditampilkan di tabel tugas
        $fakturPending = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'desc') # Urutkan dari tanggal terbaru
            ->limit(5) # Batasi hanya ambil 5
            ->get();

        # Kirim semuanya ke layar dashboard Kepala
        return view('kepala.dashboard', [
            'title' => 'Dashboard Kepala Apotek',
            'menungguMasuk' => $menungguMasuk,
            'menungguKeluar' => $menungguKeluar,
            'stokKritis' => $stokKritis,
            'fakturPending' => $fakturPending,
            # Data ini digabung ke dalam satu array agar mudah digambar oleh grafik batang
            'dataDefecta' => [$aman, $peringatan, $kritis, $kadaluwarsa]
        ]);
    }

    # --- (PLACEHOLDER) FUNGSI-FUNGSI KEPALA APOTEK YANG SEDANG DALAM PENGERJAAN ---
    public function validasi()
    {
        return "Halaman Validasi Transaksi (Dalam Pengerjaan)";
    }

    public function stok()
    {
        return "Halaman Pantauan Stok & Defecta (Dalam Pengerjaan)";
    }

    public function laporan()
    {
        return "Halaman Pusat Laporan (Dalam Pengerjaan)";
    }

    public function notifikasiKepala()
    {
        return "Halaman Notifikasi Kepala Apotek (Dalam Pengerjaan)";
    }

    public function profilKepala()
    {
        return "Halaman Profil Kepala Apotek (Dalam Pengerjaan)";
    }

    public function pengaturanKepala()
    {
        return "Halaman Pengaturan Kepala Apotek (Dalam Pengerjaan)";
    }


    # =====================================================================
    # BAGIAN 4: FUNGSI-FUNGSI KHUSUS PETUGAS APOTEK
    # =====================================================================

    # --- FUNGSI UNTUK DASHBOARD OPERASIONAL PETUGAS ---
    public function petugas()
    {
        # 1. Menghitung total seluruh jenis obat yang terdaftar di katalog
        $totalObat = DB::table('obat')->count();

        # 2. Menghitung obat yang stoknya menipis (sudah menyentuh atau kurang dari batas minimal)
        $stokMenipis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # 3. Menghitung jumlah Batch yang akan atau sudah kedaluwarsa (ED <= 6 Bulan dari sekarang)
        $enamBulanKeDepan = \Carbon\Carbon::now()->addMonths(6);
        $akanKedaluwarsa = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<=', $enamBulanKeDepan)
            ->count();

        # 4. Menyusun tabel Riwayat Aktivitas Stok Terbaru
        # Mengambil 5 aktivitas OBAT MASUK terakhir. (Kita gabungkan tabel obat_masuk, detail_masuk, dan obat untuk mendapat namanya)
        $masuk = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('obat_masuk.tanggal_masuk as tanggal', 'obat.nama_obat', 'detail_masuk.jumlah_masuk as jumlah', DB::raw("'Masuk' as tipe"))
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        # Mengambil 5 aktivitas OBAT KELUAR terakhir
        $keluar = DB::table('obat_keluar')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select('obat_keluar.tanggal_keluar as tanggal', 'obat.nama_obat', 'detail_keluar.jumlah_keluar as jumlah', DB::raw("'Keluar' as tipe"))
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        # Menggabungkan koleksi data 'Masuk' dan 'Keluar' tadi menjadi satu tabel,
        # mengurutkan semuanya berdasarkan tanggal terbaru, lalu memotongnya hanya menjadi 5 baris teratas saja
        $aktivitasTerbaru = $masuk->concat($keluar)->sortByDesc('tanggal')->take(5);

        # Tampilkan dashboard petugas beserta datanya
        return view('petugas.dashboard', compact('totalObat', 'stokMenipis', 'akanKedaluwarsa', 'aktivitasTerbaru'));
    }

    # --- (PLACEHOLDER) FUNGSI-FUNGSI PETUGAS APOTEK YANG SEDANG DALAM PENGERJAAN ---
    public function katalogObat()
    {
        return "Halaman Katalog Obat Petugas Apotek (Dalam Pengerjaan)";
    }

    public function obatMasuk()
    {
        return "Halaman Transaksi Obat Masuk Petugas Apotek (Dalam Pengerjaan)";
    }

    public function obatKeluar()
    {
        return "Halaman Transaksi Obat Keluar Petugas Apotek (Dalam Pengerjaan)";
    }

    public function stokOpname()
    {
        return "Halaman Stok Opname Petugas Apotek (Dalam Pengerjaan)";
    }

    public function notifikasiPetugas()
    {
        return "Halaman Notifikasi Petugas Apotek (Dalam Pengerjaan)";
    }

    public function profilPetugas()
    {
        return "Halaman Profil Petugas Apotek (Dalam Pengerjaan)";
    }

    public function pengaturanPetugas()
    {
        return "Halaman Pengaturan Petugas Apotek (Dalam Pengerjaan)";
    }
}
