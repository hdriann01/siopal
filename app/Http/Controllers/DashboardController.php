<?php

# Mendefinisikan namespace tempat controller ini berada agar dikenali oleh sistem Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangkap data inputan dari form atau URL
use Illuminate\Http\Request;
# Mengimpor facade Auth untuk mendapatkan data pengguna yang sedang login saat ini
use Illuminate\Support\Facades\Auth;
# Mengimpor facade DB untuk melakukan query langsung ke database (Query Builder)
use Illuminate\Support\Facades\DB;
# Mengimpor model Pengguna untuk berinteraksi dengan tabel 'pengguna'
use App\Models\Pengguna;
# Mengimpor model LogAudit untuk berinteraksi dengan tabel 'log_audit'
use App\Models\LogAudit;
# Mengimpor model Notifikasi untuk berinteraksi dengan tabel 'notifikasi'
use App\Models\Notifikasi;
# Mengimpor model Pengaturan untuk berinteraksi dengan tabel 'pengaturan'
use App\Models\Pengaturan;
# Mengimpor facade Hash untuk mengenkripsi (hashing) password demi keamanan
use Illuminate\Support\Facades\Hash;
# Mengimpor helper Str untuk menghasilkan string acak (random)
use Illuminate\Support\Str;
# Mengimpor facade Pdf dari library DomPDF untuk membuat dan mengunduh file PDF
use Barryvdh\DomPDF\Facade\Pdf;

# Mendeklarasikan class DashboardController yang mewarisi fitur Controller bawaan Laravel
class DashboardController extends Controller
{
    # --- FUNGSI UNTUK DASHBOARD UTAMA ADMINISTRATOR ---
    # --- FUNGSI UNTUK DASHBOARD UTAMA ADMINISTRATOR ---
    public function admin()
    {
        # Menghitung total keseluruhan data (Statistik Angka Biasa)
        $totalPengguna = Pengguna::count();
        $totalObat = DB::table('obat')->count();
        $totalMasuk = DB::table('obat_masuk')->count();
        $totalKeluar = DB::table('obat_keluar')->count();

        # --- LOGIKA UNTUK GRAFIK TREN (7 HARI TERAKHIR) ---
        $labelTanggal = []; # Array untuk menyimpan label tanggal di sumbu X (bawah) grafik
        $dataMasuk = [];    # Array untuk menyimpan data jumlah obat masuk di sumbu Y (kiri)
        $dataKeluar = [];   # Array untuk menyimpan data jumlah obat keluar di sumbu Y (kiri)

        # Melakukan perulangan (looping) mundur dari 6 hari yang lalu sampai hari ini (total 7 titik data)
        for ($i = 6; $i >= 0; $i--) {
            # Mengambil tanggal acuan (format: YYYY-MM-DD)
            $tanggal = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');

            # Memformat tanggal menjadi lebih ringkas (contoh: "04 Jun") untuk ditampilkan di label grafik
            $labelTanggal[] = \Carbon\Carbon::now()->subDays($i)->format('d M');

            # Menghitung jumlah transaksi masuk berdasarkan kolom 'tanggal_masuk'
            $masuk = DB::table('obat_masuk')->whereDate('tanggal_masuk', $tanggal)->count();
            $dataMasuk[] = $masuk;

            # Menghitung jumlah transaksi keluar berdasarkan kolom 'tanggal_keluar'
            $keluar = DB::table('obat_keluar')->whereDate('tanggal_keluar', $tanggal)->count();
            $dataKeluar[] = $keluar;
        }

        # Mengembalikan halaman dashboard sekaligus mengirimkan data-data array grafik tadi
        return view('admin.dashboard', [
            'title' => 'Dashboard Administrator',
            'totalPengguna' => $totalPengguna,
            'totalObat' => $totalObat,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            # Data untuk grafik tren obat
            'labelTanggal' => $labelTanggal,
            'dataMasuk' => $dataMasuk,
            'dataKeluar' => $dataKeluar,
        ]);
    }

