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
        Schema::create('custom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade'); // Links to the invoices table
            $table->string('name'); // Custom item name
            $table->string('description')->nullable(); // Custom item description
            $table->decimal('price', 10, 2); // Price per unit
            $table->integer('quantity'); // Quantity
            $table->integer('returned_quantity')->default(0);
            $table->enum('status', ['draft', 'active', 'returned', 'overdue'])->default('active'); // Rental status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_items');
    }
};
