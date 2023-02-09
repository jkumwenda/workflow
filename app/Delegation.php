<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delegation extends Model
{
    protected $fillable = [
        'status', 'sender_user_id', 'receiver_user_id', 'requisition_status_id', 'comment'
    ];
    //
    public function delegationable()
    {
        return $this->morphTo();
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }
}