    # --- FUNGSI UNTUK HALAMAN MANAJEMEN PENGGUNA (BACA DATA & PENCARIAN) ---
    public function manajemenUser(Request $request)
    {
        # Menangkap parameter pencarian 'search' dari URL (jika pengguna mengetik di kolom pencarian)
        $search = $request->query('search');

        # Mengecek apakah ada kata kunci pencarian yang dimasukkan
        if ($search) {
            # Jika ada, cari data di tabel pengguna yang nama_lengkap atau username-nya mirip dengan kata kunci
            $pengguna = Pengguna::where('nama_lengkap', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->get();
        } else {
            # Jika kotak pencarian kosong, ambil seluruh data pengguna tanpa terkecuali
            $pengguna = Pengguna::all();
        }

        # Menghitung total jumlah pengguna yang ada di database
        $totalPengguna = Pengguna::count();

        # Menampilkan halaman manajemen-user sambil mengirim data hasil query
        return view('admin.manajemen-user', [
            'title' => 'Manajemen Pengguna',
            'pengguna' => $pengguna,
            'totalPengguna' => $totalPengguna
        ]);
    }

    # --- FUNGSI UNTUK MENAMPILKAN FORM TAMBAH PENGGUNA BARU ---
    public function tambahUser()
    {
        # Mengembalikan tampilan form kosong untuk menambah pengguna
        return view('admin.tambah-user', ['title' => 'Tambah Pengguna Baru']);
    }

    # --- FUNGSI UNTUK MENYIMPAN DATA PENGGUNA BARU KE DATABASE ---
    public function simpanUser(Request $request)
    {
        # Memvalidasi inputan form; memastikan data wajib diisi, sesuai format, dan username tidak boleh kembar
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
            'password'     => 'required|min:6', # Password minimal 6 karakter
        ]);

        # Menyimpan data pengguna baru tersebut ke dalam tabel 'pengguna'
        Pengguna::create([
            # Membuat ID Pengguna unik otomatis (contoh: USRX1Y2Z3)
            'id_pengguna'  => 'USR' . strtoupper(Str::random(7)),
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            # Mengacak password menjadi teks acak rahasia agar tidak bisa dibaca langsung di database
            'password'     => Hash::make($request->password),
            'peran'        => $request->peran,
        ]);

        # Mencatat aktivitas penambahan pengguna ini ke tabel log_audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna, # ID admin yang sedang menambahkan data
            'aktivitas'   => "Menambahkan pengguna baru: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Mengarahkan kembali ke halaman manajemen user dengan membawa pesan sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN FORM EDIT PENGGUNA (BERDASARKAN ID) ---
    public function editUser(string $id)
    {
        # Mencari pengguna spesifik berdasarkan ID, tampilkan error 404 jika tidak ditemukan
        $user = Pengguna::findOrFail($id);

        # Tampilkan form edit-user beserta data lama pengguna tersebut
        return view('admin.edit-user', [
            'title' => 'Edit Pengguna',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MEMPERBARUI (UPDATE) DATA PROFIL PENGGUNA DI DATABASE ---
    public function updateUser(Request $request, string $id)
    {
        # Mencari pengguna yang akan diubah datanya
        $user = Pengguna::findOrFail($id);

        # Memvalidasi inputan. Pengecualian pada 'unique' username: ia boleh memakai username miliknya sendiri
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $id . ',id_pengguna',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
        ]);

        # Melakukan proses update (timpa data lama dengan data baru)
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'peran'        => $request->peran,
        ]);

