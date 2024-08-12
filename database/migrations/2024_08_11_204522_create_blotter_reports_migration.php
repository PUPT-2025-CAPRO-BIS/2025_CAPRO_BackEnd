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
        Schema::create('blotter_reports_migration', function (Blueprint $table) {
            $table->id();
            $table->integer('complainee_id');
            $table->integer('complainant_id');
            $table->integer('admin_id');
            $table->longText('complaint_file')->nullable();
            $table->text('complaint_remarks');
            $table->boolean('status_resolved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotter_reports_migration');
    }
};
