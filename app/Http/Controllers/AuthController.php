<?php

# Mendefinisikan namespace tempat controller ini berada agar dikenali oleh Laravel
namespace App\Http\Controllers;

# Mengimpor class Request untuk menangani dan menangkap data inputan dari pengguna
use Illuminate\Http\Request;
# Mengimpor facade Auth untuk menangani proses autentikasi (pemeriksaan login/logout)
use Illuminate\Support\Facades\Auth;
# Mengimpor model LogAudit agar sistem bisa menyimpan rekaman riwayat ke database
use App\Models\LogAudit;

# Mendeklarasikan class AuthController yang mewarisi fungsi dari class Controller bawaan
class AuthController extends Controller
{
    # Fungsi ini bertugas khusus untuk menampilkan antarmuka halaman login
    public function showLogin()
    {
        # Mengembalikan dan menampilkan file view yang berada di folder resources/views/auth/login.blade.php
        return view('auth.login');
    }

    # Fungsi ini bertugas untuk memproses data (username & password) yang dikirim saat tombol login ditekan
    public function login(Request $request)
    {
        # Memvalidasi inputan form; memastikan kolom 'username' dan 'password' wajib diisi (required)
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        # Memeriksa apakah username dan password yang diinput cocok dengan data di database
        if (Auth::attempt($credentials)) {
            # Jika cocok, buat ulang ID sesi (session) untuk melindungi dari serangan pencurian sesi (session fixation)
            $request->session()->regenerate();

            # Menyimpan seluruh data pengguna yang berhasil login ke dalam variabel $user
            $user = Auth::user();

            # Menjalankan perintah untuk mencatat aktivitas login ini ke dalam tabel 'log_audit'
            LogAudit::create([
                'id_pengguna' => $user->id_pengguna, # Mengambil ID dari akun yang baru saja login
                'aktivitas'   => 'Melakukan Login ke Sistem', # Keterangan aktivitas yang dilakukan
                'alamat_ip'   => $request->ip(), # Mendeteksi dan menyimpan alamat IP perangkat pengguna
                'status'      => 'Success', # Menandakan bahwa proses login berhasil masuk
                'created_at'  => now(), # Mencatat waktu saat fungsi ini dieksekusi
            ]);

            # Mengevaluasi hak akses (peran) dari pengguna untuk menentukan halaman tujuan (dashboard)
            if ($user->peran == 'Administrator') {
                # Jika perannya Administrator, arahkan ke URL admin/dashboard
                return redirect()->intended('admin/dashboard');
            } elseif ($user->peran == 'Kepala Apotek') {
                # Jika perannya Kepala Apotek, arahkan ke URL kepala/dashboard
                return redirect()->intended('kepala/dashboard');
            } else {
                # Jika perannya Petugas Apotek, arahkan ke URL petugas/dashboard
                return redirect()->intended('petugas/dashboard');
            }
        }

        # Jika pengecekan kredensial gagal (username/password salah), kembalikan ke halaman login
        return back()->withErrors([
            # Memunculkan pesan peringatan error di bawah kolom inputan
            'username' => 'Username atau password salah.',
        ])->onlyInput('username'); # Menyimpan inputan username sebelumnya agar pengguna tidak perlu mengetik ulang
    }

    # Fungsi ini bertugas untuk mengeluarkan pengguna dari sistem aplikasi (logout)
    public function logout(Request $request)
    {
        # Menghapus sesi autentikasi, membuat status pengguna menjadi belum login
        Auth::logout();

        # Membatalkan dan membersihkan semua data sesi yang tersimpan di memori browser
        $request->session()->invalidate();

        # Membuat ulang token CSRF baru untuk mencegah celah keamanan pemalsuan permintaan antar situs
        $request->session()->regenerateToken();

        # Mengarahkan pengguna kembali ke halaman awal (form login)
        return redirect('/login');
    }
}
