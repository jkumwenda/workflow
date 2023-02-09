<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'messageable_type', 'messageable_id', 'questioner_user_id', 'receiver_user_id', 'question'
    ];
    //
    public function messageable()
    {
        return $this->morphTo();
    }
    public function questioner()
    {
        return $this->belongsTo(User::class, 'questioner_user_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
    public function answerer()
    {
        return $this->belongsTo(User::class, 'answerer_user_id');
    }
}
