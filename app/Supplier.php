<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address'
    ];
    protected $guarded = [
        'id'
     ];
    public function purchases()
    {
       return $this->hasMany(Purchase::class);
    }
    public function supplierEvaluations()
    {
        return $this->hasMany(SupplierEvaluation::class);
    }
}
