<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = ['id'
    ];
    //
    public function users()
    {
       return $this->belongsToMany(User::class)->withPivot('unit_id', 'is_default');
    }
    public function permissions()
    {
       return $this->belongsToMany(Permission::class);
    }

}
