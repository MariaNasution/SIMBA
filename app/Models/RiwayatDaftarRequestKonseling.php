<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatDaftarRequestKonseling extends Model
{
    use HasFactory;

    protected $table = 'riwayat_daftar_request_konseling';

    protected $fillable = [
        'id',
        'nim',
        'nama_mahasiswa',
        'tanggal_pengajuan',
        'deskripsi_pengajuan',
        'alasan_penolakan',
        'status',
    ];
}
