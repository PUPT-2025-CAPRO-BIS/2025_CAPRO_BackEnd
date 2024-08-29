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
        Schema::create('blotter_reports', function (Blueprint $table) {
            $table->id();
            $table->string('complainee_name');
            $table->string('complainant_name');
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
        Schema::dropIfExists('blotter_reports');
    }
};