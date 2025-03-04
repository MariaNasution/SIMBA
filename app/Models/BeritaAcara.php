<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    
    protected $table = 'berita_acara'; // Explicitly specify the table name
    protected $primaryKey = 'ID_BeritaAcara'; // Integer primary key (auto-incrementing)

    protected $fillable = ['ID_BeritaAcara', 'PesanBeritaAcara', 'nama'];
}
