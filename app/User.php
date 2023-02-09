<?php

namespace App;

use App\Grade;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'first_name',
        'surname',
        'salutation',
        'email',
        'signature',
        'active',
        'campus_id',
        'grade_id',
        'phone_number',
        //'role_id',
        //'unit_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot('unit_id', 'is_default');
    }
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'role_user')->withPivot('role_id', 'is_default');
    }
    public function campus()
    {
       return $this->belongsTo(Campus::class);
    }
    public function grade()
    {
       return $this->belongsTo(Grade::class);
    }
    public function priority()
    {
       return $this->hasOne(Priority::class);
    }
    public function getFullNameAttribute()
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->surname);
    }

    /**
     * Name
     * (Original Get Accessor)
     *
     * @author Sayuri.Tsuboi
     */
    public function getNameAttribute()
    {
        return $this->first_name . " " . $this->surname;
    }
}
