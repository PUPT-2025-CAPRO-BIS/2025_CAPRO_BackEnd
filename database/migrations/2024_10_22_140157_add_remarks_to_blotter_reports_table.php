<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToBlotterReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->string('remarks')->nullable();  // Add remarks column
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->dropColumn('remarks');  // Remove remarks column
        });
    }
}
