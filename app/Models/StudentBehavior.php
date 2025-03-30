<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBehavior extends Model
{
    protected $table = 'student_behaviors';

    protected $primaryKey = 'id';
    protected $fillable = [
        'student_nim',
        'ta',
        'semester',
        'type',
        'description',
        'unit',
        'tanggal',
        'poin',
        'tindakan',
    ];
}
