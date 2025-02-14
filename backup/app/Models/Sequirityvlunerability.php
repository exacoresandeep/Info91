<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequirityvlunerability extends Model
{
    use HasFactory;
    protected $table="sequirityvlunerability";
    protected $fillable = [
        'user_id',
        'random_string'
    ];
}
