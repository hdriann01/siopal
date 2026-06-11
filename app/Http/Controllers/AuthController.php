<?php

# =====================================================================
# 1. BAGIAN PERSIAPAN (IMPOR KELAS DAN PENGATURAN LOKASI)
# =====================================================================

# Menentukan 'alamat' (namespace) file ini agar Laravel tahu di mana mencarinya
namespace App\Http\Controllers;

# Memanggil alat pembawa data (Request) untuk menangkap ketikan user dari form
use Illuminate\Http\Request;

# Memanggil alat keamanan (Auth) untuk mengecek apakah user boleh masuk atau tidak
use Illuminate\Support\Facades\Auth;

# Memanggil cetakan tabel (Model) LogAudit untuk menyimpan jejak riwayat ke database
use App\Models\LogAudit;

# Membuat kelas AuthController yang mewarisi kemampuan bawaan dari Laravel (Controller)
class AuthController extends Controller
{
    # =====================================================================
    # 2. FUNGSI MENAMPILKAN HALAMAN LOGIN
    # =====================================================================

    # Fungsi ini hanya bertugas satu hal: membukakan pintu depan (halaman login)
    public function showLogin()
    {
        # Mengambil dan menampilkan desain HTML dari file: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    # =====================================================================
    # 3. FUNGSI MEMPROSES DATA LOGIN (SAAT TOMBOL DITEKAN)
    # =====================================================================

    # Fungsi ini menangkap data yang dikirim user dari form HTML (lewat $request)
    public function login(Request $request)
    {
        # Langkah A: Pengecekan Syarat Wajib
        # Memastikan user tidak mengosongkan kolom username dan password
        $credentials = $request->validate([
            'username' => ['required'], # Kolom username wajib diisi
            'password' => ['required'], # Kolom password wajib diisi
        ]);

        # Langkah B: Pengecekan Kunci (Mencocokkan dengan Database)
        # Auth::attempt() akan mengecek apakah username & password itu benar ada di database
        if (Auth::attempt($credentials)) {

            # Jika benar, segera perbarui ID Sesi (Session) agar tidak diretas oleh hacker
            $request->session()->regenerate();

            # Mengambil seluruh data profil user yang baru saja sukses login
            $user = Auth::user();

            # Langkah C: Mencatat Riwayat Keamanan (Audit Trail)
            # Menyimpan jejak bahwa user ini baru saja login ke dalam tabel 'log_audit'
            LogAudit::create([
                'id_pengguna' => $user->id_pengguna,           # Siapa yang login? (ID-nya)
                'aktivitas'   => 'Melakukan Login ke Sistem',  # Apa yang dia lakukan?
                'alamat_ip'   => $request->ip(),               # Dari alamat internet (IP) mana?
                'status'      => 'Success',                    # Apakah berhasil? Ya.
                'created_at'  => now(),                        # Kapan ini terjadi? (Waktu saat ini)
            ]);

            # Langkah D: Mengatur Arah Pintu Masuk Sesuai Jabatan (Peran)
            # Mengecek jabatan user untuk menentukan dashboard mana yang akan dibuka
            if ($user->peran == 'Administrator') {

                # Jika dia Admin, bukakan pintu ke ruang Admin
                return redirect()->intended('admin/dashboard');
            } elseif ($user->peran == 'Kepala Apotek') {

                # Jika dia Kepala Apotek, bukakan pintu ke ruang Kepala
                return redirect()->intended('kepala/dashboard');
            } else {

                # Jika bukan keduanya (berarti Petugas Apotek), bukakan pintu ke ruang Petugas
                return redirect()->intended('petugas/dashboard');
            }
        }

        # Langkah E: Jika Username atau Password Salah
        # Tendang kembali ke halaman login (back) dan bawa pesan error ini
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username'); # Biarkan username yang tadi diketik tetap ada (agar tidak capek ngetik ulang)
    }

    # =====================================================================
    # 4. FUNGSI LOGOUT (KELUAR DARI SISTEM)
    # =====================================================================

    # Fungsi ini bertugas menutup sesi dan mengunci pintu kembali
    public function logout(Request $request)
    {
        # Mencabut status 'sedang login' dari user tersebut
        Auth::logout();

        # Menghancurkan memori sesi di browser agar datanya tidak bisa diintip orang lain
        $request->session()->invalidate();

        # Membuat kunci keamanan baru (Token CSRF) agar form login ter-reset aman
        $request->session()->regenerateToken();

        # Arahkan user kembali ke halaman utama (halaman login)
        return redirect('/login');
    }
}
