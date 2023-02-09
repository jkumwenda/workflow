<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subsistence extends Model
{

   protected $fillable = [
      'travel_id', 'requisition_status_id','created_user_id','current_user_id',
  ];
    public function travel()
    {
       return $this->belongsTo(Travel::class);
    }

    public function travellers()
    {
       return $this->hasMany(Traveller::class);
    }

    public function requisitionStatus()
    {  
       return $this->belongsTo(RequisitionStatus::class);
    }
    public function currentUser()
    {
       return $this->belongsTo(User::class, 'current_user_id');
    }
    public function createdUser()
    {
       return $this->belongsTo(User::class, 'created_user_id');
    }
    public function trails()
    {
        return $this->morphMany(Trail::class, 'trailable');
    }

    public function canceled()
    {
        return $this->morphOne(Cancel::class, 'cancelable');
    }
    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }

}
