@extends('layouts.master')

@section('title', 'Trial Balance')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Trial Balance</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Trial Balance for
                    {{ \Carbon\Carbon::parse($fromDate)->format('F j, Y') }}
                    to
                    {{ \Carbon\Carbon::parse($toDate)->format('F j, Y') }}
                </h5>
            </div>
            <div class="card-body">

                <!-- Date Range Filter Form -->
                <form method="GET" action="{{ route('trialbalance.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" id="from_date" name="from_date" class="form-control"
                                   value="{{ old('from_date', $fromDate) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" id="to_date" name="to_date" class="form-control"
                                   value="{{ old('to_date', $toDate) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 me-2">Filter</button>
                            <button type="button" id="reset-button" class="btn btn-secondary w-100">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Loading Spinner -->
                <div id="loading" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:9999;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Trial Balance Table -->
                @if (count($trialBalanceData) > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trialBalanceData as $data)
                                <tr>
                                    <td>{{ $data['description'] }}</td>
                                    <td>${{ number_format($data['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle"></i> No data available for the selected date range.
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Initialize Flatpickr for date inputs
        flatpickr('#from_date', {
            dateFormat: "Y-m-d"
        });
        flatpickr('#to_date', {
            dateFormat: "Y-m-d"
        });

        // Show loading spinner on form submission
        const form = document.querySelector('form');
        form.addEventListener('submit', function () {
            document.getElementById('loading').style.display = 'block';
        });

        // Reset button functionality
        document.getElementById('reset-button').addEventListener('click', function () {
            document.getElementById('from_date').value = '';
            document.getElementById('to_date').value = '';
            form.submit();
        });
    </script>
@endpush
