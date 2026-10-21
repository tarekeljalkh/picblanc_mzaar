<?php

namespace App\DataTables;

use App\Models\Invoice;
use App\Services\InvoicePaymentStatusQuery;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InvoicesDataTable extends DataTable
{
    /** Build rows for either the normal SQL path or calculated-status fallback. */
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->editColumn('rental_start_date', function (Invoice $invoice): string {
                return $invoice->rental_start_date?->format('d/m/Y') ?? '';
            })
            ->editColumn('rental_end_date', function (Invoice $invoice): string {
                return $invoice->rental_end_date?->format('d/m/Y') ?? '';
            })
            ->addColumn('customer_phone', function (Invoice $invoice): string {
                $phone = e($invoice->customer?->phone ?? '');
                $phone2 = e($invoice->customer?->phone2 ?? '');

                return $phone . ($phone2 !== '' ? '<br>' . $phone2 : '');
            })
            ->addColumn('payment_status', function (Invoice $invoice): string {
                $status = $this->paymentStatusForRow($invoice);
                $badgeClass = match ($status) {
                    'fully_paid' => 'bg-success',
                    'partially_paid' => 'bg-warning',
                    'unpaid' => 'bg-danger',
                    default => 'bg-secondary',
                };

                return '<span class="badge ' . $badgeClass . '">' .
                    e(ucfirst(str_replace('_', ' ', $status))) . '</span>';
            })
            ->addColumn('returned', function (Invoice $invoice): string {
                $returned = array_key_exists('calculated_returned', $invoice->getAttributes())
                    ? (bool) $invoice->calculated_returned
                    : $invoice->returned;

                return $returned
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('action', function (Invoice $invoice): string {
                $actions = '<a href="' . e(route('invoices.show', $invoice->id)) . '" class="btn btn-info btn-sm">Show</a> ' .
                    '<a href="' . e(route('invoices.edit', $invoice->id)) . '" class="btn btn-warning btn-sm">Edit</a> ' .
                    '<a href="' . e(route('invoices.print', $invoice->id)) . '" class="btn btn-primary btn-sm">Print</a>';

                if (auth()->user()?->role === 'admin') {
                    $actions .= ' <a href="' . e(route('invoices.destroy', $invoice->id)) . '" class="btn btn-danger btn-sm delete-item">Delete</a>';
                }

                return $actions;
            })
            ->rawColumns(['customer_phone', 'payment_status', 'returned', 'action'])
            ->setRowId('id');
    }

    /** Return a SQL builder for ordinary requests; retain the legacy calculated filters. */
    public function query(Invoice $model): QueryBuilder|Collection
    {
        $request = request();
        $selectedCategory = session('category', 'daily');
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $dateFilters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $paymentStatusQuery = app(InvoicePaymentStatusQuery::class);
        $supportsSqlStatuses = $paymentStatusQuery->supportsSql();

        $with = [
            'customer:id,name,phone,phone2',
            'category:id,name',
        ];

        // MySQL calculates the visible status columns in SQL. The full
        // relation graph is only needed by the non-MySQL compatibility path.
        if (!$supportsSqlStatuses) {
            $with = array_merge($with, [
                'invoiceItems:id,invoice_id,price,quantity,returned_quantity,rental_start_date,rental_end_date,days',
                'customItems:id,invoice_id,price,quantity,returned_quantity,rental_start_date,rental_end_date,days',
                'additionalItems:id,invoice_id,price,quantity,returned_quantity,rental_start_date,rental_end_date,days',
                'payments:id,invoice_id,amount',
                'returnDetails:id,invoice_id,invoice_item_id,additional_item_id,custom_item_id,returned_quantity,days_used',
                'returnDetails.invoiceItem:id,price,rental_start_date,rental_end_date,days',
                'returnDetails.additionalItem:id,price,rental_start_date,rental_end_date,days',
                'returnDetails.customItem:id,price,rental_start_date,rental_end_date,days',
            ]);
        }

        $query = $model->newQuery()
            ->with($with)
            ->select([
                'invoices.id',
                'invoices.customer_id',
                'invoices.category_id',
                'invoices.total_discount',
                'invoices.deposit',
                'invoices.status',
                'invoices.rental_start_date',
                'invoices.rental_end_date',
                'invoices.created_at',
            ])
            ->whereHas('category', function ($categoryQuery) use ($selectedCategory) {
                $categoryQuery->where('name', $selectedCategory);
            });

        if ($supportsSqlStatuses) {
            [$finalTotal, $paidAmount] = $paymentStatusQuery->statusExpressions($selectedCategory);
            $returnedExpression = <<<'SQL'
CASE WHEN
    NOT EXISTS (
        SELECT 1 FROM invoice_items ii_return
        WHERE ii_return.invoice_id = invoices.id
          AND ii_return.deleted_at IS NULL
          AND ii_return.quantity > ii_return.returned_quantity
    )
    AND NOT EXISTS (
        SELECT 1 FROM additional_items ai_return
        WHERE ai_return.invoice_id = invoices.id
          AND ai_return.quantity > ai_return.returned_quantity
    )
    AND NOT EXISTS (
        SELECT 1 FROM custom_items ci_return
        WHERE ci_return.invoice_id = invoices.id
          AND ci_return.quantity > ci_return.returned_quantity
    )
THEN 1 ELSE 0 END
SQL;

            $query->selectRaw("{$finalTotal} AS calculated_final_total")
                ->selectRaw("{$paidAmount} AS calculated_paid_amount")
                ->selectRaw("({$returnedExpression}) AS calculated_returned");
        }

        if (!empty($dateFilters['start_date']) || !empty($dateFilters['end_date'])) {
            if ($selectedCategory === 'season') {
                if (!empty($dateFilters['start_date'])) {
                    $query->where('created_at', '>=', $dateFilters['start_date'] . ' 00:00:00');
                }
                if (!empty($dateFilters['end_date'])) {
                    $query->where('created_at', '<=', $dateFilters['end_date'] . ' 23:59:59');
                }
            } else {
                if (!empty($dateFilters['end_date'])) {
                    $query->where('rental_start_date', '<=', $dateFilters['end_date'] . ' 23:59:59');
                }
                if (!empty($dateFilters['start_date'])) {
                    $query->where('rental_end_date', '>=', $dateFilters['start_date'] . ' 00:00:00');
                }
            }
        }

        if ($status === 'draft') {
            $query->where('invoices.status', 'draft');
        }

        // Payment status used to be filtered by loading every invoice and all of
        // its relations into PHP. MySQL can evaluate the same calculation while
        // preserving server-side pagination.
        $usesSqlPaymentStatus = $paymentStatus && $supportsSqlStatuses;

        if ($usesSqlPaymentStatus) {
            $paymentStatusQuery->applyStatus($query, $paymentStatus, $selectedCategory);
        }

        // Returned status can be expressed directly with relation existence
        // checks, so it also stays on the database side.
        if ($status === 'returned') {
            $query->whereDoesntHave('invoiceItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'))
                ->whereDoesntHave('additionalItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'))
                ->whereDoesntHave('customItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'));
        } elseif ($status === 'not_returned') {
            $query->where(function ($returnQuery) {
                $returnQuery->whereHas('invoiceItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'))
                    ->orWhereHas('additionalItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'))
                    ->orWhereHas('customItems', fn ($itemQuery) => $itemQuery->whereColumn('quantity', '>', 'returned_quantity'));
            });
        }

        // Non-MySQL installations retain the original calculated-status path.
        if (!$supportsSqlStatuses && in_array($status, ['returned', 'not_returned'], true)) {
            $invoices = $query->get();

            if ($status === 'returned') {
                $invoices = $invoices->filter(fn (Invoice $invoice) => $invoice->returned);
            } elseif ($status === 'not_returned') {
                $invoices = $invoices->reject(fn (Invoice $invoice) => $invoice->returned);
            }

            return $invoices->values();
        }

        if ($paymentStatus && !$usesSqlPaymentStatus) {
            $invoices = $query->get();

            return $invoices->filter(
                fn (Invoice $invoice) => $invoice->payment_status === $paymentStatus
            )->values();
        }

        return $query->orderByDesc('invoices.id');
    }

    private function paymentStatusForRow(Invoice $invoice): string
    {
        if (array_key_exists('calculated_final_total', $invoice->getAttributes())) {
            $finalTotal = (float) $invoice->calculated_final_total;
            $paidAmount = (float) $invoice->calculated_paid_amount;
            $balanceDue = $finalTotal - $paidAmount;

            if ($paidAmount <= 0 && $balanceDue > 1) {
                return 'unpaid';
            }

            if ($balanceDue <= 1) {
                return 'fully_paid';
            }

            return 'partially_paid';
        }

        return $invoice->payment_status;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('invoicesTable')
            ->columns($this->getColumns())
            ->minifiedAjax(route('invoices.index'), "data.start_date = $('#start_date').val(); data.end_date = $('#end_date').val(); data.status = $('#status').val(); data.payment_status = $('#payment_status').val();")
            ->orderBy(0, 'desc')
            ->parameters([
                'dom' => 'Bfrtip',
                'responsive' => true,
                'processing' => true,
                'serverSide' => true,
                'autoWidth' => false,
            ])
            ->buttons([
                Button::make('copy'),
                Button::make('csv'),
                Button::make('excel'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        $columns = [
            Column::make('id')->title('Invoice'),
            Column::make('customer.name')->title('Customer'),
            // This value is computed from the customer relation and is not searchable;
            // filtered-status requests use Yajra's CollectionDataTable, which has no filterColumn().
            Column::computed('customer_phone')->title('Phone')->searchable(false)->orderable(false),
            Column::computed('payment_status')->title('Payment Status')->searchable(false)->orderable(false),
        ];

        if (session('category', 'daily') === 'daily') {
            $columns[] = Column::make('rental_start_date')->title('From');
            $columns[] = Column::make('rental_end_date')->title('To');
        }

        $columns[] = Column::computed('returned')->title('Returned')->searchable(false)->orderable(false);
        $columns[] = Column::computed('action')
            ->title('Action')
            ->exportable(false)
            ->printable(false)
            ->searchable(false)
            ->orderable(false)
            ->addClass('text-nowrap');

        return $columns;
    }

    protected function filename(): string
    {
        return 'Invoices_' . date('YmdHis');
    }
}
