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
        Schema::table('custom_items', function (Blueprint $table) {
            $table->dateTime('rental_start_date')->nullable()->after('status');
            $table->dateTime('rental_end_date')->nullable()->after('rental_start_date');
            $table->integer('days')->nullable()->after('rental_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_items', function (Blueprint $table) {
            //
        });
    }
};
