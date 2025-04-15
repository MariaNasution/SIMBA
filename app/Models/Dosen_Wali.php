<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen_Wali extends Model
{

    use SoftDeletes;
    
    protected $table = 'dosen_wali'; // Specify the correct table name
    protected $primaryKey = 'username'; // Set primary key
    public $incrementing = false; // Disable auto-increment

    // Define fillable fields
    protected $fillable = ['username', 'kelas', 'angkatan'];

    /**
     * Define the inverse relationship with the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function kelas()
    {

        return $this->belongsTo(Dosen::class, 'kelas', 'kelas');
 
    }
}
