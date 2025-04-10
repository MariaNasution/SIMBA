<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; // Tambahkan trait Notifiable

class Keasramaan extends Model
{
    use Notifiable;

    protected $table = 'keasramaan';
    protected $primaryKey = 'username';
    public $incrementing = false;

    protected $fillable = ['username', 'nip'];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
