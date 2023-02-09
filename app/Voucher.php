<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
       'purchase_id', 'total_amount', 'requisition_status_id', 'assigned_accountant_user_id', 'created_user_id', 'current_user_id'
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
    public function assistantAccountant()
    {
       return $this->belongsTo(User::class, 'assistant_account_user_id');
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

    /**
     * taxable
     * (Original Get Accessor)
     *
     * @author Sayuri.Tsuboi
     */
    public function getTaxableAttribute()
    {

      //voucher information
      $taxable = ($this->total_amount - $this->excepted_tax);
      return $taxable;
    }

    /**
     * taxedAmount
     * (Original Get Accessor)
     *
     * @author Sayuri.Tsuboi
     */
    public function getTaxedAmountAttribute()
    {

      //voucher information
      $taxedAmount = ($this->taxable * ($this->tax_applied/100));
      return $taxedAmount;
    }
}
