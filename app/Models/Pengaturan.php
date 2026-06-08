<?php

# Menentukan lokasi (namespace) file model ini berada agar dikenali oleh struktur folder aplikasi Laravel
namespace App\Models;

# Mengimpor class Model dasar bawaan dari ORM Eloquent Laravel
use Illuminate\Database\Eloquent\Model;

# Mendeklarasikan class Pengaturan yang mewarisi (extends) seluruh kemampuan dan fitur dari class Model bawaan
class Pengaturan extends Model
{
    # Memberitahu Laravel secara eksplisit bahwa model ini berinteraksi langsung dengan tabel bernama 'pengaturan' di database
    protected $table = 'pengaturan';

    # Menyesuaikan kolom primary key menjadi 'id_pengaturan',
    # karena jika tidak diatur, Laravel secara otomatis akan mencari kolom yang hanya bernama 'id'
    protected $primaryKey = 'id_pengaturan';

    # Menonaktifkan pengisian otomatis kolom 'created_at' dan 'updated_at' oleh Laravel
    # (Di model ini dinonaktifkan karena tabel pengaturan hanya menggunakan 'updated_at' yang diatur otomatis dari sisi SQL)
    public $timestamps = false;

    # Mendefinisikan daftar kolom yang diizinkan untuk diisi atau diperbarui secara massal (Mass Assignment)
    # Ini adalah lapisan keamanan Laravel untuk mencegah user menyuntikkan data ke kolom yang tidak semestinya
    protected $fillable = [
        'nama_apotek',         # Menyimpan data nama identitas aplikasi/apotek
        'alamat_apotek',       # Menyimpan data alamat dan kontak (biasanya dipakai untuk kop laporan PDF)
        'wajib_password_kuat', # Menyimpan status (1/0) pengaturan wajib menggunakan sandi kombinasi
        'auto_logout',         # Menyimpan status (1/0) pengaturan sistem untuk memutus sesi jika diam terlalu lama
        'log_audit_global'     # Menyimpan status (1/0) pengaturan perekaman log semua aktivitas di dalam aplikasi
    ];
}
