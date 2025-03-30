<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['username', 'password', 'role'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define a one-to-one relationship with the Mahasiswa model.
     */
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'username', 'username');
    }

    /**
     * Define a one-to-one relationship with the Konselor model.
     */
    public function konselor()
    {
        return $this->hasOne(Konselor::class, 'username', 'username');
    }

    /**
     * Define a one-to-one relationship with the Kemahasiswaan model.
     */
    public function kemahasiswaan()
    {
        return $this->hasOne(Kemahasiswaan::class, 'username', 'username');
    }

    /**
     * Define a one-to-one relationship with the Keasramaan model.
     */
    public function keasramaan()
    {
        return $this->hasOne(Keasramaan::class, 'username', 'username');
    }

    /**
     * Define a one-to-one relationship with the Dosen model.
     */
    public function dosen()
    {
        return $this->hasOne(Dosen::class, 'username', 'username');
    }

    /**
     * Define a one-to-one relationship with the OrangTua model.
     */
    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'username', 'username');
    }
}