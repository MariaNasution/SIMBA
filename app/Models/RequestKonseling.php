<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestKonseling extends Model
{
    use HasFactory;

    protected $table = 'request_konseling';

    protected $fillable = [
        'nim',
        'tanggal_pengajuan',
        'deskripsi_pengajuan',
        'status',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }
}
