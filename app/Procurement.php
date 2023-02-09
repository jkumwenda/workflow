<?php

namespace App;

use App\Travel;
use App\Traveller;
use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $fillable = [
        'title', 'unit_id', 'requisition_status_id', 'created_user_id', 'current_user_id',
    ];
    //
    public function procurementItems()
    {
       return $this->hasMany(ProcurementItem::class);
    }

    public function travel()
    {
        return $this->hasOne(Travel::class);
    }

    public function traveller()
    {
        return $this->hasMany(Traveller::class);
    }

    public function createdUser()
    {
       return $this->belongsTo(User::class, 'created_user_id');
    }
    public function unit()
    {
       return $this->belongsTo(Unit::class);
    }
    public function requisitionStatus()
    {
       return $this->belongsTo(RequisitionStatus::class);
    }
    public function currentUser()
    {
       return $this->belongsTo(User::class, 'current_user_id');
    }
    public function trails()
    {
        return $this->morphMany(Trail::class, 'trailable');
    }
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function delegations()
    {
        return $this->morphMany(Delegation::class, 'delegationable');
    }
    public function canceled()
    {
        return $this->morphOne(Cancel::class, 'cancelable');
    }
    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function changeLogs()
    {
        return $this->morphMany(ChangeLog::class, 'changeable');
    }


    protected static function boot()
    {
        parent::boot();

        // delete children
        static::deleting(function($model) {
            foreach (['procurementItems', 'trails', 'documents'] as $relation) {
                foreach ($model->$relation()->get() as $child) {
                    $child->delete();
                }
            }
        });
    }
}
