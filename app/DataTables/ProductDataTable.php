<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('price', fn (Product $product): string => number_format((float) $product->price, 2))
            ->addColumn('action', function (Product $product): string {
                $actions = '';

                if ((int) $product->rented_quantity > 0) {
                    $actions .= '<a href="' . e(route('products.rentalDetails', $product->id)) . '" class="btn btn-sm btn-info">View Rental Details</a> ';
                }

                if (auth()->user()?->role === 'admin') {
                    $actions .= '<a href="' . e(route('products.edit', $product->id)) . '" class="btn btn-sm btn-warning">Edit</a> ';
                    $actions .= '<a href="' . e(route('products.destroy', $product->id)) . '" class="btn btn-danger btn-sm delete-item">Delete</a>';
                }

                return $actions;
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $categoryId = session('category', 'daily');

        return $model->newQuery()
            ->whereHas('category', fn ($query) => $query->where('name', $categoryId))
            ->withSum([
                'invoiceItems as rented_quantity' => fn ($query) => $query->whereHas(
                    'invoice',
                    fn ($invoiceQuery) => $invoiceQuery->where('status', 'active')
                ),
            ], 'quantity');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('productsTable')
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
                Button::make('csv'),
                Button::make('excel'),
                Button::make('pdf'),
                Button::make('print'),
            ])
            ->pageLength(10)
            ->lengthMenu([[10, 15, 25, 50], [10, 15, 25, 50]]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('name'),
            Column::make('description')->searchable(false),
            Column::make('price')->searchable(false),
            Column::computed('action')->exportable(false)->printable(false)->searchable(false)->orderable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Products_' . date('YmdHis');
    }
}
