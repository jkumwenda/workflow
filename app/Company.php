<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
    ];
    //
    public function units()
    {
       return $this->hasMany(Unit::class);
    }

    public function flows()
    {
        return $this->hasMany(Flow::class);
    }

}
