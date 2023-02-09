<?php

namespace App;

use App\Campus;
use App\District;
use App\Traveller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Travel extends Model
{
    use HasFactory;

    protected $table = 'travels';

    protected $fillable = [
        'procurement_id', 'requisition_status_id','vehicle_type_id','vehicle_id','driver_id','purpose','datetime_in','datetime_out','origin','destination', 'created_user_id', 'current_user_id',
    ];

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function travellers()
    {
       return $this->hasMany(Traveller::class);
    }

    public function transport()
    {
        return $this->hasOne(Transport::class);
    }

    public function subsistence()
    {
        return $this->hasMany(Subsistence::class);
    }

    public function createdUser()
    {
       return $this->belongsTo(User::class, 'created_user_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
    
    public function campus()
    {
       return $this->belongsTo(Campus::class,'origin');
    }

    public function district()
    {
       return $this->belongsTo(District::class,'destination');
    }

    public function user()
    {
       return $this->belongsTo(User::class);
    }

    public function driver()
    {
       return $this->belongsTo(User::class,'driver_id');
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
    public function changeLogs()
    {
        return $this->morphMany(ChangeLog::class, 'changeable');
    }
    public function requisitionStatus()
    {
       return $this->belongsTo(RequisitionStatus::class);
    }

}
