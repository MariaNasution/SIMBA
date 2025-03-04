<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Perwalian; // Import Perwalian model


class Absensi extends Model
{
    protected $table = 'absensi'; 
    protected $primaryKey = 'ID_Absensi'; 
    public $incrementing = false; // Disable auto-increment

    protected $fillable = ['ID_Absensi', 'ID_Perwalian', 'SatusKehadiran'];

    public function perwalian()
    {
        return $this->belongsTo(Perwalian::class, 'ID_Perwalian', 'ID_Perwalian'); // Relationship to Perwalian model
    }


}
