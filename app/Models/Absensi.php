<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'ID_Absensi';
    public $incrementing = true;

    protected $fillable = ['ID_Absensi', 'ID_Perwalian', 'nim', 'kelas', 'status_kehadiran', 'keterangan'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function perwalian()
    {
        return $this->belongsTo(Perwalian::class, 'ID_Perwalian', 'ID_Perwalian');
    }
}