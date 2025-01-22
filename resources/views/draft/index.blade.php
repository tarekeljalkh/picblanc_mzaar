@extends('layouts.master')

@section('title', 'Draft Invoices')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Drafts</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <!-- Date Filter Form -->
                <form method="GET" action="{{ route('invoices.index') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control"
                                   value="{{ request('start_date', \Carbon\Carbon::today()->toDateString()) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control"
                                   value="{{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <table id="invoicesTable" class="table table-striped table-bordered dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Paid</th>
                            @if (session('category') === 'daily')
                            <th>From</th>
                            <th>To</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->id }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>${{ $invoice->total_amount }}</td>
                                <td>{{ ucfirst($invoice->status) }}</td>
                                <td>
                                    @if($invoice->paid)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                @if (session('category') === 'daily')
                                <td>{{ $invoice->rental_start_date->format('d/m/Y h:i A') }}</td>
                                <td>{{ $invoice->rental_end_date->format('d/m/Y h:i A') }}</td>
                                @endif
                                <td>
                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">Edit</a>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-warning">Show</a>
                                    <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-warning">Print</a>
                                    @if (auth()->user()->role === 'admin')
                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
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
    <script>
        $(document).ready(function() {
            $('#invoicesTable').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                responsive: true
            });
        });
    </script>
@endpush
