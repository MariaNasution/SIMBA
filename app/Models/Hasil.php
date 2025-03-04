<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hasil extends Model
{
    use HasFactory;

    protected $table = 'hasil'; // Nama tabel di database

    protected $fillable = [
        'nama',
        'nim',
        'file', // Menyimpan path file
        'keterangan',
    ];
}
