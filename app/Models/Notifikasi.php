<?php

# Menentukan lokasi (namespace) file model ini berada agar dikenali oleh sistem Laravel
namespace App\Models;

# Mengimpor class Model dasar bawaan Eloquent Laravel
use Illuminate\Database\Eloquent\Model;

# Mendeklarasikan class Notifikasi yang mewarisi (extends) seluruh kemampuan dari class Model bawaan
class Notifikasi extends Model
{
    # Memberitahu Laravel secara eksplisit bahwa model ini terhubung dengan tabel bernama 'notifikasi' di database
    protected $table = 'notifikasi';

    # Menentukan kolom 'id_notifikasi' sebagai primary key (kunci utama) tabel ini,
    # karena secara bawaan (default) Laravel akan selalu mencari kolom bernama 'id'
    protected $primaryKey = 'id_notifikasi';

    # Menonaktifkan fitur manajemen waktu otomatis bawaan Laravel (kolom 'created_at' dan 'updated_at')
    # Hal ini dilakukan karena kita menggunakan timestamp SQL atau mengisinya secara manual dari Controller
    public $timestamps = false;

    # Mendefinisikan daftar kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
    # Fitur ini melindungi database dari celah keamanan agar user tidak bisa memanipulasi kolom lain yang tidak diizinkan
    protected $fillable = [
        'tipe',         # Kolom untuk jenis notifikasi (Keamanan, Sistem, Akun)
        'judul',        # Kolom untuk judul singkat notifikasi
        'pesan',        # Kolom untuk isi teks detail pemberitahuan
        'status_baca',  # Kolom untuk penanda apakah notifikasi sudah dibaca ('Belum' atau 'Sudah')
        'created_at'    # Kolom untuk mencatat waktu kapan notifikasi tersebut dibuat
    ];
}
