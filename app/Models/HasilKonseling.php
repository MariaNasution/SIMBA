<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilKonseling extends Model
{
    use HasFactory;
    
    protected $table = 'hasil_konseling'; // Sesuai dengan migration

    protected $fillable = ['nama', 'nim', 'file', 'keterangan'];
}
