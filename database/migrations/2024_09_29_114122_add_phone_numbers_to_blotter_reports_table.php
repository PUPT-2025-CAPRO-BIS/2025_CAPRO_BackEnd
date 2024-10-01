<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->string('complainant_phone_number')->nullable()->after('complainant_name');
            $table->string('complainee_phone_number')->nullable()->after('complainee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blotter_reports', function (Blueprint $table) {
            $table->dropColumn('complainant_phone_number');
            $table->dropColumn('complainee_phone_number');
        });
    }
};
