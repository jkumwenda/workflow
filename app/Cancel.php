<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cancel extends Model
{
    protected $fillable = [
        'comment', 'created_user_id'
    ];
    //
    public function cancelable()
    {
        return $this->morphTo();
    }
    public function user()
    {
       return $this->belongsTo(User::class, 'created_user_id');
    }
}
