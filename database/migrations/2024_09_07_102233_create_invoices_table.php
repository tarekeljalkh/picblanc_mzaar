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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->after('id');
            $table->decimal('total_vat', 5, 2)->default(0); // VAT percentage
            $table->decimal('total_discount', 5, 2)->default(0); // Discount percentage
            $table->decimal('deposit', 10, 2)->default(0); // Deposit amount
            $table->enum('status', ['draft', 'active', 'returned', 'overdue'])->default('active'); // Rental status
            $table->datetime('rental_start_date')->nullable(); // Start of the rental period
            $table->datetime('rental_end_date')->nullable(); // End of the rental period
            $table->integer('days')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
