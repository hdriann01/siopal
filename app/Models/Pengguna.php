<?php

# Menentukan lokasi (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Mengimpor class User bawaan Laravel dan memberinya alias 'Authenticatable'.
# Class ini memiliki semua fungsi yang dibutuhkan agar sistem bisa melakukan proses login (Auth).
use Illuminate\Foundation\Auth\User as Authenticatable;

# Mengimpor trait Notifiable agar model (pengguna) ini bisa menerima notifikasi bawaan Laravel
use Illuminate\Notifications\Notifiable;

# Mendeklarasikan class Pengguna yang mewarisi (extends) fitur autentikasi, bukan Model biasa
class Pengguna extends Authenticatable
{
    # Mengaktifkan trait Notifiable di dalam class ini
    use Notifiable;

    # Memberitahu Laravel secara eksplisit bahwa model ini terhubung dengan tabel bernama 'pengguna'
    protected $table = 'pengguna';

    # Menyesuaikan kolom primary key menjadi 'id_pengguna' (bawaan Laravel biasanya mencari kolom 'id')
    protected $primaryKey = 'id_pengguna';

    # Menonaktifkan fitur auto-increment (penambahan angka otomatis).
    # Ini WAJIB karena primary key kita formatnya huruf dan angka (contoh: USRX123), bukan angka berurutan.
    public $incrementing = false;

    # Memberitahu Laravel bahwa tipe data dari primary key tersebut adalah teks (string)
    protected $keyType = 'string';

    # Mendefinisikan daftar kolom yang diizinkan untuk diisi atau diperbarui secara massal (Mass Assignment)
    # Ini melindungi database dari celah keamanan manipulasi input
    protected $fillable = [
        'id_pengguna',
        'nama_lengkap',
        'username',
        'password',
        'peran',
    ];

    # Menyembunyikan kolom tertentu saat data pengguna diubah menjadi format array atau JSON.
    # Sangat penting menyembunyikan 'password' agar tidak bocor/tampil secara tidak sengaja di tampilan atau API.
    protected $hidden = [
        'password',
    ];

    # Menonaktifkan pengisian otomatis kolom 'created_at' dan 'updated_at' oleh Laravel
    # (Karena struktur tabel pengguna saat ini tidak memakai kolom waktu tersebut)
    public $timestamps = false;
}
