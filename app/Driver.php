<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'user_id', 'campus_id'
    ];
    //
    public function campus()
    {
       return $this->belongsTo(Campus::class);
    }

    public function user()
    {
       return $this->belongsTo(User::class);
    }

    // public function trips()
    // {
    //     return $this->hasMany(Trip::class);
    // }
}
