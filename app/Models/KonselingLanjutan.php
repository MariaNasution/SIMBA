<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonselingLanjutan extends Model
{
    use HasFactory;
    protected $table = 'konseling_lanjutans';
    protected $fillable = ['nama', 'nim', 'file', 'keterangan'];
}