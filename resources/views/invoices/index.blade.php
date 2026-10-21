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
                <h5 class="m-0">Invoices</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">Create New Invoice</a>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form id="invoice-filters" method="GET" action="{{ route('invoices.index') }}" class="mb-3">
                    <div class="row">
                        <!-- Date Filters -->
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">All</option>
                                <option value="not_returned" {{ request('status') === 'not_returned' ? 'selected' : '' }}>
                                    Not Returned</option>
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

                        <div class="col-md-2 align-self-end">

                            <!-- Clear all filters -->
                            <button type="button" class="btn btn-secondary" id="clearFilters">Clear</button>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary">Filter</button>

                        </div>

                    </div>
                </form>

                <!-- Invoice Table -->
                {!! $dataTable->table([
                    'class' => 'table table-striped table-bordered dt-responsive nowrap',
                    'style' => 'width:100%',
                ]) !!}
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        $('#invoice-filters').on('submit', function(event) {
            event.preventDefault();

            const query = new URLSearchParams(new FormData(this));
            window.history.replaceState({}, '', this.action + '?' + query.toString());
            $('#invoicesTable').DataTable().ajax.reload();
        });

        $('#clearFilters').on('click', function() {
            $('#invoice-filters')[0].reset();
            $('#invoice-filters').trigger('submit');
        });
    </script>
@endpush
