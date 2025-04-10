<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; // Tambahkan trait Notifiable

class Konselor extends Model
{
    use Notifiable;

    protected $table = 'konselor';
    protected $primaryKey = 'username';
    public $incrementing = false;

    protected $fillable = ['username', 'nip'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
