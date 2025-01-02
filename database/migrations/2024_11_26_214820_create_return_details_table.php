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
        Schema::create('return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('additional_item_id')->nullable()->constrained('additional_items')->onDelete('cascade');
            $table->foreignId('custom_item_id')->nullable()->constrained('custom_items')->onDelete('cascade');
            $table->integer('returned_quantity');
            $table->integer('days_used');
            $table->decimal('cost', 10, 2);
            $table->decimal('refund', 10, 2)->default(0);
            $table->datetime('return_date');
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('invoice_id');
            $table->index('invoice_item_id');
            $table->index('custom_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_details');
    }
};
