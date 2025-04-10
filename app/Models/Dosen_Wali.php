<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Dosen_Wali extends Model
{

    use SoftDeletes;
    use Notifiable;
    
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
