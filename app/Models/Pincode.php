<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\District;
use App\Models\State;

class Pincode extends Model
{
    use HasFactory;
    
    protected $table = 'pincode';  // Specify table name
    protected $fillable = ['pincode', 'postname','district_id','created_at','updated_at']; 
    
    protected $casts = [
        'pincode' => 'string',
    ];
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

}
