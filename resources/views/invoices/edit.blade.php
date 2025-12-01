@extends('layouts.master')

@section('title', 'Edit Invoice')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Invoice</li>
        </ol>
    </nav>
    <div class="row g-6">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Edit Invoice</h5>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <p>There were some problems with your input. Please check the form below:</p>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <ul class="nav nav-tabs flex-column flex-sm-row" id="editInvoiceTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="returns-tab" data-bs-toggle="tab" data-bs-target="#returns"
                                type="button" role="tab" aria-controls="returns" aria-selected="true">Manage
                                Returns</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="date-additions-tab" data-bs-toggle="tab"
                                data-bs-target="#date-additions" type="button" role="tab"
                                aria-controls="date-additions" aria-selected="false">
                                Add New Date
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="additions-tab" data-bs-toggle="tab" data-bs-target="#additions"
                                type="button" role="tab" aria-controls="additions" aria-selected="false">Add New
                                Items</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="removals-tab" data-bs-toggle="tab" data-bs-target="#removals"
                                type="button" role="tab" aria-controls="removals" aria-selected="false">Remove
                                Items</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="payment-status-tab" data-bs-toggle="tab"
                                data-bs-target="#payment-status" type="button" role="tab"
                                aria-controls="payment-status" aria-selected="false">Payment Status</button>
                        </li>
                        {{-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="invoice-status-tab" data-bs-toggle="tab"
                            data-bs-target="#invoice-status" type="button" role="tab"
                            aria-controls="invoice-status" aria-selected="false">Invoice Status</button>
                    </li> --}}
                    </ul>
                    <div class="tab-content mt-3">
                        {{-- Manage Returns Tab --}}
                        <div class="tab-pane fade show active" id="returns" role="tabpanel" aria-labelledby="returns-tab">
                            @include('invoices.partials.manage-returns')
                        </div>

                        {{-- Invoice Dates Tab --}}
                        <div class="tab-pane fade" id="date-additions" role="tabpanel" aria-labelledby="dates-tab">
                            @include('invoices.partials.add-new-dates')
                        </div>

                        {{-- Add New Items Tab --}}
                        <div class="tab-pane fade" id="additions" role="tabpanel" aria-labelledby="additions-tab">
                            @include('invoices.partials.add-new-items')
                        </div>

                        {{-- Remove Items Tab --}}
                        <div class="tab-pane fade" id="removals" role="tabpanel" aria-labelledby="removals-tab">
                            @include('invoices.partials.remove-items', ['items' => $invoice])
                        </div>


                        {{-- Payment Status Tab --}}
                        <div class="tab-pane fade" id="payment-status" role="tabpanel" aria-labelledby="payment-status-tab">
                            @include('invoices.partials.payment-status')
                        </div>

                        {{-- Invoice Status Tab --}}
                        {{-- <div class="tab-pane fade" id="invoice-status" role="tabpanel" aria-labelledby="invoice-status-tab">
                        @include('invoices.partials.invoice-status')
                    </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include Scripts --}}
    @push('scripts')
        <script>
            // Optional: Add custom JavaScript for interactivity
        </script>
    @endpush
@endsection
