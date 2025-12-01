@extends('layouts.master')

@section('title', 'Rental Details for ' . $customer->name)

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}'s Rentals</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">{{ $customer->name }}'s Rental Details</h5>
            </div>
            <div class="card-body">
                <!-- Filter form -->
                <form method="GET" action="{{ route('customers.rentalDetails', $customer->id) }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="date" name="start_date" class="form-control" placeholder="Start Date"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-5">
                            <input type="date" name="end_date" class="form-control" placeholder="End Date"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Rental details table -->
                <table id="rentalsTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <!-- Rental Dates (Daily Category Only) -->
                            @if (session('category') === 'daily')
                                <th>Rental Start Date</th>
                                <th>Rental End Date</th>
                            @endif
                            <th>Items</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            @php
                                $returnedCost = $invoice->returnDetails->sum('cost');
                                $netTotal = $invoice->total_amount - $returnedCost; // Adjusted total considering returns
                            @endphp
                            <tr>
                                <td>{{ $invoice->id }}</td>
                                <td>${{ number_format($netTotal, 2) }}</td>
                                <td> <span
                                        class="badge {{ $invoice->payment_status === 'fully_paid' ? 'bg-success' : ($invoice->payment_status === 'partially_paid' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                    </span>
                                </td>
                                <!-- Rental Dates (Daily Category Only) -->
                                @if (session('category') === 'daily')
                                    <td>{{ \Carbon\Carbon::parse($invoice->rental_start_date)->format('d/m/Y h:i A') }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->rental_end_date)->format('d/m/Y h:i A') }}</td>
                                @endif
                                <td>
                                    <ul>
                                        @foreach ($invoice->invoiceItems as $item)
                                            <li>{{ $item->product->name }} ({{ $item->quantity }} pcs)</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>{{ $invoice->created_at->format('d/m/Y h:i A') }}</td>
                                <td><a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-warning">Print</a>
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
    <!-- Include DataTables -->
    <script>
        $(document).ready(function() {
            $('#rentalsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });
        });
    </script>
@endpush
