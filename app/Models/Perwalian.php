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
        'ID_Dosen_Wali',
        'username',
        'Status',
        'nama',
        'kelas',
        'angkatan',
        'Tanggal',
        'Tanggal_Selesai',
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
        'Status' => 'string', // Ensure enum is cast as string
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the dosen wali associated with this perwalian.
     */
    public function dosenWali()
    {
        return $this->belongsTo(Dosen_Wali::class, 'ID_Dosen_Wali', 'nip');
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