<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->string('id', 22)->primary(); // Define id as varchar(22) and set it as primary key
            $table->string('group_name', 256);
            $table->string('group_table_name', 256);
            $table->string('groupusers_table_name', 256);
            $table->enum('type', ['business', 'non-business', 'community']);
            $table->string('purpose', 500);
            $table->string('category1', 256)->nullable();
            $table->string('category2', 256)->nullable();
            $table->string('category3', 256)->nullable();
            $table->string('profile_image', 256)->nullable();
            $table->dateTime('last_message_time')->nullable();
            $table->string('last_sender', 256)->nullable();
            $table->dateTime('last_send_time')->nullable();
            $table->integer('member_count')->default(0);
            $table->integer('plan_id')->nullable();
            $table->dateTime('expire_date')->nullable();
            $table->string('created_by', 22);
            $table->string('mobile_number', 256)->nullable();
            $table->string('alternative_number', 256)->nullable();
            $table->string('whatsapp_number', 256)->nullable();
            $table->string('timings', 256)->nullable();
            $table->string('contact_time', 256)->nullable();
            $table->string('holidays', 256)->nullable();
            $table->string('website_link', 256)->nullable();
            $table->string('youtube_link', 256)->nullable();
            $table->string('googlemap_link', 256)->nullable();
            $table->timestamps(); // created_at and updated_at
            $table->enum('status', [0, 1, 2]); // 0: inactive, 1: active, 2: archived
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
