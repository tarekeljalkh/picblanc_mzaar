<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = ['total_amount', 'paid_amount', 'paid', 'total', 'amount_per_day', 'payment_method'];
        $missing = array_fill_keys(array_filter($columns, fn(string $column) => !Schema::hasColumn('invoices', $column)), true);

        if ($missing === []) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) use ($missing): void {
            if (isset($missing['total_amount'])) {
                $table->decimal('total_amount', 10, 2)->default(0);
            }
            if (isset($missing['paid_amount'])) {
                $table->decimal('paid_amount', 10, 2)->default(0);
            }
            if (isset($missing['paid'])) {
                $table->boolean('paid')->default(false);
            }
            if (isset($missing['total'])) {
                $table->decimal('total', 10, 2)->default(0);
            }
            if (isset($missing['amount_per_day'])) {
                $table->decimal('amount_per_day', 10, 2)->nullable();
            }
            if (isset($missing['payment_method'])) {
                $table->string('payment_method', 32)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep these compatibility columns on rollback: they may have existed before this migration.
    }
};
