<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    // Specify the table name and primary key
    protected $table = 'notifikasi';
    protected $primaryKey = 'ID_Notifikasi';
    public $incrementing = true;

    // Only include fields that should be mass assignable.
    // Removed the primary key since it auto-increments.
    protected $fillable = [
        'Pesan',
        'nim',
        'Id_Konseling',
        'Id_Perwalian',
        'nama'
    ];

    // Optionally, cast foreign keys to integers for consistency.
    protected $casts = [
        'Id_Konseling'  => 'integer',
        'Id_Perwalian'  => 'integer',
    ];

    /**
     * Relationship with Mahasiswa model.
     * Each notification belongs to a student (Mahasiswa).
     * Assumes the Mahasiswa model has a primary key 'NIM'.
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'NIM');
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
     * Relationship with RequestKonseling model.
     * Each notification is related to a konseling request.
     */
    public function konseling()
    {
        return $this->belongsTo(RequestKonseling::class, 'Id_Konseling', 'ID_Konseling');
    }
}
