<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    // Table name (optional, specify only if not following Laravel's naming convention)
    protected $table = 'states';

    // Fillable properties for mass assignment
    protected $fillable = ['id','state_name', 'status'];
}
