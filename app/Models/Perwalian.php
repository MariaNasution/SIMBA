<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perwalian extends Model
{
    protected $table = 'perwalian';
    protected $primaryKey = 'ID_Perwalian';
    public $incrementing = true; 
    protected $keyType = 'int'; // Ensure the key type is an integer (default for id())

    protected $fillable = ['ID_Perwalian', 'ID_Dosen_Wali', 'Status', 'Tanggal', 'nama', 'kelas'];

    public function dosenWali()
    {
        return $this->belongsTo(DosenWali::class, 'ID_Dosen_Wali', 'ID_Dosen_Wali'); // Link via ID_Dosen_Wali
    }
}