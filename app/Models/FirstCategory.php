<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirstCategory extends Model
{
    use HasFactory;

    protected $table = 'first_categories';  // Specify table name if it's not plural by default
    protected $fillable = ['first_category_name', 'status'];
    public function secondCategories()
    {
        return $this->hasMany(SecondCategory::class, 'first_category_id');
    }
}
