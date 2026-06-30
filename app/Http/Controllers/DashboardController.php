<?php

# =====================================================================
# BAGIAN 1: PERSIAPAN (IMPOR KELAS DAN PENGATURAN LOKASI)
# =====================================================================

# Mendefinisikan alamat lokasi folder file ini agar dikenali oleh sistem autoloader Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangkap data dari URL (?search=) atau inputan Form HTML
use Illuminate\Http\Request;
# Mengimpor class Auth (Authentication) untuk mengecek siapa user yang sedang login saat ini
use Illuminate\Support\Facades\Auth;
# Mengimpor class DB (Database/Query Builder) untuk menjalankan perintah SQL mentah (insert, update, dll)
use Illuminate\Support\Facades\DB;
# Mengimpor Model 'Pengguna' sebagai perwakilan tabel 'pengguna' di database
use App\Models\Pengguna;
# Mengimpor Model 'LogAudit' sebagai perwakilan tabel 'log_audit'
use App\Models\LogAudit;
# Mengimpor Model 'Notifikasi' sebagai perwakilan tabel 'notifikasi'
use App\Models\Notifikasi;
# Mengimpor Model 'Pengaturan' sebagai perwakilan tabel 'pengaturan'
use App\Models\Pengaturan;
# Mengimpor class Hash bawaan Laravel untuk mengenkripsi (mengacak) password demi keamanan
use Illuminate\Support\Facades\Hash;
# Mengimpor class Str (String) untuk memanipulasi teks, seperti men-generate teks/kode acak
use Illuminate\Support\Str;
# Mengimpor class Pdf dari library 'dompdf' untuk mengubah tampilan web menjadi file PDF
use Barryvdh\DomPDF\Facade\Pdf;

# Membuat class DashboardController yang mewarisi (extends) fitur-fitur dari Controller utama Laravel
class DashboardController extends Controller
{
    # =====================================================================
    # BAGIAN 2: FUNGSI-FUNGSI KHUSUS ADMINISTRATOR
    # =====================================================================

    # --- FUNGSI HALAMAN UTAMA DASHBOARD ADMIN ---
    public function admin()
    {
        # Menghitung total seluruh baris yang ada di dalam tabel 'pengguna'
        $totalPengguna = Pengguna::count();
        # Menghitung total seluruh baris di tabel 'obat' (Total jenis master obat)
        $totalObat = DB::table('obat')->count();
        # Menghitung total seluruh baris di tabel 'obat_masuk' (Total dokumen faktur)
        $totalMasuk = DB::table('obat_masuk')->count();
        # Menghitung total seluruh baris di tabel 'obat_keluar' (Total dokumen resep/pemusnahan)
        $totalKeluar = DB::table('obat_keluar')->count();

        # Menyiapkan variabel array kosong untuk menampung teks tanggal di sumbu X grafik
        $labelTanggal = [];
        # Menyiapkan variabel array kosong untuk data jumlah barang masuk (Sumbu Y grafik 1)
        $dataMasuk = [];
        # Menyiapkan variabel array kosong untuk data jumlah barang keluar (Sumbu Y grafik 2)
        $dataKeluar = [];

        # Memulai perulangan (loop) hitung mundur dari angka 6 sampai 0 (Mewakili 7 hari ke belakang)
        for ($i = 6; $i >= 0; $i--) {
            # Mengambil waktu hari ini (Carbon::now), dikurangi sebanyak $i hari, lalu diubah formatnya jadi Tahun-Bulan-Tanggal (Y-m-d)
            $tanggal = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');

            # Melakukan hal yang sama, tapi formatnya dipendekkan (Tgl Bln, contoh: 24 Jun) lalu dimasukkan ke dalam array $labelTanggal
            $labelTanggal[] = \Carbon\Carbon::now()->subDays($i)->format('d M');

            # Mencari di tabel 'obat_masuk' di mana tanggal masuknya sama persis dengan $tanggal, lalu hitung ada berapa jumlahnya
            $masuk = DB::table('obat_masuk')->whereDate('tanggal_masuk', $tanggal)->count();
            # Masukkan hasil hitungan tersebut (misal: 5 transaksi) ke dalam array $dataMasuk
            $dataMasuk[] = $masuk;

            # Mencari di tabel 'obat_keluar' di mana tanggal keluarnya sama persis dengan $tanggal, lalu dihitung
            $keluar = DB::table('obat_keluar')->whereDate('tanggal_keluar', $tanggal)->count();
            # Masukkan hasil hitungannya ke dalam array $dataKeluar
            $dataKeluar[] = $keluar;
        }

        # Memanggil file tampilan HTML (admin/dashboard.blade.php)
        return view('admin.dashboard', [
            # Mengirimkan variabel-variabel di atas agar bisa dibaca/ditampilkan oleh kode HTML
            'title' => 'Dashboard Administrator',
            'totalPengguna' => $totalPengguna,
            'totalObat' => $totalObat,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'labelTanggal' => $labelTanggal, # Dikirim untuk label Sumbu X grafik Chart.js
            'dataMasuk' => $dataMasuk,       # Dikirim untuk isi Sumbu Y barang masuk
            'dataKeluar' => $dataKeluar,     # Dikirim untuk isi Sumbu Y barang keluar
        ]);
    }

    # --- FUNGSI DAFTAR MANAJEMEN PENGGUNA ---
    public function manajemenUser(Request $request)
    {
        # Menangkap nilai parameter URL '?search=' jika admin mengetik di kotak pencarian
        $search = $request->query('search');

        # Mengecek apakah variabel $search memiliki isi (tidak kosong)
        if ($search) {
            # Tarik data dari tabel pengguna di mana 'nama_lengkap' mirip (%...%) dengan teks pencarian
            $pengguna = Pengguna::where('nama_lengkap', 'like', '%' . $search . '%')
                # ATAU jika 'username'-nya yang mirip dengan teks pencarian
                ->orWhere('username', 'like', '%' . $search . '%')
                # Eksekusi dan ambil hasilnya (get)
                ->get();
        } else {
            # Jika kotak pencarian kosong, tarik SEMUA data pengguna tanpa terkecuali
            $pengguna = Pengguna::all();
        }

        # Menghitung kembali total pengguna (untuk ditampilkan di atas tabel)
        $totalPengguna = Pengguna::count();

        # Tampilkan halaman manajemen user dan kirimkan data hasil kueri ke HTML
        return view('admin.manajemen-user', [
            'title' => 'Manajemen Pengguna',
            'pengguna' => $pengguna,
            'totalPengguna' => $totalPengguna
        ]);
    }

    # --- FUNGSI MEMBUKA FORM TAMBAH PENGGUNA ---
    public function tambahUser()
    {
        # Hanya merender/menampilkan form HTML kosong untuk diisi
        return view('admin.tambah-user', ['title' => 'Tambah Pengguna Baru']);
    }

