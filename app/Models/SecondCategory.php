<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondCategory extends Model
{
    use HasFactory;

    protected $table = 'second_categories';  // Specify table name
    protected $fillable = ['second_category_name', 'first_category_id'];
    // Relationship: A second category belongs to a first category
    public function firstCategory()
    {
        return $this->belongsTo(FirstCategory::class, 'first_category_id');
    }

    // Relationship: A second category can have many third categories
    public function thirdCategories()
    {
        return $this->hasMany(ThirdCategory::class, 'second_category_id');
    }
}
