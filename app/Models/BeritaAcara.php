<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $fillable = ['judul', 'deskripsi', 'tanggal', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
