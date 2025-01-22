@extends('layouts.master')

@section('title', 'Overdue Invoices')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Overdue Invoices</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Overdue Invoices ({{ $invoices->count() }})</h5>
            </div>
            <div class="card-body">
                <!-- Date Filter Form -->
                <form method="GET" action="{{ route('invoices.unpaid') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select id="category" name="category" class="form-select">
                                <option value="daily" {{ $selectedCategory === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="season" {{ $selectedCategory === 'season' ? 'selected' : '' }}>Season</option>
                            </select>
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
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->id }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>${{ $invoice->total_amount }}</td>
                                <td>{{ $invoice->rental_end_date->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge
                                        {{ $invoice->payment_status === 'fully_paid' ? 'bg-success' :
                                        ($invoice->payment_status === 'partially_paid' ? 'bg-warning' : 'bg-danger') }}">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                    </span>
                                </td>
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
