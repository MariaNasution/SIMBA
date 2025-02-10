<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;
    protected $fillable = ['email', 'nim', 'token', 'created_at', 'updated_at'];

    public $timestamps = true;
    protected $primaryKey = null;
    public $incrementing = false;
}
