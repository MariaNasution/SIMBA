<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi'; // Specify the table name
    protected $primaryKey = 'ID_Notifikasi'; // Set primary key
    public $incrementing = true; // Disable auto-increment (if it's not an integer)

    protected $fillable = ['ID_Notifikasi', 'Pesan', 'nim', 'Id_Perwalian', 'nama'];

    /**
     * Relationship with Mahasiswa model.
     * Each notification belongs to a student (Mahasiswa).
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'NIM', 'nim');
    }

    /**
     * Relationship with Perwalian model.
     * Each notification may be related to a Perwalian session.
     */
    public function perwalian()
    {
        return $this->belongsTo(Perwalian::class, 'Id_Perwalian', 'ID_Perwalian');
    }
}
