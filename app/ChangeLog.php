<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $fillable = [
        'crud', 'information', 'changes', 'user_id'
    ];
    protected $casts = [
        'information' => 'array',
        'changes' => 'array'
    ];
    //
    public function changeable()
    {
        return $this->morphTo();
    }
    public function user()
    {
       return $this->belongsTo(User::class, 'user_id');
    }


}
