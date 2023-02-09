<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
       'procurement_id', 'supplier_id', 'route', 'requisition_status_id', 'current_user_id', 'created_user_id',
    ];
    //
    public function procurement()
    {
       return $this->belongsTo(Procurement::class);
    }
    public function purchaseItems()
    {
       return $this->hasMany(ProcurementItem::class);
    }
    public function supplier()
    {
       return $this->belongsTo(Supplier::class);
    }
    public function order()
    {
       return $this->hasOne(Order::class);
    }
    public function voucher()
    {
        return $this->hasOne(Voucher::class);
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
    public function delegations()
    {
        return $this->morphMany(Delegation::class, 'delegationable');
    }
    public function canceled()
    {
        return $this->morphOne(Cancel::class, 'cancelable');
    }
    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }
    public function supplierEvaluation()
    {
       return $this->hasOne(SupplierEvaluation::class);
    }
}
