<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas',
        'angkatan',
        'dosen_wali',
        'tanggal_perwalian',
        'perihal_perwalian',
        'agenda_perwalian',
        'keterangan',
        'hari_tanggal_feedback',
        'perihal_feedback', // ✅ Tambahkan ini jika belum ada
        'catatan_feedback',
        'tanggal_ttd',
        'dosen_wali_ttd',
        'user_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
