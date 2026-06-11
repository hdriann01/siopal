<?php

# =====================================================================
# PENGATURAN AWAL (NAMESPACE & IMPORT)
# =====================================================================

# Menentukan 'alamat' (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Memanggil cetakan dasar Model bawaan Laravel (Eloquent) yang menyimpan fitur perintah database
use Illuminate\Database\Eloquent\Model;

# =====================================================================
# DEKLARASI MODEL (CETAKAN TABEL LOG AUDIT)
# =====================================================================

# Membuat class LogAudit yang mewarisi seluruh kemampuan canggih dari class Model bawaan
class LogAudit extends Model
{
    # Memberitahu Laravel secara tegas bahwa model ini bertugas mengelola tabel bernama 'log_audit'
    protected $table = 'log_audit';

    # Menentukan 'id_log' sebagai kunci utama (Primary Key).
    # Ini wajib diketik karena Laravel secara bawaan selalu menduga bahwa primary key kita bernama 'id'.
    protected $primaryKey = 'id_log';

    # Mematikan fitur cap waktu (timestamps) otomatis bawaan Laravel (created_at & updated_at).
    # Kita matikan karena di tabel ini kita hanya butuh 'created_at' dan mengisinya sendiri dari Controller.
    public $timestamps = false;

    # Mendefinisikan 'fillable' atau daftar kolom yang diizinkan untuk diisi datanya secara bersamaan.
    # Ini adalah gembok keamanan Laravel agar peretas tidak bisa menyisipkan data secara paksa ke kolom lain.
    protected $fillable = [
        'id_pengguna',
        'aktivitas',
        'alamat_ip',
        'status',
        'created_at'
    ];

    # =====================================================================
    # BLOK RELASI ANTAR TABEL (HUBUNGAN KARDINALITAS)
    # =====================================================================

    # Membuat fungsi bernama 'pengguna' untuk menjembatani tabel log_audit dengan tabel pengguna
    public function pengguna()
    {
        # Menyatakan aturan relasi: Setiap 1 baris riwayat log "dimiliki oleh" (belongsTo) 1 Pengguna.
        # Parameter 1: Class model tujuan (Pengguna::class)
        # Parameter 2: Nama kolom kunci tamu (Foreign Key) yang ada di tabel log_audit ('id_pengguna')
        # Parameter 3: Nama kolom kunci utama (Primary Key) yang ada di tabel pengguna ('id_pengguna')
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
