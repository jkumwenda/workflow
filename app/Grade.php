<?php

namespace App;

use App\Unit;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','subsistence','lunch','international',
    ];
   
    public function users()
    {
        return $this->hasMany(User::class, 'grade_id');
    }

}
