<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToBlotterReportsTable extends Migration
{
    public function up()
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->string('category')->nullable(); // Add the category column
        });
    }

    public function down()
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->dropColumn('category'); // Remove the category column
        });
    }
};
