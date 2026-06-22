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

    # --- Fungsi Profil Admin ---
    public function profil()
    {
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.admin',
            'actionUrl' => url('/simpan-profil-global') # Menggunakan URL absolut agar bebas error rute
        ]);
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

            # --- TAMBAHKAN KODE INI (D. Kirim Notifikasi Balasan ke Petugas) ---
            $judulNotif = ($statusBaru == 'Disetujui') ? 'Faktur Disetujui' : 'Faktur Ditolak';
            $pesanNotif = ($statusBaru == 'Disetujui')
                ? 'Faktur <strong>' . $faktur->no_faktur . '</strong> telah diverifikasi. Stok otomatis bertambah ke dalam inventaris.'
                : 'Pengajuan faktur <strong>' . $faktur->no_faktur . '</strong> dikembalikan. Silakan periksa kembali kecocokan fisik barang dengan nota cetak.';

            DB::table('notifikasi')->insert([
                'untuk_role'  => 'petugas',
                'tipe'        => 'Faktur',
                'judul'       => $judulNotif,
                'pesan'       => $pesanNotif,
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);
            # -------------------------------------------------------------------

        }); # <-- Ini adalah penutup DB::transaction

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


    # --- Fungsi Profil Kepala Apotek ---
    public function profilKepala()
    {
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.kepala',
            'actionUrl' => url('/simpan-profil-global')
        ]);
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

        # 1. Mengambil 5 aktivitas OBAT MASUK terakhir
        $masuk = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('obat_masuk.tanggal_masuk as tanggal', 'obat.nama_obat', 'detail_masuk.jumlah_masuk as jumlah', DB::raw("'Masuk' as tipe"))
            ->where('obat_masuk.status_verifikasi', 'Disetujui')
            ->orderBy('obat_masuk.tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        # 2. Mengambil 5 aktivitas OBAT KELUAR terakhir
        $keluar = DB::table('obat_keluar')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select('obat_keluar.tanggal_keluar as tanggal', 'obat.nama_obat', 'detail_keluar.jumlah_keluar as jumlah', DB::raw("'Keluar' as tipe"))
            ->where('obat_keluar.status_otorisasi', 'Disetujui')
            ->orderBy('obat_keluar.tanggal_keluar', 'desc')
            ->limit(5)
            ->get();

        # 3. Mengambil 5 aktivitas STOK OPNAME dari Log Audit
        $opname = DB::table('log_audit')
            ->select('created_at as tanggal', 'aktivitas')
            ->where('aktivitas', 'like', 'Stok Opname: Sinkronisasi%')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                # Membongkar teks log (Contoh: "...Sinkronisasi Paracetamol dari 50 menjadi 45 (Selisih: -5)...")
                preg_match('/Sinkronisasi (.*?) dari \d+ menjadi \d+ \(Selisih: ([-\d]+)\)/', $item->aktivitas, $matches);

                $nama_obat = $matches[1] ?? 'Penyesuaian Stok (Opname)';
                $selisih = (int) ($matches[2] ?? 0);

                return (object) [
                    'tanggal'   => $item->tanggal,
                    'nama_obat' => $nama_obat,
                    'jumlah'    => abs($selisih), # Ambil angka absolut (menghilangkan tanda minus jika ada)
                    'tipe'      => $selisih > 0 ? 'Opname (+)' : 'Opname (-)',
                ];
            });

        # 4. Gabungkan ketiga sumber data dan urutkan berdasarkan waktu paling baru
        $aktivitasTerbaru = $masuk->concat($keluar)->concat($opname)->sortByDesc('tanggal')->take(5);

        # Tampilkan dashboard petugas beserta datanya
        return view('petugas.dashboard', [
            'title' => 'Dashboard Operasional',
            'totalObat' => $totalObat,
            'stokMenipis' => $stokMenipis,
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'aktivitasTerbaru' => $aktivitasTerbaru
        ]);
    }

    # --- FUNGSI HALAMAN PERINGATAN STOK MENIPIS ---
    public function stokMenipis()
    {
        $stokMenipis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->orderBy('total_stok', 'asc')
            ->get();

        return view('petugas.stok-menipis', [
            'title' => 'Peringatan Stok Menipis',
            'stokMenipis' => $stokMenipis,
        ]);
    }

    # --- FUNGSI HALAMAN PERINGATAN OBAT KEDALUWARSA ---
    public function obatKedaluwarsa()
    {
        $tigaBulanKeDepan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');

        $akanKedaluwarsa = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.satuan_dosis', 'obat.bentuk_sediaan')
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulanKeDepan)
            ->orderBy('detail_masuk.tgl_kadaluwarsa', 'asc')
            ->get();

        return view('petugas.obat-kedaluwarsa', [
            'title' => 'Peringatan Obat Kedaluwarsa',
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'hariIni' => $hariIni
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
        # Ambil daftar obat dari database, urutkan berdasarkan abjad
        $obatList = DB::table('obat')->orderBy('nama_obat', 'asc')->get();

        return view('petugas.obat-masuk', [
            'title' => 'Catat Obat Masuk',
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN TRANSAKSI OBAT MASUK (DRAF) ---
    public function simpanObatMasuk(Request $request)
    {
        # 1. Validasi Input Dasar
        $request->validate([
            'no_faktur'     => 'required|string|max:100',
            'nama_supplier' => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'items'         => 'required|array|min:1', # Memastikan keranjang obat tidak kosong
        ]);

        # 2. Proses menggunakan Database Transaction agar aman
        # Jika tiba-tiba mati lampu saat menyimpan rincian, tabel utama tidak akan jadi "sampah"
        DB::transaction(function () use ($request) {

            # A. Membuat ID Masuk Otomatis (Contoh: INVM-001)
            $lastFaktur = DB::table('obat_masuk')->orderBy('id_masuk', 'desc')->first();
            $newIdMasuk = 'INVM-001';
            if ($lastFaktur) {
                # Mengambil 3 digit angka terakhir, lalu ditambah 1
                $lastNumber = (int) substr($lastFaktur->id_masuk, 5);
                $newIdMasuk = 'INVM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # B. Simpan Header ke tabel `obat_masuk`
            DB::table('obat_masuk')->insert([
                'id_masuk'          => $newIdMasuk,
                'no_faktur'         => $request->no_faktur,
                'nama_supplier'     => $request->nama_supplier,
                'tanggal_masuk'     => $request->tanggal_masuk,
                'id_pengguna'       => Auth::user()->id_pengguna, # Mencatat ID Petugas yang menginput
                'status_verifikasi' => 'Draft' # Otomatis berstatus Draf untuk menunggu verifikasi Kepala Apotek

                # BARIS 'created_at' => now() TELAH DIHAPUS DARI SINI
            ]);

            # C. Simpan rincian keranjang (Array) ke tabel `detail_masuk` secara massal
            $detailData = [];
            foreach ($request->items as $item) {
                $detailData[] = [
                    'id_masuk'        => $newIdMasuk,
                    'id_obat'         => $item['id_obat'],
                    'jumlah_masuk'    => $item['jumlah_masuk'],
                    'tgl_kadaluwarsa' => $item['tgl_kadaluwarsa'],
                    'nomor_batch'     => $item['nomor_batch'],
                ];
            }
            DB::table('detail_masuk')->insert($detailData);

            # D. Kirim Notifikasi Sistem otomatis ke Kepala Apotek
            DB::table('notifikasi')->insert([
                'untuk_role'  => 'kepala',
                'tipe'        => 'Faktur',
                'judul'       => 'Verifikasi Faktur Baru',
                'pesan'       => 'Terdapat draf faktur masuk <strong>' . $request->no_faktur . '</strong> yang menunggu persetujuan Anda.',
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);

            # E. Catat aktivitas ini ke Log Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menginput draf faktur masuk baru No: " . $request->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # 3. Kembalikan ke halaman form dengan pesan sukses
        return back()->with('success', 'Faktur berhasil disimpan sebagai Draf dan telah dikirim ke Kepala Apotek untuk diverifikasi!');
    }

    public function obatKeluar()
    {
        # Ambil daftar obat yang stoknya > 0 beserta Rekomendasi Nomor Batch (Sistem FEFO)
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

    # --- FUNGSI UNTUK MENYIMPAN TRANSAKSI OBAT KELUAR ---
    public function simpanObatKeluar(Request $request)
    {
        $request->validate([
            'tujuan_pengeluaran' => 'required|string|max:150',
            'referensi'          => 'nullable|string|max:100',
            'tanggal_keluar'     => 'required|date',
            'items'              => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            # 1. Buat ID Keluar Otomatis (Contoh: OUTM-001)
            $lastFaktur = DB::table('obat_keluar')->orderBy('id_keluar', 'desc')->first();
            $newIdKeluar = 'OUTM-001';
            if ($lastFaktur) {
                $lastNumber = (int) substr($lastFaktur->id_keluar, 5);
                $newIdKeluar = 'OUTM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # 2. Simpan Header ke tabel `obat_keluar`
            DB::table('obat_keluar')->insert([
                'id_keluar'          => $newIdKeluar,
                'tanggal_keluar'     => $request->tanggal_keluar,
                'tujuan_pengeluaran' => $request->tujuan_pengeluaran,
                'id_pengguna'        => Auth::user()->id_pengguna,
                # Jika tujuannya Pemusnahan, butuh otorisasi Kepala Apotek. Jika Pasien/Resep, langsung Disetujui.
                'status_otorisasi'   => str_contains(strtolower($request->tujuan_pengeluaran), 'musnah') ? 'Menunggu' : 'Disetujui',
            ]);

            # 3. Simpan rincian ke `detail_keluar` & Kurangi stok (jika bukan pemusnahan)
            $detailData = [];
            foreach ($request->items as $item) {
                $detailData[] = [
                    'id_keluar'     => $newIdKeluar,
                    'id_obat'       => $item['id_obat'],
                    'jumlah_keluar' => $item['jumlah_keluar'],
                ];

                # Langsung potong stok jika ini pengeluaran resep biasa
                if (!str_contains(strtolower($request->tujuan_pengeluaran), 'musnah')) {
                    DB::table('obat')
                        ->where('id_obat', $item['id_obat'])
                        ->decrement('total_stok', $item['jumlah_keluar']);
                }
            }
            DB::table('detail_keluar')->insert($detailData);

            # 4. Kirim Notifikasi ke Kepala Apotek (Hanya jika butuh otorisasi pemusnahan)
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

            # 5. Catat Log Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Mencatat obat keluar No: " . $newIdKeluar . " (" . $request->tujuan_pengeluaran . ")",
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        return redirect()->route('petugas.dashboard')->with('success', 'Transaksi pengeluaran obat berhasil dicatat!');
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN STOK OPNAME ---
    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN STOK OPNAME ---
    public function stokOpname(Request $request)
    {
        # 1. Tangkap filter rak jika petugas memilih dari dropdown
        $rakPilihan = $request->query('rak');

        # 2. Ambil semua letak rak yang unik dari database untuk mengisi pilihan dropdown
        $rakList = DB::table('obat')->select('letak_rak')->whereNotNull('letak_rak')->where('letak_rak', '!=', '')->distinct()->pluck('letak_rak');

        # 3. Tarik data obat, filter jika rak dipilih
        $query = DB::table('obat')->orderBy('nama_obat', 'asc');
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

    # --- FUNGSI UNTUK MENYIMPAN HASIL AUDIT STOK OPNAME ---
    public function simpanStokOpname(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            $jumlahPerubahan = 0;

            # Lakukan perulangan untuk mengecek satu per satu obat yang dikirim
            foreach ($request->items as $id_obat => $data) {

                # Abaikan jika kolom Stok Fisik tidak diisi oleh petugas
                if (!isset($data['stok_fisik']) || $data['stok_fisik'] === '') {
                    continue;
                }

                $stokFisik = (int) $data['stok_fisik'];
                $obat = DB::table('obat')->where('id_obat', $id_obat)->first();

                # Jika ada selisih antara fisik dan sistem
                if ($obat && $obat->total_stok != $stokFisik) {
                    $selisih = $stokFisik - $obat->total_stok;
                    $keterangan = $data['keterangan'] ?? 'Tanpa keterangan';

                    # 1. Update stok utama di sistem menjadi sama dengan fisik
                    DB::table('obat')->where('id_obat', $id_obat)->update([
                        'total_stok' => $stokFisik
                    ]);

                    # 2. Catat penyesuaian ini ke Log Audit
                    LogAudit::create([
                        'id_pengguna' => Auth::user()->id_pengguna,
                        'aktivitas'   => "Stok Opname: Sinkronisasi " . $obat->nama_obat . " dari " . $obat->total_stok . " menjadi " . $stokFisik . " (Selisih: " . $selisih . "). Ket: " . $keterangan,
                        'alamat_ip'   => request()->ip(),
                        'status'      => 'Success',
                        'created_at'  => now(),
                    ]);

                    $jumlahPerubahan++;
                }
            }

            # Jika tidak ada perubahan sama sekali, simpan pesan khusus ke flash data
            if ($jumlahPerubahan == 0) {
                session()->flash('info', 'Tidak ada selisih yang ditemukan. Stok sistem sudah sesuai dengan fisik.');
            }
        });

        # Kembalikan ke Dashboard dengan pesan sukses
        if (session()->has('info')) {
            return redirect()->route('petugas.dashboard'); // Info ditangani di dashboard jika ada alert khusus
        }

        return redirect()->route('petugas.dashboard')->with('success', 'Audit Stok Opname selesai! Data stok telah disinkronkan dengan fisik.');
    }

    # --- Fungsi Profil Petugas Apotek ---
    public function profilPetugas()
    {
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.petugas',
            'actionUrl' => url('/simpan-profil-global')
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

    # --- FUNGSI UNTUK MENAMPILKAN FORM EDIT (DENGAN DATA LAMA) ---
    public function editObat(string $id)
    {
        # Ambil data obat spesifik berdasarkan ID
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        # Jika ada orang iseng mengetik ID sembarangan di URL, kembalikan ke katalog
        if (!$obat) {
            return redirect()->route('petugas.obat')->withErrors('Data obat tidak ditemukan.');
        }

        # Ambil daftar kategori untuk dropdown
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.edit-obat', [
            'title' => 'Edit Data Obat',
            'obat' => $obat,
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI UNTUK MENYIMPAN PERUBAHAN KE DATABASE ---
    public function updateObat(Request $request, string $id)
    {
        # 1. Validasi inputan form
        $request->validate([
            'nama_obat' => 'required|string|max:100',
            'id_kategori' => 'required|string',
            'dosis' => 'required|numeric',
            'satuan_dosis' => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak' => 'nullable|string|max:50',
            'batas_stok_min' => 'required|integer|min:0',
        ]);

        # 2. Perbarui data di dalam tabel 'obat'
        DB::table('obat')->where('id_obat', $id)->update([
            'id_kategori' => $request->id_kategori,
            'nama_obat' => $request->nama_obat,
            'dosis' => $request->dosis,
            'satuan_dosis' => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak' => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
        ]);

        # 3. Catat aktivitas ini ke Log Audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data master obat: " . $request->nama_obat . " (ID: " . $id . ")",
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # 4. Kembalikan ke halaman katalog dengan pesan sukses
        return redirect()->route('petugas.obat')->with('success', 'Data obat berhasil diperbarui!');
    }

    # --- FUNGSI UNTUK MENGHAPUS OBAT DARI KATALOG ---
    public function hapusObat(string $id, Request $request)
    {
        # 1. Cari data obat berdasarkan ID yang dikirim dari tombol
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        # Jika obat tidak ditemukan, kembalikan dengan pesan error
        if (!$obat) {
            return back()->with('error', 'Gagal: Data obat tidak ditemukan di database.');
        }

        # Simpan nama obat ke variabel sementara untuk keperluan pencatatan riwayat (Log)
        $namaObat = $obat->nama_obat;

        try {
            # 2. Hapus data obat tersebut dari tabel 'obat'
            DB::table('obat')->where('id_obat', $id)->delete();

            # 3. Catat aktivitas penghapusan ini ke Log Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menghapus master obat dari katalog: " . $namaObat,
                'alamat_ip'   => $request->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);

            # 4. Kembalikan ke halaman sebelumnya dengan pesan sukses warna hijau
            return back()->with('success', 'Data obat "' . $namaObat . '" berhasil dihapus dari katalog!');
        } catch (\Illuminate\Database\QueryException $e) {
            # KEAMANAN DATABASE: Jika obat gagal dihapus karena sedang dipakai di tabel Transaksi (Obat Masuk/Keluar)
            return back()->with('error', 'Gagal: Obat "' . $namaObat . '" tidak bisa dihapus karena memiliki riwayat transaksi di sistem.');
        }
    }

    # --- FUNGSI UNTUK MENAMPILKAN HALAMAN NOTIFIKASI GLOBAL ---
    public function pusatNotifikasi()
    {
        # 1. Identifikasi siapa yang sedang login
        $user = Auth::user();

        $nilaiRole = $user->peran ?? '';
        $roleRaw = strtolower($nilaiRole);

        if ($roleRaw === 'admin' || $roleRaw === 'administrator' || $roleRaw === '1') {
            $role = 'admin';
        } elseif (str_contains($roleRaw, 'kepala') || $roleRaw === '2') {
            $role = 'kepala';
        } else {
            $role = 'petugas';
        }

        # 2. Tentukan Layout (Sidebar & Navbar) mana yang harus dipakai
        $layout = 'layouts.' . $role;

        # 3. Ambil data notifikasi ASLI dari database khusus untuk role tersebut
        $notifikasiList = DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->orderBy('created_at', 'desc')
            ->get();

        # 4. Cek apakah ada yang belum dibaca untuk mewarnai tombol
        $hasUnread = $notifikasiList->contains('status_baca', 'Belum');

        # 5. Lempar ke file Blade tunggal
        return view('shared.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'layout' => $layout,
            'role' => $role,
            'notifikasiList' => $notifikasiList,
            'hasUnread' => $hasUnread
        ]);
    }

    # --- FUNGSI UNTUK TANDAI SEMUA DIBACA SECARA AMAN ---
    public function bacaSemuaNotifikasi()
    {
        $user = Auth::user();

        # PERBAIKAN: Menggunakan $user->peran agar tidak error
        $nilaiRole = $user->peran ?? '';
        $roleRaw = strtolower($nilaiRole);

        if ($roleRaw === 'admin' || $roleRaw === 'administrator' || $roleRaw === '1') {
            $role = 'admin';
        } elseif (str_contains($roleRaw, 'kepala') || $roleRaw === '2') {
            $role = 'kepala';
        } else {
            $role = 'petugas';
        }

        # HANYA update notifikasi milik role yang menekan tombol ini
        DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->where('status_baca', 'Belum')
            ->update(['status_baca' => 'Sudah']);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    # --- FUNGSI GLOBAL UNTUK MENYIMPAN PROFIL SEMUA PERAN ---
    public function simpanProfilGlobal(Request $request)
    {
        $user = Auth::user();
        $idPengguna = $user->id_pengguna ?? $user->id;

        # 1. Validasi: Username tidak boleh dipakai orang lain
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $idPengguna . ',id_pengguna',
        ]);

        # 2. Update data profil
        DB::table('pengguna')->where('id_pengguna', $idPengguna)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # 3. Catat di Log Audit
        LogAudit::create([
            'id_pengguna' => $idPengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi secara mandiri',
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # 4. Kembalikan ke halaman sebelumnya (apapun role-nya)
        return back()->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    # --- FUNGSI EXPORT LAPORAN EXCEL UNTUK KEPALA APOTEK ---
    public function exportLaporanExcel()
    {
        $namaFile = 'Laporan_Stok_SIOPAL_' . date('Y-m-d_H-i') . '.csv';

        // Mengambil data obat beserta sisa stoknya
        $dataObat = DB::table('obat')
            ->select('id_obat', 'id_kategori', 'nama_obat', 'dosis', 'satuan_dosis', 'bentuk_sediaan', 'letak_rak', 'batas_stok_min', 'total_stok')
            ->orderBy('nama_obat', 'asc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$namaFile",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($dataObat) {
            $file = fopen('php://output', 'w');

            // Header Kolom Excel (PENTING: Gunakan titik koma ';' agar rapi di Excel format Indonesia)
            fputcsv($file, ['ID Obat', 'Nama Obat', 'Kategori', 'Sediaan', 'Letak Rak', 'Batas Minimal', 'Stok Saat Ini', 'Status'], ';');

            // Looping data dari database ke dalam baris Excel
            foreach ($dataObat as $obat) {
                // Beri label status otomatis
                $status = $obat->total_stok <= $obat->batas_stok_min ? 'Menipis (Perlu Restok)' : 'Aman';

                fputcsv($file, [
                    $obat->id_obat,
                    $obat->nama_obat,
                    $obat->id_kategori,
                    $obat->dosis . ' ' . $obat->satuan_dosis . ' ' . $obat->bentuk_sediaan,
                    $obat->letak_rak,
                    $obat->batas_stok_min,
                    $obat->total_stok,
                    $status
                ], ';');
            }
            fclose($file);
        };

        // Stream langsung sebagai file unduhan
        return response()->stream($callback, 200, $headers);
    }
}
