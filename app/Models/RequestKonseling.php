<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestKonseling extends Model
{
    use HasFactory;

    protected $table = 'request_konselings';

    protected $fillable = [
        'nim',
        'nama_mahasiswa',
        'tanggal_pengajuan',
        'deskripsi_pengajuan',
        'status',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }
}
