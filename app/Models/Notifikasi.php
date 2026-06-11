<?php

# =====================================================================
# PENGATURAN AWAL (NAMESPACE & IMPORT)
# =====================================================================

# Menentukan 'alamat' (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Memanggil cetakan dasar Model bawaan Laravel (Eloquent) yang menyimpan fitur perintah database
use Illuminate\Database\Eloquent\Model;

# =====================================================================
# DEKLARASI MODEL (CETAKAN TABEL NOTIFIKASI)
# =====================================================================

# Membuat class Notifikasi yang mewarisi seluruh kemampuan canggih dari class Model bawaan
class Notifikasi extends Model
{
    # Memberitahu Laravel secara tegas bahwa model ini bertugas mengelola tabel bernama 'notifikasi'
    protected $table = 'notifikasi';

    # Menentukan 'id_notifikasi' sebagai kunci utama (Primary Key).
    # Ini wajib diketik karena Laravel secara bawaan selalu menduga bahwa primary key kita bernama 'id'.
    protected $primaryKey = 'id_notifikasi';

    # Mematikan fitur cap waktu (timestamps) otomatis bawaan Laravel (created_at & updated_at).
    # Kita matikan karena pada tabel ini kita hanya menggunakan 'created_at' dan mengaturnya sendiri.
    public $timestamps = false;

    # Mendefinisikan 'fillable' atau daftar kolom yang diizinkan untuk diisi datanya secara bersamaan.
    # Ini adalah gembok keamanan Laravel agar peretas tidak bisa menyisipkan data secara paksa ke kolom lain.
    protected $fillable = [
        'tipe',         # Menyimpan kategori pemberitahuan (contoh: Keamanan, Sistem, Stok)
        'judul',        # Menyimpan judul ringkas dari notifikasi tersebut
        'pesan',        # Menyimpan kalimat detail atau isi lengkap dari pemberitahuan
        'status_baca',  # Penanda saklar apakah pesan ini 'Belum' atau 'Sudah' dibaca oleh pengguna
        'created_at'    # Mencatat stempel waktu kapan alarm/notifikasi ini dipicu oleh sistem
    ];
}
