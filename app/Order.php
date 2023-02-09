<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'purchase_id', 'po_number', 'requisition_status_id', 'created_user_id', 'current_user_id'
    ];
    //
    public function purchase()
    {
       return $this->belongsTo(Purchase::class);
    }
    public function requisitionStatus()
    {
       return $this->belongsTo(RequisitionStatus::class);
    }
    public function currentUser()
    {
       return $this->belongsTo(User::class, 'current_user_id');
    }
    public function trails()
    {
        return $this->morphMany(Trail::class, 'trailable');
    }
}
