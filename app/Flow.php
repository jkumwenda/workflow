<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flow extends Model
{
    protected $fillable = [
    ];
    //
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function flowDetails()
    {
        return $this->hasMany(FlowDetail::class);
    }
}
