<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trail extends Model
{
    protected $fillable = [
        'flow_id', 'flow_detail_id', 'user_id', 'status', 'comment',
    ];
    //
    public function trailable()
    {
        return $this->morphTo();
    }
    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
    public function flowDetail()
    {
        return $this->belongsTo(FlowDetail::class);
    }
    public function requisitionStatus()
    {
       return $this->belongsTo(RequisitionStatus::class);
    }
    public function user()
    {
       return $this->belongsTo(User::class);
    }
}
