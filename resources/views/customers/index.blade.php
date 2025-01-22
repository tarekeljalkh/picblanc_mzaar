@extends('layouts.master')

@section('title', 'Customers')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Customers</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Customers ({{ $customers->count() }})</h5>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">Add New Customer</a>
            </div>
            <div class="card-body">
                <table id="customersTable" class="table table-striped table-bordered dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Has Rentals</th> <!-- New column -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->address }}</td>
                                <td>
                                    <!-- Display if customer has rentals -->
                                    @if ($customer->hasRentals())
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- Only show the 'View Rentals' button if the customer has rentals -->
                                    @if ($customer->hasRentals())
                                        <a href="{{ route('customers.rentalDetails', $customer->id) }}"
                                            class="btn btn-sm btn-info">View Rentals</a>
                                    @endif
                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('customers.edit', $customer->id) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <a href="{{ route('customers.destroy', $customer->id) }}"
                                            class="btn btn-sm btn-danger delete-item">Delete</a>
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
            $('#customersTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });
        });
    </script>
@endpush
