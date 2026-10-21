<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->index(['category_id', 'id'], 'invoices_category_id_id_index');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->index(
                ['invoice_id', 'quantity', 'returned_quantity'],
                'invoice_items_return_filter_index'
            );
        });

        Schema::table('additional_items', function (Blueprint $table): void {
            $table->index(
                ['invoice_id', 'quantity', 'returned_quantity'],
                'additional_items_return_filter_index'
            );
        });

        Schema::table('custom_items', function (Blueprint $table): void {
            $table->index(
                ['invoice_id', 'quantity', 'returned_quantity'],
                'custom_items_return_filter_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('invoices_category_id_id_index');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->dropIndex('invoice_items_return_filter_index');
        });

        Schema::table('additional_items', function (Blueprint $table): void {
            $table->dropIndex('additional_items_return_filter_index');
        });

        Schema::table('custom_items', function (Blueprint $table): void {
            $table->dropIndex('custom_items_return_filter_index');
        });
    }
};
