<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierEvaluation extends Model
{
    protected $guarded = [
        'id'
     ];
    protected $fillable = [
        'supplier_id', 'purchase_id', 'score', 'comment', 'created_user_id',
    ];
    //
    public function purchase()
    {
       return $this->belongsTo(Purchase::class);
    }
    public function supplier()
    {
       return $this->belongsTo(Supplier::class);
    }
    public function createdUser()
    {
       return $this->belongsTo(User::class, 'created_user_id');
    }
}
