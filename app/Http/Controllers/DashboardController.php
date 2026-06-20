<?php

# =====================================================================
# BAGIAN 1: PERSIAPAN (IMPOR KELAS DAN PENGATURAN LOKASI)
# =====================================================================

# Mendefinisikan namespace (alamat folder) tempat controller ini berada agar dikenali oleh sistem Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangkap data ketikan/inputan dari form atau URL
use Illuminate\Http\Request;;
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

        # Catat penghapusan data ini ke log audit menggunakan nama yang sudah kita amankan tadi
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
        # --- 1. LOGIKA UNTUK WIDGET KARTU DI ATAS ---
        # Hitung Faktur Menunggu Verifikasi (Draft)
        $fakturMenunggu = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->count();

        # Hitung Otorisasi Permintaan/Pemusnahan Menunggu
        $pemusnahanMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'Pemusnahan/Rusak')
            ->count();

        # Hitung Obat Kritis (Stok <= Batas Minimal)
        $obatKritis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->count();


        # --- 2. LOGIKA UNTUK GRAFIK PETA KEDALUWARSA (DEFECTA) ---
        $hariIni = \Carbon\Carbon::now();
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3);
        $enamBulan = \Carbon\Carbon::now()->addMonths(6);

        $kadaluwarsa = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '<', $hariIni)->count();
        $kritis = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$hariIni, $tigaBulan])->count();
        $peringatan = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$tigaBulan->copy()->addDay(), $enamBulan])->count();
        $aman = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '>', $enamBulan)->count();


        # --- 3. LOGIKA UNTUK TABEL DAFTAR TUGAS TERBARU ---
        # Mengambil 5 faktur terbaru yang berstatus Draft
        $fakturPending = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(5)
            ->get();


        # --- 4. KIRIM SEMUA DATA KE VIEW (HANYA BOLEH ADA 1 RETURN DI SINI) ---
        return view('kepala.dashboard', [
            'title' => 'Dashboard Kepala Apotek',
            'fakturMenunggu' => $fakturMenunggu,
            'pemusnahanMenunggu' => $pemusnahanMenunggu,
            'obatKritis' => $obatKritis,
            'fakturPending' => $fakturPending,
            'dataDefecta' => [$aman, $peringatan, $kritis, $kadaluwarsa]
        ]);
    }

    # --- HALAMAN VALIDASI TRANSAKSI (KEPALA APOTEK) ---
    public function verifikasi()
    {
        # 1. Menghitung total faktur yang berstatus Draft
        $totalMenunggu = DB::table('obat_masuk')->where('status_verifikasi', 'Draft')->count();

        # 2. Menghitung jumlah faktur "Urgent"
        # (Faktur yang didalamnya terdapat obat dengan masa kedaluwarsa kurang dari 3 bulan)
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');

        $urgentCount = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->where('obat_masuk.status_verifikasi', 'Draft')
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulan)
            ->distinct('obat_masuk.id_masuk')
            ->count('obat_masuk.id_masuk');

        # 3. Mengambil daftar faktur Draft beserta perhitungan jumlah item di dalamnya (Subquery)
        $fakturList = DB::table('obat_masuk')
            ->select(
                'obat_masuk.*',
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk) as jumlah_item'),
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk AND tgl_kadaluwarsa <= "' . $tigaBulan . '") as ada_urgent')
            )
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'asc') # Diurutkan dari yang paling lama menunggu
            ->get();

        return view('kepala.verifikasi', [
            'title' => 'Verifikasi Faktur',
            'totalMenunggu' => $totalMenunggu,
            'urgentCount' => $urgentCount,
            'fakturList' => $fakturList
        ]);
    }

    public function detailVerifikasi(string $id_masuk)
    {
        # 1. Ambil data Header Faktur (Digabung dengan tabel pengguna untuk mendapat nama petugas)
        $faktur = DB::table('obat_masuk')
            ->join('pengguna', 'obat_masuk.id_pengguna', '=', 'pengguna.id_pengguna')
            ->select('obat_masuk.*', 'pengguna.nama_lengkap')
            ->where('id_masuk', $id_masuk)
            ->first();

        if (!$faktur) {
            abort(404, 'Dokumen faktur tidak ditemukan di dalam sistem.');
        }

        # 2. Ambil data rincian item obat di dalam faktur tersebut
        $detailObat = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.dosis', 'obat.bentuk_sediaan')
            ->where('detail_masuk.id_masuk', $id_masuk)
            ->get();

        return view('kepala.detail_verifikasi', [
            'title' => 'Detail Verifikasi Faktur',
            'faktur' => $faktur,
            'detailObat' => $detailObat
        ]);
    }

    # --- FUNGSI UNTUK MENGEKSEKUSI PERSETUJUAN/PENOLAKAN FAKTUR ---
    public function prosesVerifikasi(Request $request, string $id_masuk)
    {
        # 1. Pastikan data status yang dikirim dari tombol form valid (hanya boleh Disetujui atau Ditolak)
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        $statusBaru = $request->status;

        # 2. Cari data header faktur di database berdasarkan ID yang dikirim
        $faktur = DB::table('obat_masuk')->where('id_masuk', $id_masuk)->first();

        # Keamanan ganda: Jangan proses jika faktur tidak ditemukan atau statusnya sudah bukan 'Draft'
        if (!$faktur || $faktur->status_verifikasi != 'Draft') {
            return redirect()->route('kepala.verifikasi')->with('error', 'Faktur tidak valid atau sudah pernah diproses.');
        }

        # 3. Mulai proses transaksi Database secara aman (DB::transaction)
        # Jika di tengah jalan terjadi error mati lampu/koneksi terputus, semua perubahan akan dibatalkan otomatis
        DB::transaction(function () use ($id_masuk, $statusBaru, $faktur) {

            # A. Update status faktur di tabel obat_masuk menjadi 'Disetujui' atau 'Ditolak'
            DB::table('obat_masuk')
                ->where('id_masuk', $id_masuk)
                ->update(['status_verifikasi' => $statusBaru]);

            # B. JIKA DISETUJUI: Eksekusi penambahan stok ke tabel master 'obat'
            if ($statusBaru == 'Disetujui') {
                # Ambil semua rincian item obat yang ada di dalam faktur ini
                $detailFaktur = DB::table('detail_masuk')->where('id_masuk', $id_masuk)->get();

                # Lakukan perulangan untuk menambahkan stok masing-masing obat
                foreach ($detailFaktur as $item) {
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->increment('total_stok', $item->jumlah_masuk);
                }
            }

            # C. Catat keputusan Kepala Apotek ini ke dalam Buku Log (Audit Trail)
            $kataKerja = ($statusBaru == 'Disetujui') ? 'Menyetujui' : 'Menolak';
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => $kataKerja . " faktur masuk dengan No: " . $faktur->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # 4. Kembalikan Kepala Apotek ke halaman daftar verifikasi dengan pesan sukses
        $pesanNotif = ($statusBaru == 'Disetujui')
            ? 'Faktur disetujui! Stok obat berhasil ditambahkan ke dalam inventaris.'
            : 'Faktur telah ditolak dan diarsipkan.';

        return redirect()->route('kepala.verifikasi')->with('success', $pesanNotif);
    }

    # --- FUNGSI UNTUK HALAMAN OTORISASI PEMUSNAHAN ---
    public function pemusnahan(Request $request)
    {
        # 1. Tangkap parameter klik tab dari URL (default-nya adalah 'Menunggu')
        $tab = $request->query('tab', 'Menunggu');

        # 2. Hitung total antrean (untuk angka di dalam badge tombol biru)
        $totalMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'like', '%Pemusnahan%')
            ->count();

        # 3. Mulai meracik query pencarian data
        $query = DB::table('obat_keluar')
            ->join('pengguna', 'obat_keluar.id_pengguna', '=', 'pengguna.id_pengguna')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select(
                'obat_keluar.id_keluar',
                'obat_keluar.tanggal_keluar',
                'obat_keluar.status_otorisasi', # Penting untuk dicek di tabel
                'obat_keluar.tujuan_pengeluaran',
                'pengguna.nama_lengkap',
                'pengguna.peran',
                'obat.nama_obat',
                'obat.dosis',
                'obat.satuan_dosis',
                'detail_keluar.jumlah_keluar'
            )
            ->where('obat_keluar.tujuan_pengeluaran', 'like', '%Pemusnahan%');

        # 4. Saring data sesuai tombol filter yang sedang diklik Kepala Apotek
        if ($tab == 'Menunggu') {
            $query->where('obat_keluar.status_otorisasi', 'Menunggu');
        } elseif ($tab == 'Disetujui') {
            $query->where('obat_keluar.status_otorisasi', 'Disetujui');
        } elseif ($tab == 'Ditolak') {
            $query->where('obat_keluar.status_otorisasi', 'Ditolak');
        }
        # (Catatan: Jika tab == 'Semua', query dibiarkan los tanpa filter status tambahan)

        # Urutkan dari yang terbaru
        $query->orderBy('obat_keluar.tanggal_keluar', 'desc');

        # Eksekusi pencarian
        $pengajuanList = $query->get();

        return view('kepala.pemusnahan', [
            'title' => 'Otorisasi Pemusnahan',
            'totalMenunggu' => $totalMenunggu,
            'pengajuanList' => $pengajuanList,
            'activeTab' => $tab # Kirim nama tab ke Blade agar warnanya bisa disesuaikan
        ]);
    }

    # --- FUNGSI UNTUK MENGEKSEKUSI PEMUSNAHAN (SETUJU/TOLAK) ---
    public function prosesPemusnahan(Request $request, string $id_keluar)
    {
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        $statusBaru = $request->status;

        $pengajuan = DB::table('obat_keluar')->where('id_keluar', $id_keluar)->first();

        if (!$pengajuan || $pengajuan->status_otorisasi != 'Menunggu') {
            return redirect()->route('kepala.pemusnahan')->with('error', 'Data tidak valid.');
        }

        DB::transaction(function () use ($id_keluar, $statusBaru, $pengajuan) {

            # Update status di tabel obat_keluar
            DB::table('obat_keluar')
                ->where('id_keluar', $id_keluar)
                ->update(['status_otorisasi' => $statusBaru]);

            # Jika disetujui, kurangi total_stok di tabel obat
            if ($statusBaru == 'Disetujui') {
                $detailKeluar = DB::table('detail_keluar')->where('id_keluar', $id_keluar)->get();
                foreach ($detailKeluar as $item) {
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->decrement('total_stok', $item->jumlah_keluar); # Decrement = mengurangi stok
                }
            }

            # Catat ke Log Audit
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

    # --- FUNGSI UNTUK PUSAT LAPORAN & STOK ---
    public function laporan(Request $request)
    {
        # 1. Tangkap parameter filter kategori dari URL (jika ada)
        $kategoriPilihan = $request->query('kategori', 'semua');

        # 2. Hitung statistik untuk 3 Kartu KPI di atas
        $totalItem = DB::table('obat')->count();

        # Menghitung stok kritis (stok yang lebih kecil atau sama dengan batas minimal, tapi belum 0)
        # Digabung dengan stok kosong agar Kepala Apotek tahu total obat yang bermasalah
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # Menghitung berapa jenis obat yang punya batch kedaluwarsa (lewat hari ini)
        $expiredCount = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<', now())
            ->distinct('id_obat')
            ->count('id_obat');

        # 3. Ambil daftar Kategori untuk isi dropdown filter
        $kategoriList = DB::table('kategori')->get();

        # 4. Tarik data obat beserta nama kategorinya
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Jika Kepala Apotek memilih kategori tertentu di dropdown, saring datanya
        if ($kategoriPilihan != 'semua') {
            $query->where('obat.id_kategori', $kategoriPilihan);
        }

        # Urutkan berdasarkan nama obat sesuai abjad, lalu bagi menjadi 10 baris per halaman (Pagination)
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

    # --- FUNGSI UNTUK PUSAT NOTIFIKASI KEPALA APOTEK ---
    public function notifikasiKepala(Request $request)
    {
        # Tangkap parameter tab dari URL
        $tab = $request->query('tab', 'Semua');

        # Siapkan penampung data (Collection)
        $notifikasiList = collect();

        # 1. Peringatan: Faktur Menunggu Verifikasi
        $fakturDraft = DB::table('obat_masuk')
            ->join('pengguna', 'obat_masuk.id_pengguna', '=', 'pengguna.id_pengguna')
            ->where('status_verifikasi', 'Draft')
            ->get();

        foreach ($fakturDraft as $f) {
            $notifikasiList->push((object)[
                'tipe' => 'Persetujuan',
                'judul' => 'Verifikasi Faktur Baru',
                'pesan' => "Supplier <strong>{$f->nama_supplier}</strong>: Petugas {$f->nama_lengkap} telah menginput faktur <strong>#{$f->no_faktur}</strong>. Segera lakukan pengecekan.",
                'waktu' => $f->tanggal_masuk,
                'url' => route('kepala.verifikasi'),
                'ikon' => 'receipt_long',
                'warna' => 'primary'
            ]);
        }

        # 2. Peringatan: Otorisasi Pemusnahan
        $pemusnahanDraft = DB::table('obat_keluar')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'Pemusnahan/Rusak')
            ->get();

        foreach ($pemusnahanDraft as $p) {
            $notifikasiList->push((object)[
                'tipe' => 'Persetujuan',
                'judul' => 'Otorisasi Pemusnahan',
                'pesan' => "<strong>{$p->nama_obat}</strong> ({$p->jumlah_keluar} {$p->satuan_dosis}): Terdeteksi rusak/ED. Membutuhkan persetujuan pemusnahan.",
                'waktu' => $p->tanggal_keluar,
                'url' => route('kepala.pemusnahan'),
                'ikon' => 'delete_forever',
                'warna' => 'primary'
            ]);
        }

        # 3. Peringatan: Stok Kritis / Kosong
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->get();

        foreach ($stokKritis as $s) {
            $notifikasiList->push((object)[
                'tipe' => 'Peringatan Stok',
                'judul' => ($s->total_stok == 0) ? 'Stok Obat Kosong' : 'Stok Minimum Tercapai',
                'pesan' => "Ketersediaan <strong>{$s->nama_obat}</strong> berada di angka kritis (<strong>{$s->total_stok} {$s->bentuk_sediaan}</strong>). Pertimbangkan untuk order ulang.",
                'waktu' => now()->toDateTimeString(), # Status real-time
                'url' => route('kepala.laporan'),
                'ikon' => ($s->total_stok == 0) ? 'error' : 'trending_down',
                'warna' => ($s->total_stok == 0) ? 'error' : 'warning'
            ]);
        }

        # 4. Peringatan: Obat Hampir/Telah Kedaluwarsa
        $expired = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->whereDate('tgl_kadaluwarsa', '<=', now()->addMonths(3))
            ->get();

        foreach ($expired as $e) {
            $isExpired = \Carbon\Carbon::parse($e->tgl_kadaluwarsa)->isPast();
            $notifikasiList->push((object)[
                'tipe' => 'Peringatan Stok',
                'judul' => $isExpired ? 'Obat Telah Kedaluwarsa' : 'Peringatan Kedaluwarsa Dini',
                'pesan' => "<strong>{$e->nama_obat}</strong> (Batch #{$e->nomor_batch}) " . ($isExpired ? "<strong>telah melewati masa kedaluwarsa!</strong>" : "akan kedaluwarsa dalam waktu dekat."),
                'waktu' => now()->toDateTimeString(), # Status real-time
                'url' => route('kepala.laporan'),
                'ikon' => 'warning',
                'warna' => 'error'
            ]);
        }

        # Saring berdasarkan Tab yang diklik Kepala Apotek
        if ($tab != 'Semua') {
            $notifikasiList = $notifikasiList->where('tipe', $tab);
        }

        # Urutkan dari yang terbaru (descending)
        $notifikasiList = $notifikasiList->sortByDesc('waktu');

        return view('kepala.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'notifikasiList' => $notifikasiList,
            'activeTab' => $tab
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN PROFIL KEPALA APOTEK ---
    public function profilKepala()
    {
        # Ambil data pengguna yang sedang login saat ini
        $user = Auth::user();

        return view('kepala.profil', [
            'title' => 'Profil Saya',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PEMBARUAN PROFIL KEPALA APOTEK ---
    public function updateProfilKepala(Request $request)
    {
        $user = Auth::user();

        # Validasi inputan (Nama wajib ada, username harus unik kecuali miliknya sendiri)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $user->id_pengguna . ',id_pengguna',
        ]);

        # Perbarui data di database
        $userModel = Pengguna::findOrFail($user->id_pengguna);
        $userModel->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # Catat aktivitas ini ke Log Audit
        LogAudit::create([
            'id_pengguna' => $user->id_pengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Pastikan route() ini mengarah ke nama rute yang benar-benar ada di web.php
        return redirect()->route('kepala.profil')->with('success', 'Profil Anda berhasil diperbarui!');
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
        return view('petugas.dashboard', [
            'title' => 'Dashboard Operasional',
            'totalObat' => $totalObat,
            'stokMenipis' => $stokMenipis,
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'aktivitasTerbaru' => $aktivitasTerbaru
        ]);
    }

    # --- FUNGSI UNTUK HALAMAN KATALOG OBAT (PETUGAS) ---
    public function katalogObat(Request $request)
    {
        # Menangkap parameter dari URL (kolom pencarian & dropdown filter)
        $search = $request->query('search');
        $kategoriId = $request->query('kategori');

        # Mulai meracik perintah pencarian data ke database (Tabel Obat + Tabel Kategori)
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Jika Petugas mengetik sesuatu di kotak pencarian...
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('obat.nama_obat', 'like', '%' . $search . '%')
                  ->orWhere('obat.id_obat', 'like', '%' . $search . '%');
            });
        }

        # Jika Petugas memilih kategori tertentu di dropdown filter...
        if ($kategoriId) {
            $query->where('obat.id_kategori', $kategoriId);
        }

        # Urutkan berdasarkan nama obat A-Z, lalu batasi 10 baris per halaman (Pagination)
        $obatList = $query->orderBy('obat.nama_obat', 'asc')->paginate(10);

        # Ambil daftar seluruh kategori untuk mengisi pilihan dropdown filter
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.katalog', [
            'title' => 'Inventaris & Katalog Obat',
            'obatList' => $obatList,
            'kategoriList' => $kategoriList,
            'search' => $search,
            'kategoriPilihan' => $kategoriId
        ]);
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

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN NOTIFIKASI PETUGAS ---
    public function notifikasiPetugas()
    {
        // CONTOH DATA DINAMIS
        $notifikasiList = [
            (object)[
                'jenis' => 'Faktur', 'judul' => 'Faktur Disetujui', 'pesan' => 'Faktur <strong>#INV-092</strong> telah diverifikasi.',
                'waktu' => 'Baru saja', 'is_read' => false,
            ],
            (object)[
                'jenis' => 'Stok Kritis', 'judul' => 'Stok Kritis', 'pesan' => 'Obat <strong>Amoxicillin 500mg</strong> sisa <strong>5 Strip</strong>.',
                'waktu' => 'Kemarin, 14:30', 'is_read' => false,
            ],
            (object)[
                'jenis' => 'Info', 'judul' => 'Jadwal Stok Opname', 'pesan' => 'Hari ini adalah jadwal stok opname.',
                'waktu' => 'Kemarin, 08:00', 'is_read' => true,
            ]
        ];

        // MENGOSONGKAN DATA (Sesuai keinginanmu saat ini)
        // Jadikan komentar baris di bawah ini jika ingin melihat tombol berubah Hijau Full!
        $notifikasiList = [];

        // LOGIKA PENGECEKAN: Adakah notifikasi yang belum dibaca (is_read == false)?
        $hasUnread = collect($notifikasiList)->contains('is_read', false);

        return view('petugas.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'notifikasiList' => $notifikasiList,
            'hasUnread' => $hasUnread // Melempar hasil pengecekan ke file Blade
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN PROFIL PETUGAS APOTEK ---
    public function profilPetugas()
    {
        return view('petugas.profil', [
            'title' => 'Profil Saya',
            // Memanggil data pengguna yang sedang login saat ini
            'user' => Auth::user()
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN FORM TAMBAH OBAT ---
    public function tambahObat()
    {
        # Ambil daftar kategori dari database untuk mengisi pilihan dropdown
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.tambah-obat', [
            'title' => 'Tambah Obat Baru',
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN DATA OBAT BARU KE DATABASE ---
    public function simpanObat(Request $request)
    {
        # 1. Validasi inputan dari form
        $request->validate([
            'nama_obat' => 'required|string|max:100',
            'id_kategori' => 'required|string',
            'dosis' => 'required|numeric',
            'satuan_dosis' => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak' => 'nullable|string|max:50',
            'batas_stok_min' => 'required|integer|min:0',
        ]);

        # 2. Buat ID Obat Otomatis (Contoh: Jika yang terakhir OBT002, maka ini jadi OBT003)
        $lastObat = DB::table('obat')->orderBy('id_obat', 'desc')->first();
        $newId = 'OBT001';
        if ($lastObat) {
            $lastNumber = (int) substr($lastObat->id_obat, 3); # Mengambil angka setelah kata 'OBT'
            $newId = 'OBT' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        # 3. Simpan data ke dalam tabel 'obat'
        DB::table('obat')->insert([
            'id_obat' => $newId,
            'id_kategori' => $request->id_kategori,
            'nama_obat' => $request->nama_obat,
            'dosis' => $request->dosis,
            'satuan_dosis' => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak' => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
            'total_stok' => 0, # Obat baru selalu diawali dengan stok 0 (akan bertambah lewat Faktur Masuk)
        ]);

        # 4. Catat aktivitas ini ke Log Audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menambahkan master obat baru: " . $request->nama_obat,
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # 5. Kembalikan ke halaman katalog dengan pesan sukses
        return redirect()->route('petugas.obat')->with('success', 'Obat baru berhasil ditambahkan ke katalog!');
    }

    public function editObat(string $id)
    {
        return "Halaman Form Edit Obat untuk ID: " . $id . " (Dalam Pengerjaan)";
    }

    # --- FUNGSI UNTUK MENYIMPAN PERUBAHAN PROFIL PETUGAS ---
    public function updateProfilPetugas(Request $request)
    {
        # Ambil data user yang sedang login saat ini
        $user = Auth::user();

        # Mendapatkan ID Pengguna
        $idPengguna = $user->id_pengguna ?? $user->id;

        # 1. Validasi inputan form (Ubah nama_pengguna menjadi nama_lengkap)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $idPengguna . ',id_pengguna',
        ]);

        # 2. Perbarui data menggunakan Query Builder
        DB::table('pengguna')
            ->where('id_pengguna', $idPengguna)
            ->update([
                'nama_lengkap' => $request->nama_lengkap, # <--- DISESUAIKAN
                'username'     => $request->username,
            ]);

        # 3. Kembalikan ke halaman profil dengan pesan sukses
        return redirect()->route('petugas.profil')->with('success', 'Data profil berhasil diperbarui!');
    }
}
