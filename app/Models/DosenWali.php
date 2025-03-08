<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DosenWali extends Model
{
    use HasFactory, SoftDeletes; // Add soft deletes for safely removing records

    protected $table = 'dosen_wali'; // Explicitly specify the table name
    protected $primaryKey = 'ID_Dosen_Wali'; // Integer primary key (auto-incrementing)

    protected $fillable = ['ID_Dosen_Wali', 'nama'];

    /**
     * Relationship with Mahasiswa model.
     * Each DosenWali may advise multiple students (Mahasiswa).
     */

}