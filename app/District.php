<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function campus()
    {
        return $this->hasMany(Campus::class);
    }
   
    

}
