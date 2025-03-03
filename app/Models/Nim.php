<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nim extends Model
{
  use HasFactory;

  protected $table = 'nim'; // Sesuai dengan nama tabel di database

  protected $fillable = ['nim', 'nama']; // Pastikan hanya kolom 'nim' yang dapat diisi
}

