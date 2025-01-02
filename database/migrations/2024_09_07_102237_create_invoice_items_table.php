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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity'); // Quantity of the product
            $table->decimal('price', 10, 2); // Price per unit
            $table->decimal('total_price', 10, 2); // Total price after VAT and discount
            $table->datetime('rental_start_date')->nullable(); // Start of the rental period for this item
            $table->datetime('rental_end_date')->nullable(); // End of the rental period for this item
            $table->integer('days')->nullable();
            $table->integer('returned_quantity')->default(0);
            $table->integer('added_quantity')->default(0);
            $table->enum('status', ['draft', 'active', 'returned', 'overdue'])->default('active'); // Rental status
            $table->boolean('is_additional')->default(false);
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Add this line to enable soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
