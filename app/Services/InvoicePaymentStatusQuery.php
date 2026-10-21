<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InvoicePaymentStatusQuery
{
    public function supportsSql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    /**
     * Apply the same payment-status rules as Invoice::getPaymentStatusAttribute,
     * but let MySQL filter before Eloquent hydrates the invoice relations.
     */
    public function applyStatus(Builder $query, string $status, string $categoryName): Builder
    {
        [$finalTotal, $paidAmount] = $this->amountExpressions($categoryName);
        $difference = "({$finalTotal} - {$paidAmount})";

        return match ($status) {
            'fully_paid' => $query->whereRaw("{$difference} <= 1"),
            'unpaid' => $query->whereRaw("{$paidAmount} <= 0 AND {$difference} > 1"),
            'partially_paid' => $query->whereRaw("{$paidAmount} > 0 AND {$difference} > 1"),
            default => $query,
        };
    }

    /**
     * Expose the SQL expressions used to calculate payment status so list
     * pages can render the status without hydrating every invoice relation.
     *
     * @return array{string,string}
     */
    public function statusExpressions(string $categoryName): array
    {
        return $this->amountExpressions($categoryName);
    }

    /**
     * Return dashboard counts in one aggregate query.
     *
     * @return array{fully_paid:int, partially_paid:int, unpaid:int, overdue:int}|null
     */
    public function counts(int $categoryId, string $categoryName): ?array
    {
        if (!$this->supportsSql()) {
            return null;
        }

        [$finalTotal, $paidAmount] = $this->amountExpressions($categoryName);
        $invoiceAmounts = Invoice::query()
            ->where('category_id', $categoryId)
            ->select(['invoices.id', 'invoices.rental_end_date'])
            ->selectRaw("{$finalTotal} AS final_total")
            ->selectRaw("{$paidAmount} AS paid_amount");

        // Calculate each invoice's totals once. The previous query repeated
        // the correlated item, return, and payment subqueries for every CASE
        // expression, which made dashboard loads grow sharply with invoice
        // volume.
        $row = DB::query()
            ->fromSub($invoiceAmounts, 'invoice_amounts')
            ->selectRaw('SUM(CASE WHEN (final_total - paid_amount) <= 1 THEN 1 ELSE 0 END) AS fully_paid')
            ->selectRaw('SUM(CASE WHEN paid_amount > 0 AND (final_total - paid_amount) > 1 THEN 1 ELSE 0 END) AS partially_paid')
            ->selectRaw('SUM(CASE WHEN paid_amount <= 0 AND (final_total - paid_amount) > 1 THEN 1 ELSE 0 END) AS unpaid')
            ->selectRaw('SUM(CASE WHEN rental_end_date < ? AND (final_total - paid_amount) > 1 THEN 1 ELSE 0 END) AS overdue', [now()])
            ->first();

        return [
            'fully_paid' => (int) ($row->fully_paid ?? 0),
            'partially_paid' => (int) ($row->partially_paid ?? 0),
            'unpaid' => (int) ($row->unpaid ?? 0),
            'overdue' => (int) ($row->overdue ?? 0),
        ];
    }

    /**
     * Sum unpaid balances in SQL instead of hydrating every invoice and all of
     * its item, payment, and return relations in PHP.
     */
    public function unpaidBalanceSum(
        int $categoryId,
        string $categoryName,
        ?int $userId = null,
        ?callable $filter = null
    ): ?float {
        if (!$this->supportsSql()) {
            return null;
        }

        [$finalTotal, $paidAmount] = $this->amountExpressions($categoryName);
        $difference = "({$finalTotal} - {$paidAmount})";

        $query = Invoice::query()
            ->where('category_id', $categoryId)
            ->when($userId !== null, fn (Builder $query) => $query->where('user_id', $userId));

        if ($filter !== null) {
            $filter($query);
        }

        return (float) ($query
            ->selectRaw("COALESCE(SUM(GREATEST(0, {$difference})), 0) AS total_unpaid")
            ->value('total_unpaid') ?? 0);
    }

    /**
     * Build the final-total and payments expressions used by the model accessor.
     * These are intentionally MySQL-specific and are only used when supportsSql()
     * is true; SQLite and other drivers retain the existing model-based fallback.
     *
     * @return array{string,string}
     */
    private function amountExpressions(string $categoryName): array
    {
        $dailyDays = static function (string $table): string {
            return "CASE WHEN {$table}.rental_start_date IS NOT NULL AND {$table}.rental_end_date IS NOT NULL " .
                "THEN DATEDIFF({$table}.rental_end_date, {$table}.rental_start_date) + 1 " .
                "ELSE COALESCE({$table}.days, 1) END";
        };

        $days = $categoryName === 'season'
            ? '1'
            : null;

        $invoiceItemDays = $days ?? $dailyDays('ii');
        $customItemDays = $days ?? $dailyDays('ci');
        $additionalItemDays = $days ?? $dailyDays('ai');

        $invoiceItemsTotal = "(SELECT COALESCE(SUM(ii.price * ii.quantity * {$invoiceItemDays}), 0) " .
            "FROM invoice_items ii WHERE ii.invoice_id = invoices.id AND ii.deleted_at IS NULL)";
        $customItemsTotal = "(SELECT COALESCE(SUM(ci.price * ci.quantity * {$customItemDays}), 0) " .
            "FROM custom_items ci WHERE ci.invoice_id = invoices.id)";
        $additionalItemsTotal = "(SELECT COALESCE(SUM(ai.price * ai.quantity * {$additionalItemDays}), 0) " .
            "FROM additional_items ai WHERE ai.invoice_id = invoices.id)";

        $subtotal = "({$invoiceItemsTotal} + {$customItemsTotal})";
        $discount = "({$subtotal} * COALESCE(invoices.total_discount, 0) / 100)";

        $invoiceItemRefund = $this->refundExpression(
            'invoice_items',
            'ii',
            $categoryName === 'season' ? '1' : $dailyDays('ii'),
            'rd.invoice_item_id = ii.id'
        );
        $additionalItemRefund = $this->refundExpression(
            'additional_items',
            'ai',
            $categoryName === 'season' ? '1' : $dailyDays('ai'),
            'rd.additional_item_id = ai.id'
        );
        $customItemRefund = $this->refundExpression(
            'custom_items',
            'ci',
            $categoryName === 'season' ? '1' : $dailyDays('ci'),
            'rd.custom_item_id = ci.id'
        );

        $refund = "({$invoiceItemRefund} + {$additionalItemRefund} + {$customItemRefund})";
        $finalTotal = "({$subtotal} + {$additionalItemsTotal} - {$discount} - {$refund} - COALESCE(invoices.deposit, 0))";
        $paidAmount = '(SELECT COALESCE(SUM(ip.amount), 0) FROM invoice_payments ip WHERE ip.invoice_id = invoices.id)';

        return [$finalTotal, $paidAmount];
    }

    private function refundExpression(string $itemTable, string $itemAlias, string $daysExpression, string $joinCondition): string
    {
        return "(SELECT COALESCE(SUM(GREATEST(0, ({$daysExpression}) - rd.days_used) * " .
            "rd.returned_quantity * {$itemAlias}.price), 0) " .
            "FROM return_details rd JOIN {$itemTable} {$itemAlias} ON {$joinCondition} " .
            "WHERE rd.invoice_id = invoices.id" .
            ($itemTable === 'invoice_items' ? " AND {$itemAlias}.deleted_at IS NULL" : '') . ")";
    }
}
