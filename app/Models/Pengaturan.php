<?php

# =====================================================================
# PENGATURAN AWAL (NAMESPACE & IMPORT)
# =====================================================================

# Menentukan 'alamat' (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Memanggil cetakan dasar Model bawaan Laravel (Eloquent) yang menyimpan fitur perintah database
use Illuminate\Database\Eloquent\Model;

# =====================================================================
# DEKLARASI MODEL (CETAKAN TABEL PENGATURAN)
# =====================================================================

# Membuat class Pengaturan yang mewarisi seluruh kemampuan canggih dari class Model bawaan
class Pengaturan extends Model
{
    # Memberitahu Laravel secara tegas bahwa model ini bertugas mengelola tabel bernama 'pengaturan'
    protected $table = 'pengaturan';

    # Menentukan 'id_pengaturan' sebagai kunci utama (Primary Key).
    # Ini wajib diketik karena Laravel secara bawaan selalu menduga bahwa primary key kita bernama 'id'.
    protected $primaryKey = 'id_pengaturan';

    # Mematikan fitur cap waktu (timestamps) otomatis bawaan Laravel (created_at & updated_at).
    # Kita matikan karena pada tabel ini kita hanya bergantung pada 'updated_at' yang nilainya diubah otomatis oleh sistem database (SQL).
    public $timestamps = false;

    # Mendefinisikan 'fillable' atau daftar kolom yang diizinkan untuk diubah datanya secara bersamaan.
    # Ini adalah gembok keamanan Laravel agar peretas tidak bisa memanipulasi pengaturan di luar kolom yang diizinkan.
    protected $fillable = [
        'nama_apotek',         # Menyimpan nama identitas apotek/aplikasi yang akan tampil di layar
        'alamat_apotek',       # Menyimpan detail alamat (biasanya digunakan untuk mencetak kop surat/faktur PDF)
        'wajib_password_kuat', # Saklar (nilai 1 atau 0) untuk memaksa pengguna memakai kata sandi yang rumit
        'auto_logout',         # Saklar (nilai 1 atau 0) untuk mengaktifkan pemutusan sesi otomatis jika pengguna tidak aktif
        'log_audit_global'     # Saklar (nilai 1 atau 0) untuk menghidupkan fitur perekaman jejak aktivitas sistem
    ];
}
