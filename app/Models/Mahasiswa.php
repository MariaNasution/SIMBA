<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa'; // Use plural for consistency with Laravel convention
    protected $primaryKey = 'nim'; // String primary key
    public $incrementing = false; // Disable auto-increment

    protected $fillable = ['nim', 'username', 'nama', 'kelas', 'ID_Dosen', 'ID_Perwalian'];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username'); // Relationship to User model
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'ID_Dosen', 'id'); // Relationship to Dosen model
    }

    public function perwalian()
    {
        return $this->belongsTo(Perwalian::class, 'ID_Perwalian', 'ID_Perwalian'); // Relationship to Perwalian model
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'nim', 'nim'); // Relationship to Notifikasi model (if applicable)
    }
}