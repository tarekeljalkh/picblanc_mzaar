@extends('layouts.master')

@section('title', 'Invoices')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Invoices</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Invoices ({{ $invoices->count() }})</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">Create New Invoice</a>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('invoices.index') }}" class="mb-3">
                    <div class="row">
                        <!-- Date Filters -->
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

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned
                                </option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <!-- Payment Status Filter -->
                        <div class="col-md-2">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select id="payment_status" name="payment_status" class="form-select">
                                <option value="">All</option>
                                <option value="fully_paid"
                                    {{ request('payment_status') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                                <option value="partially_paid"
                                    {{ request('payment_status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid
                                </option>
                                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>
                                    Unpaid</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <!-- Invoice Table -->
                <table id="invoicesTable" class="table table-striped table-bordered dt-responsive nowrap"
                    style="width:100%">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Payment Status</th>
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
                                <!-- Customer Name -->
                                <td>{{ $invoice->customer->name }}</td>

                                <!-- Payment Status -->
                                <td>
                                    <span
                                        class="badge {{ $invoice->payment_status === 'fully_paid' ? 'bg-success' : ($invoice->payment_status === 'partially_paid' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                    </span>
                                </td>

                                <!-- Rental Dates (Daily Category Only) -->
                                @if (session('category') === 'daily')
                                    <td>{{ optional($invoice->rental_start_date)->format('d/m/Y h:i A') }}</td>
                                    <td>{{ optional($invoice->rental_end_date)->format('d/m/Y h:i A') }}</td>
                                @endif

                                <!-- Actions -->
                                <td>
                                    <a href="{{ route('invoices.show', $invoice->id) }}"
                                        class="btn btn-info btn-sm">Show</a>
                                    <a href="{{ route('invoices.edit', $invoice->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <a href="{{ route('invoices.print', $invoice->id) }}"
                                        class="btn btn-primary btn-sm">Print</a>
                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('invoices.destroy', $invoice->id) }}"
                                            class="btn btn-danger btn-sm delete-item">Delete</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    <nav>
                        <ul class="pagination justify-content-center">
                            {{ $invoices->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </ul>
                    </nav>
                </div>
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
