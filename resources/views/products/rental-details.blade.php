@extends('layouts.master')

@section('title', 'Rental and Return Details for ' . $product->name)

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rental and Return Details</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Rental and Return Details for {{ $product->name }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Rented Quantity</th>
                            <th>Returned Quantity</th> <!-- New column -->
                            <th>Remaining Quantity</th> <!-- New column -->
                            <th>Rental Start Date</th>
                            <th>Rental End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rentals as $rental)
                            @php
                                // Calculate remaining quantity
                                $returnedQuantity = $rental->returnDetails->sum('returned_quantity');
                                $remainingQuantity = $rental->quantity - $returnedQuantity;
                            @endphp
                            <tr>
                                <td>{{ $rental->invoice->customer->name }}</td> <!-- Customer Name -->
                                <td>{{ $rental->quantity }}</td> <!-- Quantity Rented -->
                                <td>{{ $returnedQuantity }}</td> <!-- Quantity Returned -->
                                <td>{{ $remainingQuantity }}</td> <!-- Remaining Quantity -->
                                <td>{{ $rental->invoice->rental_start_date }}</td> <!-- Rental Start Date -->
                                <td>{{ $rental->invoice->rental_end_date }}</td> <!-- Rental End Date -->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Table for Return Details -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="m-0">Return Details for {{ $product->name }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Returned Quantity</th>
                            <th>Days Used</th>
                            <th>Cost</th>
                            <th>Return Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rentals as $rental)
                            @foreach ($rental->returnDetails as $return)
                                <tr>
                                    <td>{{ $rental->invoice->customer->name }}</td> <!-- Customer Name -->
                                    <td>{{ $return->returned_quantity }}</td> <!-- Quantity Returned -->
                                    <td>{{ $return->days_used }}</td> <!-- Days Used -->
                                    <td>${{ number_format($return->cost, 2) }}</td> <!-- Cost -->
                                    <td>{{ $return->return_date->format('Y-m-d') }}</td> <!-- Return Date -->
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
