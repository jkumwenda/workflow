<?php

namespace App;

use App\User;
use App\Travel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Traveller extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_id','user_id','created_user_id','current_user_id','amount','subsistence_id','voucher_id','departure_date','return_date','accomodation_provided','updated_at'
    ];
    
    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }

    public function subsistence(){
        return $this->belongsTo(Subsistence::class);
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
