<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('has_rentals', function (Customer $customer): string {
                return $customer->invoices_exists
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('action', function (Customer $customer): string {
                $actions = '';

                if ($customer->invoices_exists) {
                    $actions .= '<a href="' . e(route('customers.rentalDetails', $customer->id)) . '" class="btn btn-sm btn-info">View Rentals</a> ';
                }

                $actions .= '<a href="' . e(route('customers.edit', $customer->id)) . '" class="btn btn-sm btn-warning">Edit</a> ';
                $actions .= '<a href="' . e(route('customers.destroy', $customer->id)) . '" class="btn btn-sm btn-danger delete-item">Delete</a>';

                return $actions;
            })
            ->rawColumns(['has_rentals', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()->withExists('invoices');
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customersTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->parameters([
                'dom' => 'Bfrtip',
                'responsive' => true,
                'processing' => true,
                'serverSide' => true,
                'autoWidth' => false,
            ])
            ->buttons([
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ])
            ->pageLength(10)
            ->lengthMenu([[10, 15, 25, 50], [10, 15, 25, 50]]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->width(150),
            Column::make('phone'),
            Column::make('address'),
            Column::computed('has_rentals')->title('Has Rentals')->searchable(false)->orderable(false),
            Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(150)
            ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Customer_' . date('YmdHis');
    }
}
