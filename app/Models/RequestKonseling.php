<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestKonseling extends Model
{
    use HasFactory;

    protected $table = 'request_konseling';
    protected $fillable = ['tanggal_pengajuan', 'deskripsi_pengajuan', 'status'];
}