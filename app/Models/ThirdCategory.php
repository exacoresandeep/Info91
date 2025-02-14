<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirdCategory extends Model
{
    use HasFactory;

    protected $table = 'third_categories';  // Specify table name
    protected $fillable = ['third_category_name', 'second_category_id']; 
    // Relationship: A third category belongs to a second category
    public function secondCategory()
    {
        return $this->belongsTo(SecondCategory::class, 'second_category_id');
    }
   
}
