<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    // Define the table name (optional if following Laravel's naming convention)
    protected $table = 'plans';

    // Specify the attributes that can be mass assigned
    protected $fillable = [
        'id',
        'plan_name',
        'amount',
        'duration',
        'tax',
        'total_members',
        'status',
    ];

    
}