        # Mencatat aktivitas pengubahan data profil ini ke tabel log_audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data profil pengguna: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Mengarahkan kembali ke halaman manajemen dengan pesan sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN FORM RESET PASSWORD PENGGUNA TERTENTU ---
    public function resetPassword(string $id)
    {
        # Cari pengguna berdasarkan ID
        $user = Pengguna::findOrFail($id);

        # Tampilkan halaman khusus reset password
        return view('admin.reset-password', [
            'title' => 'Reset Password',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MEMPERBARUI PASSWORD PENGGUNA ---
    public function updatePassword(Request $request, string $id)
    {
        # Cari pengguna berdasarkan ID
        $user = Pengguna::findOrFail($id);

        # Validasi bahwa password baru minimal 6 karakter dan kolom konfirmasi password harus cocok ('confirmed')
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        # Update password di database dengan format terenkripsi (Hash)
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        # Catat aktivitas peresetan sandi ini ke log_audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Mereset password pengguna: " . $user->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembali ke halaman manajemen user dengan pesan sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Password pengguna berhasil direset!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN KONFIRMASI SEBELUM MENGHAPUS PENGGUNA ---
    public function konfirmasiHapus(string $id)
    {
        # Cari pengguna berdasarkan ID
        $user = Pengguna::findOrFail($id);

        # Tampilkan halaman konfirmasi
        return view('admin.hapus-user', [
            'title' => 'Konfirmasi Hapus',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MENGHAPUS DATA PENGGUNA SECARA PERMANEN ---
    public function prosesHapus(string $id, Request $request)
    {
        # Cari pengguna yang akan dihapus
        $user = Pengguna::findOrFail($id);

        # Simpan nama lengkap pengguna ke variabel cadangan sebelum datanya lenyap dari database
        $namaYangDihapus = $user->nama_lengkap;

        # Eksekusi perintah penghapusan baris data tersebut dari tabel pengguna
        $user->delete();

        # Catat aktivitas penghapusan ini ke log audit menggunakan nama yang sudah kita simpan tadi
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menghapus permanen pengguna: " . $namaYangDihapus,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembali ke halaman manajemen dengan pesan sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil dihapus permanen.');
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN LOG AUDIT SISTEM ---
    public function auditLogs(Request $request)
    {
        # Tangkap parameter filter 'role' dari URL dropdown
        $role = $request->query('role');

        # Mulai menyusun query: Ambil log audit beserta data penggunanya (relasi join), urutkan dari yang paling baru
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');

        # Jika ada filter peran yang dipilih, tambahkan syarat pemfilteran (whereHas)
        if ($role) {
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Eksekusi dan ambil data hasil akhirnya
        $logs = $query->get();

        # Tampilkan view log audit
        return view('admin.audit-logs', [
            'title' => 'Log Audit Sistem',
            'logs' => $logs
        ]);
    }

    # --- FUNGSI UNTUK PUSAT NOTIFIKASI ---
    public function notifikasi(Request $request)
    {
        # Menangkap parameter filter 'tab' (Semua, Keamanan, Sistem), nilai bawaannya adalah 'Semua'
        $tab = $request->query('tab', 'Semua');

        # Mulai menyusun query notifikasi, diurutkan dari yang terbaru
        $query = Notifikasi::orderBy('created_at', 'desc');

        # Jika tab bukan "Semua", saring berdasarkan tipe notifikasi
        if ($tab == 'Keamanan') {
            $query->where('tipe', 'Keamanan');
        } elseif ($tab == 'Sistem') {
            $query->where('tipe', 'Sistem');
        }

        # Eksekusi query notifikasi
        $notifikasi = $query->get();

        # Hitung berapa banyak notifikasi yang statusnya masih 'Belum' dibaca (untuk indikator angka)
        $belumDibaca = Notifikasi::where('status_baca', 'Belum')->count();

        # Tampilkan view pusat notifikasi
        return view('admin.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'notifikasi' => $notifikasi,
            'belumDibaca' => $belumDibaca,
            'activeTab' => $tab # Mengirim data tab mana yang sedang aktif ke view
        ]);
    }

    # --- FUNGSI UNTUK MENANDAI SEMUA NOTIFIKASI SEBAGAI TELAH DIBACA ---
    public function bacaSemuaNotifikasi()
    {
        # Cari semua notifikasi yang statusnya 'Belum', lalu ubah nilai kolom tersebut menjadi 'Sudah' sekaligus
        Notifikasi::where('status_baca', 'Belum')->update(['status_baca' => 'Sudah']);

        # Kembali ke halaman sebelumnya
        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN PENGATURAN SISTEM GLOBAL ---
    public function pengaturan()
    {
        # Mengambil konfigurasi baris pertama saja dari tabel 'pengaturan'
        $pengaturan = Pengaturan::first();

        # Menampilkan form pengaturan
        return view('admin.pengaturan', [
            'title' => 'Pengaturan Sistem',
            'pengaturan' => $pengaturan
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PEMBARUAN PENGATURAN SISTEM ---
    public function updatePengaturan(Request $request)
    {
        # Ambil baris pertama tabel pengaturan yang ingin diperbarui
        $pengaturan = Pengaturan::first();

        # Validasi teks identitas aplikasi
        $request->validate([
            'nama_apotek' => 'required|string|max:100',
            'alamat_apotek' => 'required|string',
        ]);

        # Lakukan pembaruan. Checkbox HTML (seperti toggle keamanan) jika dimatikan tidak akan mengirim data apa pun.
        # Karenanya, kita gunakan metode 'has()' untuk memeriksa apakah opsi itu dicentang atau tidak (menghasilkan angka 1 atau 0)
        $pengaturan->update([
            'nama_apotek' => $request->nama_apotek,
            'alamat_apotek' => $request->alamat_apotek,
            'wajib_password_kuat' => $request->has('wajib_password_kuat') ? 1 : 0,
            'auto_logout' => $request->has('auto_logout') ? 1 : 0,
            'log_audit_global' => $request->has('log_audit_global') ? 1 : 0,
        ]);

        # Catat aktivitas pembaruan ini ke dalam log audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => 'Memperbarui Pengaturan Sistem Aplikasi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembali ke halaman pengaturan dengan pesan sukses
        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN PROFIL PRIBADI AKUN YANG SEDANG LOGIN ---
    public function profil()
    {
        # Tarik data diri lengkap pengguna yang sedang aktif saat ini
        $user = Auth::user();

        # Tampilkan form edit profil
        return view('admin.profil', [
            'title' => 'Profil Saya',
            'user' => $user
        ]);
    }

    # --- FUNGSI UNTUK MEMPERBARUI DATA PROFIL PRIBADI ---
    public function updateProfil(Request $request)
    {
        # Ambil data pengguna yang sedang login
        $user = Auth::user();

        # Validasi input; khusus username dipastikan tidak kembar di tabel pengguna, kecuali username miliknya sendiri
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $user->id_pengguna . ',id_pengguna',
        ]);

        # Cari data berdasarkan ID, lalu jalankan fungsi pembaruan database
        $userModel = Pengguna::findOrFail($user->id_pengguna);
        $userModel->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # Catat bahwa profil telah diedit secara mandiri
        LogAudit::create([
            'id_pengguna' => $user->id_pengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Arahkan kembali dengan pesan berhasil
        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MEMBUAT DAN MENDOWNLOAD LAPORAN PDF DARI LOG AUDIT ---
    public function exportPdfAuditLogs(Request $request)
    {
        # Tangkap parameter 'role' jika pengguna saat ini sedang memfilter peran tertentu
        $role = $request->query('role');

        # Susun query sama persis seperti fungsi auditLogs agar data PDF sama dengan tampilan layar
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');
        if ($role) {
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Eksekusi dan simpan semua baris data yang cocok
        $logs = $query->get();

        # Panggil library DomPDF (Pdf) untuk me-render data ke dalam template HTML (pdf-audit-logs)
        $pdf = Pdf::loadView('admin.pdf-audit-logs', [
            'logs' => $logs,
            'role' => $role # Kirim juga nama perannya untuk dicetak sebagai judul di dalam PDF
        ]);

        # Instruksikan browser untuk langsung men-download hasil rendernya sebagai file PDF
        return $pdf->download('Laporan_Audit_Log_SIOPAL.pdf');
    }

    public function kepala()
    {
        # 1. Menghitung jumlah Faktur Masuk yang berstatus 'Draft'
        $menungguMasuk = DB::table('obat_masuk')->where('status_verifikasi', 'Draft')->count();

        # 2. Menghitung jumlah Permintaan Keluar/Pemusnahan yang berstatus 'Menunggu'
        $menungguKeluar = DB::table('obat_keluar')->where('status_otorisasi', 'Menunggu')->count();

        # 3. Menghitung jumlah Obat Kritis (total_stok <= batas_stok_min)
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # --- LOGIKA BARU UNTUK GRAFIK KEDALUWARSA (DEFECTA) ---
        $hariIni = \Carbon\Carbon::now();
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3);
        $enamBulan = \Carbon\Carbon::now()->addMonths(6);

        # Batch Kedaluwarsa (Tanggal ED sudah lewat dari hari ini)
        $kadaluwarsa = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '<', $hariIni)->count();

        # Batch Kritis (ED antara hari ini sampai 3 bulan ke depan)
        $kritis = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$hariIni, $tigaBulan])->count();

        # Batch Peringatan (ED antara 3 bulan sampai 6 bulan ke depan)
        # Menggunakan addDay() agar tidak ada tanggal yang beririsan dengan variabel $tigaBulan
        $peringatan = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$tigaBulan->copy()->addDay(), $enamBulan])->count();

        # Batch Aman (ED masih lebih dari 6 bulan)
        $aman = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '>', $enamBulan)->count();

        # 5. Mengambil 5 daftar tugas terbaru untuk tabel
        $fakturPending = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        return view('kepala.dashboard', [
            'title' => 'Dashboard Kepala Apotek',
            'menungguMasuk' => $menungguMasuk,
            'menungguKeluar' => $menungguKeluar,
            'stokKritis' => $stokKritis,
            'fakturPending' => $fakturPending,
            # Data array baru untuk dikirim ke grafik Bar
            'dataDefecta' => [$aman, $peringatan, $kritis, $kadaluwarsa]
        ]);
    }

    # --- PLACEHOLDER HALAMAN KEPALA APOTEK ---
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

    public function petugas()
    {
        # Mengembalikan halaman dashboard khusus untuk peran Petugas Apotek
        return view('petugas.dashboard', ['title' => 'Dashboard Petugas Apotek']);
    }
}
