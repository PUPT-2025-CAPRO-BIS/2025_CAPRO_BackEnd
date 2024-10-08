<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHouseAndLotDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('house_and_lot_ownership')->nullable();
            $table->string('living_with_owner')->nullable();
            $table->string('renting')->nullable();
            $table->string('relationship_to_owner')->nullable();
            $table->string('pet_details')->nullable();
            $table->string('pet_vaccination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'house_and_lot_ownership', 
                'living_with_owner', 
                'renting', 
                'relationship_to_owner', 
                'pet_details', 
                'pet_vaccination'
            ]);
        });
    }
}
