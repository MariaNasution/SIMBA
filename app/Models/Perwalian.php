<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perwalian extends Model
{
    protected $table = 'perwalian';
    protected $primaryKey = 'ID_Perwalian';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['ID_Perwalian', 'ID_Dosen_Wali', 'Status', 'Tanggal', 'nama', 'kelas', 'angkatan'];

    public function dosenWali()
    {
        return $this->belongsTo(DosenWali::class, 'ID_Dosen_Wali', 'ID_Dosen_Wali');
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'ID_Perwalian', 'ID_Perwalian');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'ID_Perwalian', 'ID_Perwalian');
    }
}