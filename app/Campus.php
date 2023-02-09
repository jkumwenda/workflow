<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $fillable = [
        'name','district_id',
    ];
    public function user()
    {
       return $this->hasMany(User::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    public function vehicle()
    {
        return $this->hasMany(Vehicle::class);
    }
}
