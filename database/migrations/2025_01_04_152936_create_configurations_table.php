<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('key_name')->unique(); // Key name (e.g., max_appointments_per_day)
            $table->string('key_value');         // Key value (e.g., 5, 10, etc.)
            $table->timestamps();               // created_at, updated_at
        });

        // Insert default slot limit
        DB::table('configurations')->insert([
            'key_name' => 'max_appointments_per_day',
            'key_value' => '5', // Default slot limit
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configurations');
    }
}
