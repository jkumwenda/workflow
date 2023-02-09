<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    protected $fillable = [
        'item_id', 'quantity', 'uom', 'description',
    ];
    //
    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
