<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $fillable = [
        'name',
    ];
    //
    public function vehicle()
    {
       return $this->hasMany(Vehicle::class, 'vehicle_type_id','id');
    }




}
