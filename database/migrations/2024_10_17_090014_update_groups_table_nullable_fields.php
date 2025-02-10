<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGroupsTableNullableFields extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            // Make the group_table_name and groupusers_table_name nullable
            $table->string('group_table_name')->nullable()->change();
            $table->string('groupusers_table_name')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            // Revert to non-nullable
            $table->string('group_table_name')->nullable(false)->change();
            $table->string('groupusers_table_name')->nullable(false)->change();
        });
    }
}

