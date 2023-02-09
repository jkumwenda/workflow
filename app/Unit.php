<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'
    ];
    //
    public function users()
    {
       return $this->belongsToMany(User::class, 'role_user')->withPivot('role_id', 'is_default');
   }
    public function company()
    {
       return $this->belongsTo(Company::class);
    }

    public function Vehicle()
    {
       return $this->hasMany(Vehicle::class);
    }


}
