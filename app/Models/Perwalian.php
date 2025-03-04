<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perwalian extends Model
{
    protected $table = 'perwalian';
    protected $primaryKey = 'ID_Perwalian';
    public $incrementing = false; // Disable auto-increment (if it's not an integer)

    protected $fillable = ['ID_Perwalian', 'ID_Dosen_Wali', 'Status', 'Tanggal'];

    public function dosenWali()
    {
        return $this->belongsTo(DosenWali::class, 'ID_Dosen_Wali', 'ID_Dosen_Wali'); // Link via ID_Dosen_Wali
    }
}