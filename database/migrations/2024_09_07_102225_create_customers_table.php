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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');         // Customer name
            $table->string('phone')->nullable();        // Phone number
            $table->string('phone2')->nullable();        // Phone number
            $table->string('address')->nullable();
            $table->string('deposit_card')->nullable(); // Deposit card (optional)
            $table->timestamps();

            // Unique constraints to ensure phone and phone2 uniqueness
            $table->unique(['phone', 'phone2']); // Combined unique
            $table->unique(['phone']);           // Unique phone1
            $table->unique(['phone2']);          // Unique phone2

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
