<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FlowDetail extends Model
{
    protected $fillable = [
        'level', 'role_id', 'requisition_status_id'
    ];
    //
    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
    public function requisitionStatus()
    {
        return $this->belongsTo(RequisitionStatus::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
