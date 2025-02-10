<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivationToken extends Model
{
    use HasFactory;

    protected $table = 'activation_tokens'; // Nama tabel di database

    protected $fillable = [
        'nim',
        'email',
        'password',
        'token',
    ];
}
