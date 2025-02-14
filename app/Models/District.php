<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\State;
use App\Models\Pincode;


class District extends Model
{
    use HasFactory;
    protected $table = 'districts';  
    protected $fillable = ['district_name', 'state_id','id'];
    
    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function pincode()
    {
        return $this->hasMany(Pincode::class, 'district_id');
    }
    
}
