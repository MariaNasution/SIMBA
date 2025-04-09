<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perwalian extends Model
{
    protected $table = 'perwalian';
    protected $primaryKey = 'ID_Perwalian';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Status',
        'Tanggal',
        'Tanggal_Selesai',
        'nama',
        'kelas',
        'angkatan',
        'role',
        'keterangan',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'Tanggal' => 'datetime',
        'Tanggal_Selesai' => 'datetime',
    ];

    /**
     * Get the dosen wali associated with this perwalian.
     */
    public function dosenWali()
    {
        return $this->belongsTo(DosenWali::class, 'ID_Dosen_Wali', 'nip');
    }

    /**
     * Get the mahasiswas associated with this perwalian.
     */
    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'ID_Perwalian', 'ID_Perwalian');
    }

    /**
     * Get the absensi records associated with this perwalian.
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'ID_Perwalian', 'ID_Perwalian');
    }
}