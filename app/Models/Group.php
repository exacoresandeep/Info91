<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    // Specify the primary key type
    protected $keyType = 'string'; // Indicate that the primary key is a string

    // Disable auto-incrementing
    public $incrementing = false; // Set to false since we are using a varchar as an ID
    
    protected $fillable = [
        'id', // Include id in fillable to allow mass assignment
        'group_name',
        'group_table_name',
        'groupusers_table_name',
        'type',
        'purpose',
        'address',
        'category1',
        'category2',
        'category3',
        'profile_image',
        'last_message_time',
        'last_sender',
        'last_send_time',
        'member_count',
        'plan_id',
        'expire_date',
        'created_by',
        'mobile_number',
        'alternative_number',
        'whatsapp_number',
        'timings',
        'contact_time',
        'holidays',
        'website_link',
        'youtube_link',
        'googlemap_link',
        'email',
        'tag_key_1',
        'tag_key_2',
        'tag_key_3',
        'jointype',
        'status',
    ];
    protected $appends = ['formatted_created_at'];

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d-m-Y') : null;
    }
    public function firstCategory()
    {
        return $this->belongsTo(FirstCategory::class, 'category1');
    }

    public function secondCategory()
    {
        return $this->belongsTo(SecondCategory::class, 'category2');
    }

    public function thirdCategory()
    {
        return $this->belongsTo(ThirdCategory::class, 'category3');
    }

    // Relationship with the plans table
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
