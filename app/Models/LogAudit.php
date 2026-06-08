<?php

# Menentukan lokasi (namespace) file model ini agar mudah ditemukan oleh sistem Laravel
namespace App\Models;

# Mengimpor class Model dasar bawaan Eloquent Laravel
use Illuminate\Database\Eloquent\Model;

# Mendeklarasikan class LogAudit yang mewarisi seluruh kemampuan (extends) dari class Model
class LogAudit extends Model
{
    # Memberitahu Laravel secara eksplisit bahwa model ini terhubung dengan tabel bernama 'log_audit'
    protected $table = 'log_audit';

    # Menentukan kolom 'id_log' sebagai primary key, karena Laravel secara default akan mencari kolom bernama 'id'
    protected $primaryKey = 'id_log';

    # Menonaktifkan fitur otomatis pengisian kolom 'created_at' dan 'updated_at' bawaan Laravel
    # (Karena di tabel ini kita hanya menggunakan 'created_at' dan mengisinya secara manual dari Controller)
    public $timestamps = false;

    # Mendefinisikan daftar kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
    # Ini adalah fitur keamanan Laravel untuk mencegah pengguna memasukkan data ke kolom yang tidak seharusnya
    protected $fillable = [
        'id_pengguna',
        'aktivitas',
        'alamat_ip',
        'status',
        'created_at'
    ];

    # --- BLOK RELASI ANTAR TABEL ---
    # Membuat fungsi bernama 'pengguna' untuk menjembatani tabel log_audit dengan tabel pengguna
    public function pengguna()
    {
        # Menyatakan bahwa setiap 1 baris data di tabel log_audit "dimiliki oleh" (belongsTo) 1 baris data di tabel Pengguna.
        # Parameter kedua ('id_pengguna') adalah nama kolom foreign key di tabel log_audit.
        # Parameter ketiga ('id_pengguna') adalah nama kolom primary key tujuan di tabel pengguna.
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
