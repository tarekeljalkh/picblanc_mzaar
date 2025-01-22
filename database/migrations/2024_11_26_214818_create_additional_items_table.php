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
        Schema::create('additional_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->integer('returned_quantity')->default(0);
            $table->decimal('price', 10, 2);
            $table->integer('days')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->datetime('rental_start_date')->nullable();
            $table->datetime('rental_end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'returned', 'overdue'])->default('active'); // Rental status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_items');
    }
};
