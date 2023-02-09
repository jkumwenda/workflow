<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $table = 'priority';

    protected $fillable = [
        'module_id', 'role_id','user_id',
    ];

    public function role()
    {
        return $this->hasOne(Role::class, 'id','role_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
