<?php

# =====================================================================
# PENGATURAN AWAL (NAMESPACE & IMPORT)
# =====================================================================

# Menentukan 'alamat' (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Mengimpor class User bawaan Laravel dan memberinya nama samaran (alias) 'Authenticatable'.
# Class ini sangat sakti karena memiliki semua fungsi dasar agar sistem bisa mengenali proses login (Auth).
use Illuminate\Foundation\Auth\User as Authenticatable;

# Mengimpor fitur tambahan (trait) Notifiable agar pengguna ini bisa menerima surat/notifikasi dari sistem Laravel
use Illuminate\Notifications\Notifiable;

# =====================================================================
# DEKLARASI MODEL (CETAKAN TABEL PENGGUNA UNTUK LOGIN)
# =====================================================================

# Membuat class Pengguna yang mewarisi fitur khusus login (Authenticatable), bukan Model biasa
class Pengguna extends Authenticatable
{
    # Mengaktifkan saklar fitur Notifiable di dalam class ini agar fungsi kirim pesan berjalan
    use Notifiable;

    # Memberitahu Laravel secara tegas bahwa model ini bertugas mengelola tabel bernama 'pengguna'
    protected $table = 'pengguna';

    # Menentukan 'id_pengguna' sebagai kunci utama (Primary Key).
    # Ini wajib diketik karena Laravel secara bawaan selalu menduga bahwa primary key kita bernama 'id'.
    protected $primaryKey = 'id_pengguna';

    # Mematikan fitur hitung angka otomatis (auto-increment) bawaan Laravel.
    # Ini WAJIB dinonaktifkan karena ID kita menggunakan format teks kustom (contoh: USRX1Y2Z3), bukan angka berurutan 1, 2, 3.
    public $incrementing = false;

    # Memberitahu Laravel bahwa jenis data dari kunci utama (Primary Key) tersebut adalah berupa teks (string)
    protected $keyType = 'string';

    # Mendefinisikan 'fillable' atau daftar kolom yang diizinkan untuk diisi datanya secara bersamaan.
    # Ini adalah gembok keamanan Laravel agar pengguna tidak bisa memanipulasi input ke kolom yang tidak sah.
    protected $fillable = [
        'id_pengguna',  # Menyimpan kode unik buatan sistem untuk setiap pengguna
        'nama_lengkap', # Menyimpan nama asli lengkap pemilik akun
        'username',     # Menyimpan nama unik akun untuk kebutuhan mengetik di form login
        'password',     # Menyimpan kata sandi rahasia yang sudah diacak
        'peran',        # Menyimpan hak akses tingkatan jabatan (Administrator, Kepala Apotek, Petugas Apotek)
    ];

    # Menyembunyikan kolom tertentu agar tidak ikut terpotret/tampil ketika data pengguna diubah ke bentuk array atau JSON.
    # Ini adalah aturan wajib bagi kolom 'password' agar kode rahasianya tidak bocor atau tampil tanpa sengaja di layar browser.
    protected $hidden = [
        'password',
    ];

    # Mematikan fitur cap waktu (timestamps) otomatis bawaan Laravel (created_at & updated_at).
    # Kita matikan karena pada struktur tabel pengguna saat ini kita tidak menggunakan kolom-kolom waktu tersebut.
    public $timestamps = false;
}
