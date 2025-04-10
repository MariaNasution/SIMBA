<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; // Tambahkan trait Notifiable

class OrangTua extends Model
{
    use Notifiable;

    protected $table = 'orang_tua';
    protected $primaryKey = 'username';
    public $incrementing = false;

    protected $fillable = ['username', 'nim', 'no_hp'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
