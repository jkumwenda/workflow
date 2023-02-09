<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $guarded = ['id'
    ];
    protected $fillable = [
        'make',
        'registration_number',
        'mileage',
        'vehicle_type_id',
        'capacity',
        'colour',
        'unit_id',
        'make_id',
        'campus_id'
     ];
    //

    public function vehicleType()
    {
       return $this->belongsTo(VehicleType::class);
    }
    public function unit()
    {

       return $this->belongsTo(Unit::class);
    }
    public function campus()
    {
      return $this->belongsTo(Campus::class);
    }

    public function make()
    {
      return $this->belongsTo(Make::class);
    }
}
