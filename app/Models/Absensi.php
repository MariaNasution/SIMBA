<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Perwalian; // Import Perwalian model


class Absensi extends Model
{
    protected $table = 'absensi'; 
    protected $primaryKey = 'ID_Absensi'; 
    public $incrementing = true; // Disable auto-increment

    protected $fillable = ['ID_Absensi', 'Kelas', 'nim', 'status_kehadiran', 'keterangan'];

    


}