    # --- FUNGSI MENYIMPAN DATA PENGGUNA BARU ---
    public function simpanUser(Request $request)
    {
        # Memvalidasi inputan yang masuk dari Form HTML. Jika melanggar, otomatis dikembalikan ke form dengan error
        $request->validate([
            'nama_lengkap' => 'required|string|max:100', # Wajib ada, harus teks, maksimal 100 huruf
            'username'     => 'required|string|max:50|unique:pengguna,username', # Unik berarti username ini belum boleh ada di tabel 'pengguna'
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek', # Harus sesuai dengan 3 pilihan ini (Dropdown)
            'password'     => 'required|min:6', # Minimal panjang sandi 6 karakter
        ]);

        # Mengeksekusi penambahan data (Insert) menggunakan fungsi 'create' pada Model
        Pengguna::create([
            # Gabungkan kata "USR" dengan 7 huruf acak yang dibesarkan hurufnya (strtoupper) untuk dijadikan ID Pengguna
            'id_pengguna'  => 'USR' . strtoupper(Str::random(7)),
            'nama_lengkap' => $request->nama_lengkap, # Ambil dari input kotak nama
            'username'     => $request->username,     # Ambil dari input kotak username
            # Lindungi password menggunakan Hash (enkripsi satu arah) agar tidak bisa dibaca hacker
            'password'     => Hash::make($request->password),
            'peran'        => $request->peran,        # Ambil dari pilihan dropdown
        ]);

        # Mencatat tindakan penambahan ini ke dalam tabel Log Audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna, # Ambil ID aktor/admin yang saat ini sedang login
            'aktivitas'   => "Menambahkan pengguna baru: " . $request->nama_lengkap, # Deskripsi aktivitas
            'alamat_ip'   => $request->ip(), # Rekam IP Address perangkat pengguna
            'status'      => 'Success', # Status sukses
            'created_at'  => now(), # Catat di waktu detik ini
        ]);

        # Lempar admin kembali ke halaman manajemen user (tabel) dengan membawa notifikasi hijau (success)
        return redirect()->route('admin.manajemen-user')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    # --- FUNGSI MEMBUKA FORM EDIT PENGGUNA (Berdasarkan ID) ---
    public function editUser(string $id)
    {
        # Cari data 1 pengguna di tabel berdasarkan Primary Key ($id). Jika tidak ada, munculkan halaman Error 404 (Not Found)
        $user = Pengguna::findOrFail($id);

        # Tampilkan file HTML form edit, dan lempar data diri orang tersebut ($user) agar kotaknya terisi otomatis
        return view('admin.edit-user', [
            'title' => 'Edit Pengguna',
            'user' => $user
        ]);
    }

    # --- FUNGSI MENYIMPAN HASIL EDIT PENGGUNA ---
    public function updateUser(Request $request, string $id)
    {
        # Temukan kembali data lamanya di database
        $user = Pengguna::findOrFail($id);

        # Lakukan validasi ketat
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            # Kunci Unique di sini diberi PENGECUALIAN. Username harus unik, KECUALI untuk dirinya sendiri (id_pengguna = $id) agar dia tidak dianggap menduplikat namanya sendiri.
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $id . ',id_pengguna',
            'peran'        => 'required|in:Administrator,Kepala Apotek,Petugas Apotek',
        ]);

        # Timpa (Update) isi tabel di database dengan data yang baru diketik di form
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'peran'        => $request->peran,
        ]);

        # Catat kelakuan admin mengedit profil ini ke dalam Log
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data profil pengguna: " . $request->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan admin ke tabel dengan pesan berhasil diperbarui
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    # --- FUNGSI MEMBUKA FORM RESET PASSWORD ---
    public function resetPassword(string $id)
    {
        # Temukan data akun mana yang passwordnya akan diubah paksa
        $user = Pengguna::findOrFail($id);

        # Tampilkan form ubah sandi
        return view('admin.reset-password', [
            'title' => 'Reset Password',
            'user' => $user
        ]);
    }

    # --- FUNGSI MENYIMPAN PASSWORD BARU HASIL RESET ---
    public function updatePassword(Request $request, string $id)
    {
        # Temukan data penggunanya terlebih dahulu
        $user = Pengguna::findOrFail($id);

        # Aturan 'confirmed' di Laravel akan otomatis mencocokkan input name="password" dengan name="password_confirmation". Jika tidak kembar, form ditolak.
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        # Update khusus untuk kolom password saja dengan isian yang baru di-Hash
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        # Tulis di riwayat log bahwa admin melakukan tindakan sensitif (Reset Sandi)
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Mereset password pengguna: " . $user->nama_lengkap,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Alihkan kembali ke halaman awal dengan alert sukses
        return redirect()->route('admin.manajemen-user')->with('success', 'Password pengguna berhasil direset!');
    }

    # --- FUNGSI MENGELUARKAN POPUP / HALAMAN PERINGATAN HAPUS ---
    public function konfirmasiHapus(string $id)
    {
        # Temukan data orang yang akan divonis hapus
        $user = Pengguna::findOrFail($id);

        # Munculkan layar yang menanyakan "Apakah Anda Yakin?" beserta rincian namanya
        return view('admin.hapus-user', [
            'title' => 'Konfirmasi Hapus',
            'user' => $user
        ]);
    }

    # --- FUNGSI MENGEKSEKUSI PENGHAPUSAN PERMANEN (DELETE) ---
    public function prosesHapus(string $id, Request $request)
    {
        # Temukan datanya
        $user = Pengguna::findOrFail($id);

        # Selamatkan / simpan sementara nama orang tersebut ke dalam variabel memori, karena sebentar lagi data $user akan hancur
        $namaYangDihapus = $user->nama_lengkap;

        # Hancurkan baris data tersebut dari tabel database (Permanen)
        $user->delete();

        # Tulis nama yang dihapus tadi ke dalam buku rekam jejak menggunakan variabel sementara
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menghapus permanen pengguna: " . $namaYangDihapus,
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembali ke daftar dengan alert bahwa data telah dihapus
        return redirect()->route('admin.manajemen-user')->with('success', 'Data pengguna berhasil dihapus permanen.');
    }

    # --- FUNGSI MENAMPILKAN TABEL REKAM JEJAK (LOG AUDIT) ---
    public function auditLogs(Request $request)
    {
        # Menangkap pilihan admin di kotak combobox HTML (Misal admin hanya ingin melihat log Petugas)
        $role = $request->query('role');

        # Mulai menyusun dasar kueri: Panggil model LogAudit, ikat/gabungkan dengan relasi 'pengguna' agar nama aslinya ikut terbawa, urutkan dari waktu paling baru (descending).
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');

        # Jika combobox Role tadi dipilih...
        if ($role) {
            # Tambahkan syarat filter: Cari di dalam tabel anaknya (relasi 'pengguna') menggunakan 'whereHas'.
            # Ambil log hanya jika 'peran' di tabel penggunanya sama persis dengan $role yang dipilih.
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Tarik semua data (get) hasil saringan tersebut ke dalam bentuk Collection/Array
        $logs = $query->get();

        # Lempar datanya ke HTML
        return view('admin.audit-logs', [
            'title' => 'Log Audit Sistem',
            'logs' => $logs
        ]);
    }

    # --- FUNGSI MENAMPILKAN FORM PENGATURAN UMUM APLIKASI ---
    public function pengaturan()
    {
        # Karena pengaturan global hanya punya 1 baris di tabel, langsung ambil baris pertamanya saja (first)
        $pengaturan = Pengaturan::first();

        # Tampilkan HTML form dan isi dengan data setelan lama
        return view('admin.pengaturan', [
            'title' => 'Pengaturan Sistem',
            'pengaturan' => $pengaturan
        ]);
    }

    # --- FUNGSI MENYIMPAN (UPDATE) PENGATURAN APLIKASI ---
    public function updatePengaturan(Request $request)
    {
        # Ambil kembali baris pertamanya
        $pengaturan = Pengaturan::first();

        # Validasi nama dan alamat wajib diisi berupa teks
        $request->validate([
            'nama_apotek' => 'required|string|max:100',
            'alamat_apotek' => 'required|string',
        ]);

        # Lakukan update (penimpaan) ke tabel
        $pengaturan->update([
            'nama_apotek'         => $request->nama_apotek,
            'alamat_apotek'       => $request->alamat_apotek,
            # Trik untuk form Checkbox: Jika ada/dicentang (has) maka simpan angka 1 (True), jika tidak dicentang maka simpan 0 (False)
            'wajib_password_kuat' => $request->has('wajib_password_kuat') ? 1 : 0,
            'auto_logout'         => $request->has('auto_logout') ? 1 : 0,
            'log_audit_global'    => $request->has('log_audit_global') ? 1 : 0,
        ]);

        # Catat aktivitas sakral mengubah setelan aplikasi ini ke Log Audit
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => 'Memperbarui Pengaturan Sistem Aplikasi',
            'alamat_ip'   => $request->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        # Kembalikan ke halaman sebelumnya dengan alert berhasil (with success)
        return back()->with('success', 'Pengaturan sistem berhasil diperbarui!');
    }

    # --- FUNGSI MENAMPILKAN HALAMAN PROFIL KHUSUS ROLE ADMIN ---
    public function profil()
    {
        # Membuka file 'shared/profil.blade.php' (file profil yang dipakai bersama untuk 3 aktor)
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(), # Ambil identitas admin ini sendiri
            'layout' => 'layouts.admin', # Instruksikan HTML agar membungkus halamannya pakai Sidebar warna/menu Admin
            'actionUrl' => url('/simpan-profil-global') # Arahkan formnya agar mensubmit data ke rute global
        ]);
    }

    # --- FUNGSI MENCETAK LAPORAN LOG AUDIT KE DALAM FORMAT PDF ---
    public function exportPdfAuditLogs(Request $request)
    {
        # Menangkap jika ada filter peran yang sedang diaktifkan di layar (agar yang dicetak sama dengan yang dilihat)
        $role = $request->query('role');

        # Susun ulang pencarian datanya sama persis seperti fungsi auditLogs()
        $query = LogAudit::with('pengguna')->orderBy('created_at', 'desc');

        # Terapkan filternya jika ada
        if ($role) {
            $query->whereHas('pengguna', function ($q) use ($role) {
                $q->where('peran', $role);
            });
        }

        # Dapatkan data akhirnya
        $logs = $query->get();

        # Panggil library DomPDF (Pdf::loadView). Perintahkan library ini untuk me-render/membaca file HTML bernama 'pdf-audit-logs' menjadi wujud kanvas PDF
        $pdf = Pdf::loadView('admin.pdf-audit-logs', [
            'logs' => $logs, # Sertakan data log-nya agar tercetak ke tabel PDF
            'role' => $role
        ]);

        # Paksa peramban (browser) milik pengguna untuk langsung mengunduh file hasil jadi tersebut (bukan menampilkannya)
        return $pdf->download('Laporan_Audit_Log_SIOPAL.pdf');
    }

    # =====================================================================
    # BAGIAN 3: FUNGSI-FUNGSI KHUSUS KEPALA APOTEK
    # =====================================================================

    # --- FUNGSI HALAMAN UTAMA DASHBOARD KEPALA APOTEK ---
    public function kepala()
    {
        # Menghitung berapa dokumen faktur barang masuk yang stempelnya masih 'Draft' (Artinya antre untuk di-ACC Kepala)
        $fakturMenunggu = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->count();

        # Menghitung berapa dokumen obat keluar (khusus yang tujuannya Pemusnahan) dan statusnya masih antre 'Menunggu'
        $pemusnahanMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'Pemusnahan/Rusak')
            ->count();

        # Menghitung jumlah jenis master obat yang sisa stoknya nyaris habis (lebih kecil / sama dengan batas minimalnya)
        $obatKritis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->count();

        # Menentukan patokan 3 Garis Waktu untuk memeriksa Kedaluwarsa (Expired Date)
        $hariIni = \Carbon\Carbon::now(); # Tanggal hari ini
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3); # Tanggal tepat 3 bulan ke depan
        $enamBulan = \Carbon\Carbon::now()->addMonths(6); # Tanggal tepat 6 bulan ke depan

        # Menghitung jumlah kotak batch barang yang Expired Date-nya SUDAH LEWAT hari ini (Kadaluwarsa Parah)
        $kadaluwarsa = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '<', $hariIni)->count();
        # Menghitung batch obat yang rentang Expired Date-nya berada di antara hari ini sampai 3 bulan ke depan (Kritis)
        $kritis = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$hariIni, $tigaBulan])->count();
        # Menghitung batch obat yang rentang Expired Date-nya antara 3-6 bulan lagi (Peringatan Awal)
        $peringatan = DB::table('detail_masuk')->whereBetween('tgl_kadaluwarsa', [$tigaBulan->copy()->addDay(), $enamBulan])->count();
        # Menghitung batch obat yang umurnya masih sangat panjang (Lebih dari 6 bulan)
        $aman = DB::table('detail_masuk')->whereDate('tgl_kadaluwarsa', '>', $enamBulan)->count();

        # Mengambil 5 antrean faktur draf paling awal untuk ditampilkan secara ringkas di kotak (widget) layar dashboard
        $fakturPending = DB::table('obat_masuk')
            ->where('status_verifikasi', 'Draft')
            ->orderBy('tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        # Lempar semua variabel di atas ke file tampilan HTML
        return view('kepala.dashboard', [
            'title' => 'Dashboard Kepala Apotek',
            'fakturMenunggu' => $fakturMenunggu,
            'pemusnahanMenunggu' => $pemusnahanMenunggu,
            'obatKritis' => $obatKritis,
            'fakturPending' => $fakturPending,
            # Satukan 4 metrik status ED ke dalam 1 urutan array agar mudah ditangkap oleh script Chart.js di bagian depan
            'dataDefecta' => [$aman, $peringatan, $kritis, $kadaluwarsa]
        ]);
    }

    # --- FUNGSI HALAMAN MENAMPILKAN DAFTAR ANTRIAN VALIDASI FAKTUR ---
    public function verifikasi()
    {
        # Menghitung total faktur yang berstatus Draft
        $totalMenunggu = DB::table('obat_masuk')->where('status_verifikasi', 'Draft')->count();

        # Membuat variabel patokan tanggal 3 bulan ke depan (Dalam format string Y-m-d)
        $tigaBulan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');

        # Mencari tahu apakah ada Faktur yang "Urgent" (Faktur draf yang tanpa disadari berisi kiriman barang yang ED-nya tinggal <= 3 bulan)
        $urgentCount = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk') # Gabung tabel detail untuk bisa baca tgl ED
            ->where('obat_masuk.status_verifikasi', 'Draft') # Syarat 1: Dokumen masih Draf
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulan) # Syarat 2: Tgl ED <= 3 bulan
            ->distinct('obat_masuk.id_masuk') # Syarat 3: Hilangkan duplikat. Jika 1 faktur punya 5 obat expired, hitung fakturnya sebagai 1 dokumen urgent saja.
            ->count('obat_masuk.id_masuk');

        # Mengambil daftar faktur Draf untuk ditampilkan di tabel HTML
        $fakturList = DB::table('obat_masuk')
            ->select(
                'obat_masuk.*', # Ambil semua kolom dasar faktur
                # DB::raw menyelundupkan 'Sub-Query' SQL. Ini bertugas menghitung cepat berapa total macam/jenis obat di dalam 1 faktur ini
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk) as jumlah_item'),
                # Sub-Query kedua bertugas mengecek apakah di dalam dokumen ini ada minimal 1 barang urgent (Nilainya nanti True/False atau Angka)
                DB::raw('(SELECT COUNT(*) FROM detail_masuk WHERE detail_masuk.id_masuk = obat_masuk.id_masuk AND tgl_kadaluwarsa <= "' . $tigaBulan . '") as ada_urgent')
            )
            ->where('status_verifikasi', 'Draft') # Tarik yang draf saja
            ->orderBy('tanggal_masuk', 'asc') # Diurutkan dari tgl faktur terlama agar yang antre paling awal diurus duluan (FIFO process)
            ->get();

        return view('kepala.verifikasi', [
            'title' => 'Verifikasi Faktur',
            'totalMenunggu' => $totalMenunggu,
            'urgentCount' => $urgentCount,
            'fakturList' => $fakturList
        ]);
    }

    # --- FUNGSI MELIHAT RINCIAN KERANJANG DI DALAM SEBUAH FAKTUR ---
    public function detailVerifikasi(string $id_masuk)
    {
        # Tarik data "Kepala / Wajah" dokumen fakturnya (Nomor faktur, supplier, tgl dll), gabung dengan nama si Petugas pembuat (Join)
        $faktur = DB::table('obat_masuk')
            ->join('pengguna', 'obat_masuk.id_pengguna', '=', 'pengguna.id_pengguna')
            ->select('obat_masuk.*', 'pengguna.nama_lengkap')
            ->where('id_masuk', $id_masuk)
            ->first();

        # Pintu Keamanan: Jika Kepala Apotek mengetik ID faktur ngawur di URL, aplikasi tidak boleh error, melainkan menampilkan peringatan 404
        if (!$faktur) {
            abort(404, 'Dokumen faktur tidak ditemukan di dalam sistem.');
        }

        # Tarik data "Perut / Rincian" obat apa saja yang dimasukkan ke dalam faktur ini
        $detailObat = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.dosis', 'obat.bentuk_sediaan')
            ->where('detail_masuk.id_masuk', $id_masuk)
            ->get();

        # Tampilkan Halaman Rinciannya
        return view('kepala.detail_verifikasi', [
            'title' => 'Detail Verifikasi Faktur',
            'faktur' => $faktur,
            'detailObat' => $detailObat
        ]);
    }

    # --- FUNGSI SAKTI: MENGEKSEKUSI KEPUTUSAN (ACC/TOLAK) ATAS FAKTUR ---
    public function prosesVerifikasi(Request $request, string $id_masuk)
    {
        # Lindungi tombol dari injeksi kode hacker: Nilai form 'status' WAJIB hanya berisi kata "Disetujui" atau "Ditolak"
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        # Simpan nilai ketikan form tersebut ke variabel lokal
        $statusBaru = $request->status;

        # Cek database, pastikan dokumen faktur yang mau di-ACC ini benar-benar ada
        $faktur = DB::table('obat_masuk')->where('id_masuk', $id_masuk)->first();

        # Pintu Keamanan Ganda: Batalkan proses jika dokumennya hilang atau statusnya sudah terlanjur BUKAN 'Draft' (Menghindari Kepala Apotek memencet tombol ACC 2x)
        if (!$faktur || $faktur->status_verifikasi != 'Draft') {
            return redirect()->route('kepala.verifikasi')->with('error', 'Faktur tidak valid atau sudah pernah diproses.');
        }

        # MEMULAI TRANSACTION DB (Sangat Penting):
        # Jika di tengah jalan (misal baris 486) listrik mati, maka seluruh baris kode di dalam blok ini dibatalkan (Rollback). Mencegah stempel sudah Disetujui tapi angka stok belum bertambah.
        DB::transaction(function () use ($id_masuk, $statusBaru, $faktur) {

            # 1. Update/Timpa tulisan "Draft" di dokumen tersebut menjadi "Disetujui" atau "Ditolak"
            DB::table('obat_masuk')
                ->where('id_masuk', $id_masuk)
                ->update(['status_verifikasi' => $statusBaru]);

            # 2. JIKA KEPALA APOTEK MEMILIH "DISETUJUI"...
            if ($statusBaru == 'Disetujui') {
                # Tarik seluruh rincian barang dari dalam kotak (detail_masuk)
                $detailFaktur = DB::table('detail_masuk')->where('id_masuk', $id_masuk)->get();

                # Lakukan perulangan satu per satu item obat...
                foreach ($detailFaktur as $item) {
                    # Tembak ke tabel 'obat' (Rak Master), cari yang ID-nya sama, lalu TAMBAHKAN nilai kolom 'total_stok' dengan porsi angka dari dokumen ($item->jumlah_masuk).
                    # 'increment' digunakan agar angka tidak ditimpa total, tapi dijumlahkan (Stok lama + Stok baru).
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->increment('total_stok', $item->jumlah_masuk);
                }
            }

            # 3. Meracik kata kerja log ("Menyetujui" atau "Menolak") sesuai keputusan, lalu catat di Log Audit
            $kataKerja = ($statusBaru == 'Disetujui') ? 'Menyetujui' : 'Menolak';
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => $kataKerja . " faktur masuk dengan No: " . $faktur->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);

            # 4. Menyusun pesan balasan (Notifikasi) untuk dikirim ke HP/Akun si Petugas
            $judulNotif = ($statusBaru == 'Disetujui') ? 'Faktur Disetujui' : 'Faktur Ditolak';
            $pesanNotif = ($statusBaru == 'Disetujui')
                ? 'Faktur <strong>' . $faktur->no_faktur . '</strong> telah diverifikasi. Stok otomatis bertambah ke dalam inventaris.'
                : 'Pengajuan faktur <strong>' . $faktur->no_faktur . '</strong> dikembalikan. Silakan periksa kembali kecocokan fisik barang dengan nota cetak.';

            # Masukkan (Insert) surat balasan tersebut ke Kotak Pesan Notifikasi (Role: petugas)
            DB::table('notifikasi')->insert([
                'untuk_role'  => 'petugas',
                'tipe'        => 'Faktur',
                'judul'       => $judulNotif,
                'pesan'       => $pesanNotif,
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);
        });
        # (Batas akhir blok aman Transaction)

        # Buat kalimat alert sukses yang akan muncul melayang di layar Kepala Apotek
        $pesanNotif = ($statusBaru == 'Disetujui')
            ? 'Faktur disetujui! Stok obat berhasil ditambahkan ke dalam inventaris.'
            : 'Faktur telah ditolak dan diarsipkan.';

        # Kembalikan Kepala Apotek ke halaman tabel Verifikasi
        return redirect()->route('kepala.verifikasi')->with('success', $pesanNotif);
    }

    # --- FUNGSI MELIHAT TABEL PERMOHONAN PEMUSNAHAN BARANG RUSAK ---
    public function pemusnahan(Request $request)
    {
        # Menangkap parameter '?tab=' di URL, jika kosong, anggap dia mau melihat tab 'Menunggu'
        $tab = $request->query('tab', 'Menunggu');

        # Menghitung ada berapa pengajuan pemusnahan yang statusnya masih 'Menunggu' (Untuk ikon angka merah)
        $totalMenunggu = DB::table('obat_keluar')
            ->where('status_otorisasi', 'Menunggu')
            ->where('tujuan_pengeluaran', 'like', '%Pemusnahan%')
            ->count();

        # Menyusun kueri pencarian berlapis untuk menggabungkan Data Dokumen, Data Petugas, dan Master Obatnya
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
            # Wajib saring: HANYA tampilkan dokumen yang tulisan tujuannya mengandung kata 'Pemusnahan'
            ->where('obat_keluar.tujuan_pengeluaran', 'like', '%Pemusnahan%');

        # Menyaring data berdasarkan Tab HTML mana yang sedang di-klik Kepala
        if ($tab == 'Menunggu') {
            $query->where('obat_keluar.status_otorisasi', 'Menunggu');
        } elseif ($tab == 'Disetujui') {
            $query->where('obat_keluar.status_otorisasi', 'Disetujui');
        } elseif ($tab == 'Ditolak') {
            $query->where('obat_keluar.status_otorisasi', 'Ditolak');
        }

        # Urutkan kueri dari tanggal teranyar, lalu eksekusi tarik data (get)
        $query->orderBy('obat_keluar.tanggal_keluar', 'desc');
        $pengajuanList = $query->get();

        # Tampilkan halamannya
        return view('kepala.pemusnahan', [
            'title' => 'Otorisasi Pemusnahan',
            'totalMenunggu' => $totalMenunggu,
            'pengajuanList' => $pengajuanList,
            'activeTab' => $tab # Oper variabel ini agar CSS HTML bisa memberi warna aktif pada tombol tab
        ]);
    }

    # --- FUNGSI EKSEKUSI TANDA TANGAN (ACC/TOLAK) ATAS PEMUSNAHAN BARANG ---
    public function prosesPemusnahan(Request $request, string $id_keluar)
    {
        # Pintu pengamanan input form
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak'
        ]);

        $statusBaru = $request->status;

        # Pastikan dokumen permohonannya sah ada di database
        $pengajuan = DB::table('obat_keluar')->where('id_keluar', $id_keluar)->first();

        # Jika dicegat di tengah jalan (dokumen sudah ditarik/hilang/sudah ttd)
        if (!$pengajuan || $pengajuan->status_otorisasi != 'Menunggu') {
            return redirect()->route('kepala.pemusnahan')->with('error', 'Data tidak valid.');
        }

        # BUNGKUS TRANSAKSI LAGI (Untuk Amankan Pemotongan Stok)
        DB::transaction(function () use ($id_keluar, $statusBaru, $pengajuan) {

            # 1. Update stempel surat permohonannya
            DB::table('obat_keluar')
                ->where('id_keluar', $id_keluar)
                ->update(['status_otorisasi' => $statusBaru]);

            # 2. JIKA DI-ACC (Barang Resmi Dibakar/Dibuang), MAKA STOK HARUS DIKURANGI DARI KOMPUTER
            if ($statusBaru == 'Disetujui') {
                # Tarik daftar obat apa saja yang minta dibuang di dokumen tsb
                $detailKeluar = DB::table('detail_keluar')->where('id_keluar', $id_keluar)->get();

                # Looping per baris...
                foreach ($detailKeluar as $item) {
                    # Tembak master obat, lalu POTONG ('decrement') angka stoknya sesuai jumlah yang dibakar
                    DB::table('obat')
                        ->where('id_obat', $item->id_obat)
                        ->decrement('total_stok', $item->jumlah_keluar);
                }
            }

            # 3. Tulis aktivitas ekstrem ini di buku Log Audit
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => ($statusBaru == 'Disetujui' ? 'Menyetujui' : 'Menolak') . " pemusnahan obat ID: " . $id_keluar,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # Atur teks pesan berdasar keputusan
        $pesan = $statusBaru == 'Disetujui' ? 'Pemusnahan disetujui, stok telah dikurangi.' : 'Pengajuan pemusnahan ditolak.';
        return redirect()->route('kepala.pemusnahan')->with('success', $pesan);
    }

    # --- FUNGSI UNTUK MELIHAT TABEL DATA LAPORAN INVENTARIS ---
    public function laporan(Request $request)
    {
        # Menangkap parameter filter Dropdown Kategori Obat (Bawaannya 'semua' data)
        $kategoriPilihan = $request->query('kategori', 'semua');

        # Metrik KPI Atas: Total Item Barang Keseluruhan
        $totalItem = DB::table('obat')->count();

        # Metrik KPI Atas: Total Barang yang Stoknya Sekarat
        $stokKritis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # Metrik KPI Atas: Obat yang punya tumor / kedaluwarsa
        # Gunakan distinct agar jika ada obat A yg mati 3 batch, tetap dihitung 1 masalah obat
        $expiredCount = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<', now())
            ->distinct('id_obat')
            ->count('id_obat');

        # Ambil daftar master kelompok kategori (Bebas, dsb) untuk bahan Combobox HTML
        $kategoriList = DB::table('kategori')->get();

        # Racik pencarian master obat
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Jika Kepala memilih kategori khusus, tambahkan saringan kueri
        if ($kategoriPilihan != 'semua') {
            $query->where('obat.id_kategori', $kategoriPilihan);
        }

        # Urutkan secara Abjad A-Z.
        # PENTING: Gunakan 'paginate(10)' untuk membelah data menjadi buku 10 baris per halaman. (Mempercepat loading jika tabel punya 10.000 obat)
        $obatList = $query->orderBy('obat.nama_obat', 'asc')->paginate(10);

        # Tampilkan halamannya
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

    # --- FUNGSI MENGAKSES PROFIL LAYOUT KEPALA APOTEK ---
    public function profilKepala()
    {
        # Sama persis seperti punya Admin, hanya melempar variabel pembungkus CSS (layout) yang berbeda
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.kepala', # Mengaktifkan sisi kiri berwarna khas Kepala Apotek
            'actionUrl' => url('/simpan-profil-global')
        ]);
    }

    # =====================================================================
    # BAGIAN 4: FUNGSI-FUNGSI KHUSUS PETUGAS APOTEK (GUDANG / KASIR)
    # =====================================================================

    # --- FUNGSI DASHBOARD UTAMA PETUGAS ---
    public function petugas()
    {
        # Metrik dasar
        $totalObat = DB::table('obat')->count();
        $stokMenipis = DB::table('obat')->whereRaw('total_stok <= batas_stok_min')->count();

        # Metrik jumlah batch yang batas ajalnya <= 6 Bulan
        $enamBulanKeDepan = \Carbon\Carbon::now()->addMonths(6);
        $akanKedaluwarsa = DB::table('detail_masuk')
            ->whereDate('tgl_kadaluwarsa', '<=', $enamBulanKeDepan)
            ->count();

        # --- LOGIKA KEREN: MERAKIT TABEL TIMELINE (AKTIVITAS GABUNGAN) ---

        # 1. Mengambil Top 5 Transaksi Barang Masuk
        $masuk = DB::table('obat_masuk')
            ->join('detail_masuk', 'obat_masuk.id_masuk', '=', 'detail_masuk.id_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            # Samakan nama kolom hasil keluarannya pakai (as) agar formatnya seragam
            ->select('obat_masuk.tanggal_masuk as tanggal', 'obat.nama_obat', 'detail_masuk.jumlah_masuk as jumlah', DB::raw("'Masuk' as tipe"))
            ->where('obat_masuk.status_verifikasi', 'Disetujui')
            ->orderBy('obat_masuk.tanggal_masuk', 'desc')
            ->limit(5)
            ->get();

        # 2. Mengambil Top 5 Transaksi Barang Keluar
        $keluar = DB::table('obat_keluar')
            ->join('detail_keluar', 'obat_keluar.id_keluar', '=', 'detail_keluar.id_keluar')
            ->join('obat', 'detail_keluar.id_obat', '=', 'obat.id_obat')
            ->select('obat_keluar.tanggal_keluar as tanggal', 'obat.nama_obat', 'detail_keluar.jumlah_keluar as jumlah', DB::raw("'Keluar' as tipe"))
            ->where('obat_keluar.status_otorisasi', 'Disetujui')
            ->orderBy('obat_keluar.tanggal_keluar', 'desc')
            ->limit(5)
            ->get();

        # 3. Mengambil Top 5 Catatan Log Khusus "Stok Opname" (Pembetulan Stok Manual)
        $opname = DB::table('log_audit')
            ->select('created_at as tanggal', 'aktivitas')
            ->where('aktivitas', 'like', 'Stok Opname: Sinkronisasi%')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            # Menerapkan fungsi map() untuk membongkar setiap baris teks dari log
            ->map(function ($item) {
                # Menggunakan mesin pencari Regex untuk mengupas teks:
                # Menelan apa saja setelah kata Sinkronisasi (.*?), dan menelan selisih angkanya ([-\d]+)
                preg_match('/Sinkronisasi (.*?) dari \d+ menjadi \d+ \(Selisih: ([-\d]+)\)/', $item->aktivitas, $matches);

                # Tangkap potongan teksnya ke variabel
                $nama_obat = $matches[1] ?? 'Penyesuaian Stok (Opname)';
                $selisih = (int) ($matches[2] ?? 0);

                # Cetak objek baru agar format susunannya 100% klop dengan hasil array Masuk dan Keluar tadi
                return (object) [
                    'tanggal'   => $item->tanggal,
                    'nama_obat' => $nama_obat,
                    'jumlah'    => abs($selisih), # Fungsi abs (Absolute) membuang tanda minus pada angka selisih
                    'tipe'      => $selisih > 0 ? 'Opname (+)' : 'Opname (-)',
                ];
            });

        # 4. GABUNGKAN (Concat) ketiga array tersebut menjadi 1 antrean panjang (15 baris),
        # lalu Sortir Ulang dari jam ter-update, terakhir potong/ambil cukup 5 yang teratas (take(5))
        $aktivitasTerbaru = $masuk->concat($keluar)->concat($opname)->sortByDesc('tanggal')->take(5);

        return view('petugas.dashboard', [
            'title' => 'Dashboard Operasional',
            'totalObat' => $totalObat,
            'stokMenipis' => $stokMenipis,
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'aktivitasTerbaru' => $aktivitasTerbaru
        ]);
    }

    # --- FUNGSI MENAMPILKAN TABEL DAFTAR BARANG YANG KOSONG ---
    public function stokMenipis()
    {
        # Tarik data obat yang nyawanya / stoknya menipis
        $stokMenipis = DB::table('obat')
            ->whereRaw('total_stok <= batas_stok_min')
            ->orderBy('total_stok', 'asc') # Urutkan dari yang sisa stoknya paling miris (0/Kosong) di atas
            ->get();

        return view('petugas.stok-menipis', [
            'title' => 'Peringatan Stok Menipis',
            'stokMenipis' => $stokMenipis,
        ]);
    }

    # --- FUNGSI MENAMPILKAN TABEL BATCH BARANG EXPIRED ---
    public function obatKedaluwarsa()
    {
        # Tentukan Garis Batas Peringatan: 3 Bulan
        $tigaBulanKeDepan = \Carbon\Carbon::now()->addMonths(3)->format('Y-m-d');
        # Variabel pembanding warna di HTML
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');

        # Tarik rincian dus/faktur yang kadaluwarsanya tinggal dalam hitungan 3 bulan atau kurang
        $akanKedaluwarsa = DB::table('detail_masuk')
            ->join('obat', 'detail_masuk.id_obat', '=', 'obat.id_obat')
            ->select('detail_masuk.*', 'obat.nama_obat', 'obat.satuan_dosis', 'obat.bentuk_sediaan')
            ->whereDate('detail_masuk.tgl_kadaluwarsa', '<=', $tigaBulanKeDepan)
            ->orderBy('detail_masuk.tgl_kadaluwarsa', 'asc') # Yang paling mendesak busuk ditaruh di puncak tabel
            ->get();

        return view('petugas.obat-kedaluwarsa', [
            'title' => 'Peringatan Obat Kedaluwarsa',
            'akanKedaluwarsa' => $akanKedaluwarsa,
            'hariIni' => $hariIni
        ]);
    }

    # --- FUNGSI MEMBUKA BUKU KATALOG INDUK (MASTER OBAT) ---
    public function katalogObat(Request $request)
    {
        # Tangkap ketikan di kotak pencarian dan pilihan combobox kategori
        $search = $request->query('search');
        $kategoriId = $request->query('kategori');

        # Susun relasi kueri
        $query = DB::table('obat')
            ->join('kategori', 'obat.id_kategori', '=', 'kategori.id_kategori')
            ->select('obat.*', 'kategori.nama_kategori');

        # Terapkan Saringan Teks (Jika ada)
        if ($search) {
            # Dibungkus function(q) agar klausa 'OR' tidak membocorkan saringan kategori di luar kurung
            $query->where(function ($q) use ($search) {
                $q->where('obat.nama_obat', 'like', '%' . $search . '%')
                    ->orWhere('obat.id_obat', 'like', '%' . $search . '%');
            });
        }

        # Terapkan Saringan Kategori (Jika ada)
        if ($kategoriId) {
            $query->where('obat.id_kategori', $kategoriId);
        }

        # Paginate untuk efisiensi RAM, pecah per 10 baris obat per halaman
        $obatList = $query->orderBy('obat.nama_obat', 'asc')->paginate(10);

        # Lempar juga master Kategori untuk daftar pilihan Combobox HTML
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.katalog', [
            'title' => 'Inventaris & Katalog Obat',
            'obatList' => $obatList,
            'kategoriList' => $kategoriList,
            'search' => $search,
            'kategoriPilihan' => $kategoriId
        ]);
    }

    # --- FUNGSI MEMBUKA KERTAS KOSONG FORM FAKTUR DATANG ---
    public function obatMasuk()
    {
        # Ambil daftar obat untuk pilihan barang yang mau diinput
        $obatList = DB::table('obat')->orderBy('nama_obat', 'asc')->get();

        return view('petugas.obat-masuk', [
            'title' => 'Catat Obat Masuk',
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI MENCATAT / MENYIMPAN FAKTUR DATANG KE DATABASE ---
    public function simpanObatMasuk(Request $request)
    {
        # Validasi Ketat. Array 'items' (daftar obat di keranjang HTML) WAJIB ada minimal 1 baris
        $request->validate([
            'no_faktur'     => 'required|string|max:100',
            'nama_supplier' => 'required|string|max:100',
            'tanggal_masuk' => 'required|date',
            'items'         => 'required|array|min:1',
        ]);

        # GUNAKAN TRANSAKSI: Karena kita akan meng-insert tabel Obat Masuk, dan banyak baris Detail Masuk, serta Notifikasi sekaligus!
        DB::transaction(function () use ($request) {

            # --- 1. MEMBUAT ID DOKUMEN FAKTUR SECARA CERDAS (AUTO NUMBERING) ---
            # Mengintip ID Faktur (INVM-...) terakhir di database
            $lastFaktur = DB::table('obat_masuk')->orderBy('id_masuk', 'desc')->first();
            # Default awal jika database masih 100% kosong
            $newIdMasuk = 'INVM-001';

            # Jika data terakhir ada isinya (misal: "INVM-045")
            if ($lastFaktur) {
                # Buang teks depannya, tarik angkanya (45).
                $lastNumber = (int) substr($lastFaktur->id_masuk, 5);
                # Jumlahkan 1 (46), tambahkan bantalan nol di kiri agar jadi "046", lalu rakit kembali (INVM-046)
                $newIdMasuk = 'INVM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # --- 2. SIMPAN SAMPUL DOKUMENNYA ---
            DB::table('obat_masuk')->insert([
                'id_masuk'          => $newIdMasuk, # Pakai ID baru yang dirakit
                'no_faktur'         => $request->no_faktur,
                'nama_supplier'     => $request->nama_supplier,
                'tanggal_masuk'     => $request->tanggal_masuk,
                'id_pengguna'       => Auth::user()->id_pengguna, # ID petugas pencatat
                'status_verifikasi' => 'Draft' # Paksa ke status digantung (Belum sah, nunggu Boss)
            ]);

            # --- 3. SIMPAN USUS / ISI KERANJANG BARANGNYA SECARA MASSAL ---
            $detailData = []; # Siapkan wadah (Array) raksasa

            # Looping per satu baris yang disubmit oleh JavaScript HTML
            foreach ($request->items as $item) {
                # Cemplungkan semua data ke array raksasa
                $detailData[] = [
                    'id_masuk'        => $newIdMasuk, # Kunci pengait/Relasi ke faktur kepala
                    'id_obat'         => $item['id_obat'],
                    'jumlah_masuk'    => $item['jumlah_masuk'],
                    'tgl_kadaluwarsa' => $item['tgl_kadaluwarsa'],
                    'nomor_batch'     => $item['nomor_batch'],
                ];
            }
            # Guyur semua isi array raksasa tadi ke database (Batch Insert: Jauh lebih ringan daripada Insert Loop berkali-kali)
            DB::table('detail_masuk')->insert($detailData);

            # --- 4. KIRIM EMAIL/NOTIFIKASI (SISTEM ALARM DALAM) KEPADA KEPALA APOTEK ---
            DB::table('notifikasi')->insert([
                'untuk_role'  => 'kepala', # Ditembakkan khusus untuk telinga (Role) Kepala Apotek
                'tipe'        => 'Faktur',
                'judul'       => 'Verifikasi Faktur Baru',
                'pesan'       => 'Terdapat draf faktur masuk <strong>' . $request->no_faktur . '</strong> yang menunggu persetujuan Anda.',
                'status_baca' => 'Belum',
                'created_at'  => now(),
            ]);

            # --- 5. TULIS DI BUKU LOG AUDIT ---
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menginput draf faktur masuk baru No: " . $request->no_faktur,
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # Kembalikan senyum ke petugas dengan pesan hijau di layar
        return back()->with('success', 'Faktur berhasil disimpan sebagai Draf dan telah dikirim ke Kepala Apotek untuk diverifikasi!');
    }

    # --- FUNGSI MEMBUKA FORM POTONG STOK / OBAT KELUAR ---
    public function obatKeluar()
    {
        # LOGIKA CERDAS SISTEM FEFO (First Expired First Out)
        $obatList = DB::table('obat')
            # Gunakan Sub-Query untuk menyelam ke tabel 'masuk', mencari batch dus dengan tanggal EXPIRED (Ajal) paling dekat/tua,
            # lalu mengambil 1 nama batch tersebut (LIMIT 1) untuk disodorkan sebagai nilai variabel 'batch_rekomendasi' ke petugas
            ->select('obat.*', DB::raw('(SELECT nomor_batch FROM detail_masuk WHERE detail_masuk.id_obat = obat.id_obat ORDER BY tgl_kadaluwarsa ASC LIMIT 1) as batch_rekomendasi'))
            # Tentu saja jangan tampilkan obat yang stoknya 0, karena tidak bisa dikeluarkan
            ->where('total_stok', '>', 0)
            ->orderBy('nama_obat', 'asc')
            ->get();

        return view('petugas.obat-keluar', [
            'title' => 'Catat Obat Keluar',
            'obatList' => $obatList
        ]);
    }

    # --- FUNGSI MENGEKSEKUSI PENGELUARAN OBAT ---
    public function simpanObatKeluar(Request $request)
    {
        # Validasi struktur form keluar
        $request->validate([
            'tujuan_pengeluaran' => 'required|string|max:150',
            'referensi'          => 'nullable|string|max:100', # Referensi (Nomor Resep) boleh kosong (Nullable)
            'tanggal_keluar'     => 'required|date',
            'items'              => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {
            # Buat Tiket ID (Contoh: OUTM-008) persis dengan logika form masuk
            $lastFaktur = DB::table('obat_keluar')->orderBy('id_keluar', 'desc')->first();
            $newIdKeluar = 'OUTM-001';
            if ($lastFaktur) {
                $lastNumber = (int) substr($lastFaktur->id_keluar, 5);
                $newIdKeluar = 'OUTM-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            # Simpan kepala dokumen keluar
            DB::table('obat_keluar')->insert([
                'id_keluar'          => $newIdKeluar,
                'tanggal_keluar'     => $request->tanggal_keluar,
                'tujuan_pengeluaran' => $request->tujuan_pengeluaran,
                'id_pengguna'        => Auth::user()->id_pengguna,
                # PENTING (KUNCI OTOMATISASI):
                # Deteksi kata di kolom tujuan (Tebus Resep vs Pemusnahan)
                # Jika tujuannya Pemusnahan, maka paksa Status "Menunggu" (Tertahan Ttd Kepala)
                # Jika tujuannya sekadar ngelayanin resep pasien, stempel "Disetujui" (Langsung lewat, karena pasien butuh obat detik ini juga)
                'status_otorisasi'   => str_contains(strtolower($request->tujuan_pengeluaran), 'musnah') ? 'Menunggu' : 'Disetujui',
            ]);

            # Simpan isi rincian yang dibuang/diambil
            $detailData = [];
            foreach ($request->items as $item) {
                $detailData[] = [
                    'id_keluar'     => $newIdKeluar,
                    'id_obat'       => $item['id_obat'],
                    'jumlah_keluar' => $item['jumlah_keluar'],
                ];

                # JIKA KELUARNYA SEBAGAI RESEP PASIEN NORMAL (BUKAN PEMUSNAHAN BARANG RUSAK)
                if (!str_contains(strtolower($request->tujuan_pengeluaran), 'musnah')) {
                    # POTONG STOK DI TABEL MASTER SAAT INI JUGA TANPA MENUNGGU SIAPAPUN! (Decrement)
                    DB::table('obat')
                        ->where('id_obat', $item['id_obat'])
                        ->decrement('total_stok', $item['jumlah_keluar']);
                }
            }
            # Simpan detail array
            DB::table('detail_keluar')->insert($detailData);

            # Jika ini transkasi Pemusnahan, petugas berhak mengirim notifikasi panggil Boss (Kepala)
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

            # Catat tindakan petugas ke log
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Mencatat obat keluar No: " . $newIdKeluar . " (" . $request->tujuan_pengeluaran . ")",
                'alamat_ip'   => request()->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);
        });

        # Alihkan tampilan
        return redirect()->route('petugas.keluar')->with('success', 'Transaksi pengeluaran obat berhasil dicatat!');
    }

    # --- FUNGSI HALAMAN FORM PEMBETULAN STOK OPNAME (MATA FISIK) ---
    public function stokOpname(Request $request)
    {
        # Menangkap parameter rak (Jika petugas hanya ingin mencetak/menghitung Rak A saja)
        $rakPilihan = $request->query('rak');

        # Mengambil daftar kolom 'letak_rak' yang isinya unik (distinct) untuk mengisi Combobox Dropdown Filter
        $rakList = DB::table('obat')->select('letak_rak')->whereNotNull('letak_rak')->where('letak_rak', '!=', '')->distinct()->pluck('letak_rak');

        # Susun kueri panggil obat
        $query = DB::table('obat')->orderBy('nama_obat', 'asc');

        # Aktifkan penyaring jika petugas memilih dropdown
        if ($rakPilihan) {
            $query->where('letak_rak', $rakPilihan);
        }

        # Eksekusi (Tarik semua obat untuk disajikan di tabel kertas input panjang)
        $obatList = $query->get();

        return view('petugas.stok-opname', [
            'title' => 'Audit Stok Opname',
            'obatList' => $obatList,
            'rakList' => $rakList,
            'rakPilihan' => $rakPilihan
        ]);
    }

    # --- FUNGSI PENJINAK SELISIH (UPDATE STOK OPNAME MANUAL) ---
    public function simpanStokOpname(Request $request)
    {
        # Cek jangan sampai form ditarik kosong
        $request->validate([
            'items' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            # Bikin indikator variabel penanda apakah petugas melakukan amandemen sekecil apapun?
            $jumlahPerubahan = 0;

            # Membedah 1 per 1 obat yang dikirim form
            foreach ($request->items as $id_obat => $data) {

                # SANGAT PENTING: Lompati obat ini (continue iterasi), apabila kotak teks "Stok Fisik"-nya dibiarkan kosong
                # (Ini bertujuan agar Petugas tidak dipaksa mengisi ulang stok ribuan barang jika fisik dan komputernya sudah cocok)
                if (!isset($data['stok_fisik']) || $data['stok_fisik'] === '') {
                    continue;
                }

                # Tangkap angka ketikan matanya (Paksa jadi Integer agar aman)
                $stokFisik = (int) $data['stok_fisik'];

                # Cek database, intip memori lama si komputer
                $obat = DB::table('obat')->where('id_obat', $id_obat)->first();

                # PENGHAKIMAN: Jika ketemu, DAN TERNYATA angka mata (Fisik) BEDA dengan memori (total_stok)
                if ($obat && $obat->total_stok != $stokFisik) {

                    # 1. Hitung kerugian/selisihnya
                    $selisih = $stokFisik - $obat->total_stok;
                    # Ambil teks keterangannya (Misal: "Hilang dicuri tikus")
                    $keterangan = $data['keterangan'] ?? 'Tanpa keterangan';

                    # 2. EKSEKUSI PEMAKSAAN: Update memori komputer (total_stok) agar wajib sama persis dengan angka fisik
                    DB::table('obat')->where('id_obat', $id_obat)->update([
                        'total_stok' => $stokFisik
                    ]);

                    # 3. Laporkan/catat tragedi amandemen ini ke meja Log Audit (Agar Bos tahu barang mana yang angkanya sering dimanipulasi/hilang)
                    LogAudit::create([
                        'id_pengguna' => Auth::user()->id_pengguna,
                        'aktivitas'   => "Stok Opname: Sinkronisasi " . $obat->nama_obat . " dari " . $obat->total_stok . " menjadi " . $stokFisik . " (Selisih: " . $selisih . "). Ket: " . $keterangan,
                        'alamat_ip'   => request()->ip(),
                        'status'      => 'Success',
                        'created_at'  => now(),
                    ]);

                    # Tambah hitungan tanda bukti perubahan
                    $jumlahPerubahan++;
                }
            }

            # Sesudah muter me-looping form, jika TERNYATA nihil perubahan (0)
            if ($jumlahPerubahan == 0) {
                # Pasang notif flash hijau "Tidak Ada Selisih"
                session()->flash('info', 'Tidak ada selisih yang ditemukan. Stok sistem sudah sesuai dengan fisik.');
            }
        });

        # Belokkan laju URL: Jika tadi ada 'info' nihil di atas, pulangkan dia ke beranda dashboard saja
        if (session()->has('info')) {
            return redirect()->route('petugas.dashboard');
        }

        # Jika ada amandemen berdarah, kembalikan ke halaman opname dengan senyum sukses normal
        return redirect()->route('petugas.opname')->with('success', 'Audit Stok Opname selesai! Data stok telah disinkronkan dengan fisik.');
    }

    # --- FUNGSI MENGEDIT PROFIL PRIBADI ROLE PETUGAS ---
    public function profilPetugas()
    {
        # Mirip seperti role lainnya, tapi beri cap 'layouts.petugas' ke HTML
        return view('shared.profil', [
            'title' => 'Profil Saya',
            'user' => Auth::user(),
            'layout' => 'layouts.petugas',
            'actionUrl' => url('/simpan-profil-global')
        ]);
    }

    # --- FUNGSI MEMBUKA HALAMAN PENDAFTARAN BUKU KATALOG OBAT BARU ---
    public function tambahObat()
    {
        # Tarik master label/kategori obat
        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.tambah-obat', [
            'title' => 'Tambah Obat Baru',
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI MENYIMPAN MASTER KATALOG OBAT ---
    public function simpanObat(Request $request)
    {
        # Validasi batas minimal dll (Pastikan dosis dll tidak diketik nyeleneh)
        $request->validate([
            'nama_obat'      => 'required|string|max:100',
            'id_kategori'    => 'required|string',
            'dosis'          => 'required|numeric',
            'satuan_dosis'   => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak'      => 'nullable|string|max:50', # Boleh kosong kalau rak-nya belum jadi (nullable)
            'batas_stok_min' => 'required|integer|min:0', # Gembok batas alarm
        ]);

        # Teknik cincang ID (OBT001) yang sama
        $lastObat = DB::table('obat')->orderBy('id_obat', 'desc')->first();
        $newId = 'OBT001';
        if ($lastObat) {
            $lastNumber = (int) substr($lastObat->id_obat, 3);
            $newId = 'OBT' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        # Simpan datanya ke tabel Induk 'obat'
        DB::table('obat')->insert([
            'id_obat'        => $newId,
            'id_kategori'    => $request->id_kategori,
            'nama_obat'      => $request->nama_obat,
            'dosis'          => $request->dosis,
            'satuan_dosis'   => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak'      => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
            # ATURAN ABSOLUT: Obat yang baru daftar lahirnya, PASTI berstok 0 di dalam sistem.
            # Angka ini hanya boleh bertambah lewat Surat Faktur Barang Datang agar tercatat rapi
            'total_stok'     => 0,
        ]);

        # Catat ke log
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Menambahkan master obat baru: " . $request->nama_obat,
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('petugas.obat')->with('success', 'Obat baru berhasil ditambahkan ke katalog!');
    }

    # --- FUNGSI MENGAMBIL DATA KATALOG LAMA UNTUK DI EDIT ---
    public function editObat(string $id)
    {
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        # Usir ke beranda jika obatnya gaib
        if (!$obat) {
            return redirect()->route('petugas.obat')->withErrors('Data obat tidak ditemukan.');
        }

        $kategoriList = DB::table('kategori')->orderBy('nama_kategori', 'asc')->get();

        return view('petugas.edit-obat', [
            'title' => 'Edit Data Obat',
            'obat' => $obat,
            'kategoriList' => $kategoriList
        ]);
    }

    # --- FUNGSI UPDATE DATA MASTER OBAT ---
    public function updateObat(Request $request, string $id)
    {
        # Validasi kembali
        $request->validate([
            'nama_obat'      => 'required|string|max:100',
            'id_kategori'    => 'required|string',
            'dosis'          => 'required|numeric',
            'satuan_dosis'   => 'required|string',
            'bentuk_sediaan' => 'required|string',
            'letak_rak'      => 'nullable|string|max:50',
            'batas_stok_min' => 'required|integer|min:0',
        ]);

        # Timpa data obatnya. (Perhatikan: total_stok tidak diubah sama sekali di layar Edit Katalog ini)
        DB::table('obat')->where('id_obat', $id)->update([
            'id_kategori'    => $request->id_kategori,
            'nama_obat'      => $request->nama_obat,
            'dosis'          => $request->dosis,
            'satuan_dosis'   => $request->satuan_dosis,
            'bentuk_sediaan' => $request->bentuk_sediaan,
            'letak_rak'      => $request->letak_rak,
            'batas_stok_min' => $request->batas_stok_min,
        ]);

        # Catat
        LogAudit::create([
            'id_pengguna' => Auth::user()->id_pengguna,
            'aktivitas'   => "Memperbarui data master obat: " . $request->nama_obat . " (ID: " . $id . ")",
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return redirect()->route('petugas.obat')->with('success', 'Data obat berhasil diperbarui!');
    }

    # --- FUNGSI MENGHAPUS / MEMUSNAHKAN NAMA KATALOG OBAT ---
    public function hapusObat(string $id, Request $request)
    {
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        # Jika sudah tidak ada
        if (!$obat) {
            return back()->with('error', 'Gagal: Data obat tidak ditemukan di database.');
        }

        # Simpan string nama aslinya demi isi tabel Log
        $namaObat = $obat->nama_obat;

        # MELINDUNGI SCRIPT DARI ERROR FATAL DATABASE MYSQL (TRY & CATCH)
        try {
            # Lakukan pemusnahan baris targetnya
            DB::table('obat')->where('id_obat', $id)->delete();

            # Jika berhasil, tulis log
            LogAudit::create([
                'id_pengguna' => Auth::user()->id_pengguna,
                'aktivitas'   => "Menghapus master obat dari katalog: " . $namaObat,
                'alamat_ip'   => $request->ip(),
                'status'      => 'Success',
                'created_at'  => now(),
            ]);

            return back()->with('success', 'Data obat "' . $namaObat . '" berhasil dihapus dari katalog!');

            # TANGKAP SERANGAN BALIK DARI DATABASE
            # (Database akan memberontak jika kita memaksa menghapus Induk Obat yang ID-nya ternyata sedang nangkring sebagai tamu (Foreign Key) di dokumen Faktur Masuk/Keluar bulan lalu)
        } catch (\Illuminate\Database\QueryException $e) {
            # Jinakkan errornya jadi pesan manis merah agar sistem tidak hang (Blue Screen)
            return back()->with('error', 'Gagal: Obat "' . $namaObat . '" tidak bisa dihapus karena memiliki riwayat transaksi di sistem.');
        }
    }

    # =====================================================================
    # BAGIAN 5: FUNGSI-FUNGSI PUSAT / GLOBAL UNTUK SEMUA ROLE
    # =====================================================================

    # --- FUNGSI OTOMATIS MENAMPILKAN POPUP KOTAK LONCENG NOTIFIKASI ---
    public function pusatNotifikasi()
    {
        # Tangkap siapa orangnya?
        $user = Auth::user();

        # Normalisasi (Ratakan) string peran/jabatannya menjadi 3 kode murni huruf kecil
        $nilaiRole = $user->peran ?? '';
        $roleRaw = strtolower($nilaiRole);

        if ($roleRaw === 'admin' || $roleRaw === 'administrator' || $roleRaw === '1') {
            $role = 'admin';
        } elseif (str_contains($roleRaw, 'kepala') || $roleRaw === '2') {
            $role = 'kepala';
        } else {
            $role = 'petugas';
        }

        # Susun string folder tempat layout warna CSS menu berada ('layouts.admin', dsb)
        $layout = 'layouts.' . $role;

        # Tarik data dari laci surat (tabel notifikasi) MURNI KHUSUS UNTUK KELOMPOK ROLE TERSEBUT (Where 'untuk_role')
        $notifikasiList = DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->orderBy('created_at', 'desc')
            ->get();

        # Pindai/Scan apakah dari tumpukan surat tadi ada minimal satu saja yang masih 'Belum' dibaca? (Fungsi contains() menghasilkan True/False)
        $hasUnread = $notifikasiList->contains('status_baca', 'Belum');

        # Buka pop-up loncengnya di HTML
        return view('shared.notifikasi', [
            'title' => 'Pusat Notifikasi',
            'layout' => $layout,
            'role' => $role,
            'notifikasiList' => $notifikasiList,
            'hasUnread' => $hasUnread # Kalau True, JavaScript akan menyalakan Titik Merah notifikasi
        ]);
    }

    # --- FUNGSI SEKALI KLIK SAPU BERSIH: MARK ALL AS READ ---
    public function bacaSemuaNotifikasi()
    {
        # Deteksi jabatannya
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

        # SANGAT AMAN:
        # Timpa paksa (Update massal) status 'Belum' menjadi 'Sudah',
        # TAPI DENGAN SYARAT KETAT (->where('untuk_role', $role)).
        # Artinya Petugas tidak akan secara keliru men-"Sudah"-kan surat teguran miliknya Kepala Apotek.
        DB::table('notifikasi')
            ->where('untuk_role', $role)
            ->where('status_baca', 'Belum')
            ->update(['status_baca' => 'Sudah']);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    # --- FUNGSI MENYIMPAN FORM EDIT NAMA DIRI SENDIRI ---
    public function simpanProfilGlobal(Request $request)
    {
        $user = Auth::user();
        # Amankan tangkapan objek ID (Berjaga-jaga siapa tahu nama kolom Primary Key-nya berganti di model)
        $idPengguna = $user->id_pengguna ?? $user->id;

        # Validasi khusus Profil Sendiri: Username Unique TAPI diberi dispensasi untuk ID-nya dia sendiri
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pengguna,username,' . $idPengguna . ',id_pengguna',
        ]);

        # Lakukan update nama pada baris aslinya di tabel
        DB::table('pengguna')->where('id_pengguna', $idPengguna)->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
        ]);

        # Catat aktivitas mandiri ini
        LogAudit::create([
            'id_pengguna' => $idPengguna,
            'aktivitas'   => 'Memperbarui data profil pribadi secara mandiri',
            'alamat_ip'   => request()->ip(),
            'status'      => 'Success',
            'created_at'  => now(),
        ]);

        return back()->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    # --- FUNGSI MENGUNDUH DATABASE KATALOG OBAT KE EXCEL (.CSV) KEPALA APOTEK ---
    public function exportLaporanExcel()
    {
        # Merakit string nama File agar dinamis menyesuaikan detik kapan tombolnya di klik
        # (Misal: Laporan_Stok_SIOPAL_2026-06-30_21-12.csv)
        $namaFile = 'Laporan_Stok_SIOPAL_' . date('Y-m-d_H-i') . '.csv';

        # Menarik paksa seluruh baris obat di tabel (Tidak dibatasi paginate)
        $dataObat = DB::table('obat')
            ->select('id_obat', 'id_kategori', 'nama_obat', 'dosis', 'satuan_dosis', 'bentuk_sediaan', 'letak_rak', 'batas_stok_min', 'total_stok')
            ->orderBy('nama_obat', 'asc')
            ->get();

        # Membuat paket pembungkus HTTP Headers (Surat Perintah kepada Browser Chrome/Mozilla)
        # Tujuannya: "Wahai browser, jangan baca kode di bawah sebagai teks layarmu, namun langsung buang dia jadi file Excel lampiran (Attachment)!"
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$namaFile",
            "Pragma"              => "no-cache", # Jangan simpan file nyangkut di riwayat
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        # Mendeklarasikan pekerja pembuat isian file-nya (Fungsi Closure)
        $callback = function () use ($dataObat) {
            # Buka Saluran 'php://output' (Saluran pembuangan file) dengan mode Write ('w')
            $file = fopen('php://output', 'w');

            # KUNCI FORMAT RAPI: Masukkan daftar Header Judul Tabel ke baris paling atas Excel.
            # Menggunakan fputcsv dan WAJIB ditutup/dipisahkan dengan karakter TITIK KOMA (;)
            # Kenapa bukan Koma (,)? Karena di region Windows Indonesia, Koma dianggap bilangan desimal, sehingga seluruh kolom di Excel akan hancur menumpuk di 1 kotak!
            fputcsv($file, ['ID Obat', 'Nama Obat', 'Kategori', 'Sediaan', 'Letak Rak', 'Batas Minimal', 'Stok Saat Ini', 'Status'], ';');

            # Mulai mencacah data hasil tarikan tabel Obat tadi baris per baris...
            foreach ($dataObat as $obat) {
                # Cerdas buatan: Sisipkan teks vonis Status di ujung kanan tabelnya secara dinamis
                $status = $obat->total_stok <= $obat->batas_stok_min ? 'Menipis (Perlu Restok)' : 'Aman';

                # Cetak masuk isi rinciannya ke dalam kotak/sel Excel yang sejajar urutannya dengan Header Judul di atas
                fputcsv($file, [
                    $obat->id_obat,
                    $obat->nama_obat,
                    $obat->id_kategori,
                    # Satukan teks sediaan (Misal: 500 + mg + Kapsul) ke dalam 1 sel yang sama
                    $obat->dosis . ' ' . $obat->satuan_dosis . ' ' . $obat->bentuk_sediaan,
                    $obat->letak_rak,
                    $obat->batas_stok_min,
                    $obat->total_stok,
                    $status
                ], ';');
            }

            # Jika semua baris sudah habis, matikan saluran/tutup file-nya
            fclose($file);
        };

        # Lempar kembali ke browser dengan status 200 OK, beserta Header dan Callback yang sudah dirakit di atas
        return response()->stream($callback, 200, $headers);
    }
}
