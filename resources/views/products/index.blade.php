@extends('layouts.master')

@section('title', 'Products')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Products</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Products ({{ $products->count() }})</h5>
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
            </div>
            <div class="card-body">
                <table id="productsTable" class="table table-striped table-bordered dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->description }}</td>
                                <td>{{ $product->price }}</td>
                                <td>
                                    <!-- Conditional Button for Rental Details -->
                                    @if ($product->rentedQuantity() > 0)
                                        <a href="{{ route('products.rentalDetails', $product->id) }}"
                                            class="btn btn-sm btn-info">View Rental Details</a>
                                    @endif

                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <a href="{{ route('products.destroy', $product->id) }}"
                                            class="btn btn-danger btn-sm delete-item">Delete</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Include the DataTable scripts -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables with export buttons and responsive support
            $('#productsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });
        });
    </script>
@endpush
