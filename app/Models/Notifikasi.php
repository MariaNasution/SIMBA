<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi'; // Specify the table name
    protected $primaryKey = 'ID_Notifikasi'; // Set primary key
    public $incrementing = true; // Auto increment primary key

    protected $fillable = ['ID_Notifikasi', 'Pesan', 'nim', 'Id_Perwalian', 'nama'];

    /**
     * Relationship with Mahasiswa model.
     * Each notification belongs to a student (Mahasiswa).
     */
    public function mahasiswa()
    {
        //return $this->belongsTo(Mahasiswa::class, 'NIM', 'nim');
    }

    /**
     * Relationship with Perwalian model.
     * Each notification may be related to a Perwalian session.
     */
    public function perwalian()
    {
        return $this->belongsTo(Perwalian::class, 'Id_Perwalian', 'ID_Perwalian');
    }

    /**
     * Relationship dengan RequestKonseling.
     * Setiap notifikasi terkait dengan pengajuan konseling tertentu.
     */
    public function konseling()
    {
        return $this->belongsTo(RequestKonseling::class, 'Id_Konseling', 'ID_Konseling');
    }
}
