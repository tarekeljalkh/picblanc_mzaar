@extends('layouts.master')

@section('title', 'Show Invoice')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-invoice.css') }}" />
@endpush

@section('content')

    <div class="row invoice-preview">
        <!-- Invoice -->
        <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6">
            <div class="card invoice-preview-card p-sm-12 p-6">
                <!-- Invoice Header -->
                <div class="card-body invoice-preview-header rounded">
                    <div
                        class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column align-items-xl-center align-items-md-start align-items-sm-center align-items-start">
                        <div class="mb-xl-0 mb-6 text-heading">
                            <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('logo_croped.png') }}" alt="Logo" width="150">
                                </span>
                            </div>
                            <p class="mb-2">Mzaar Rental Shop</p>
                            <p class="mb-2">Tel: 71715612</p>
                        </div>
                        <div>
                            <h5 class="mb-6">Rental Agreement #{{ $invoice->id }}</h5>
                            <div class="mb-1 text-heading">
                                <span>Date Created:</span>
                                <span class="fw-medium">{{ $invoice->created_at->format('M d, Y') }}</span>
                            </div>
                            <br>
                            <div class="text-heading">
                                @if ($invoice->category->name === 'daily')
                                    <p>Rental Start: {{ $invoice->rental_start_date->format('d/m/Y') }}</p>
                                    <p>Rental End: {{ $invoice->rental_end_date->format('d/m/Y') }}</p>
                                    <p>Rental Days: {{ $invoice->days }} day(s)</p>
                                @else
                                    <p>Category: Season</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer and Rental Details -->
                <div class="card-body px-0">
                    <div class="row">
                        <div class="col-xl-6 col-md-12 col-sm-5 col-12 mb-xl-0 mb-md-6 mb-sm-0 mb-6">
                            <h6>Invoice To:</h6>
                            <p class="mb-1">{{ $invoice->customer->name }}</p>
                            <p class="mb-1">{{ $invoice->customer->address }}</p>
                            <p class="mb-1">{{ $invoice->customer->phone }}</p>
                            <p class="mb-0">{{ $invoice->customer->email }}</p>
                        </div>
                        <div class="col-xl-6 col-md-12 col-sm-7 col-12">
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="table-responsive border border-bottom-0 border-top-0 rounded">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th>Total Price</th>
                                @if ($invoice->category->name === 'daily')
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Total Days</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Regular Items -->
                            @foreach ($invoice->invoiceItems as $item)
                                <tr>
                                    <td class="text-nowrap text-heading">{{ $item->product->name }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    @php
                                        $from = $item->rental_start_date
                                            ? \Carbon\Carbon::parse($item->rental_start_date)
                                            : null;
                                        $to = $item->rental_end_date
                                            ? \Carbon\Carbon::parse($item->rental_end_date)
                                            : null;
                                        $days = $from && $to ? $to->diffInDays($from) + 1 : 0;
                                        $totalPrice = $item->price * $item->quantity * $days;
                                    @endphp
                                    <td>${{ number_format($totalPrice, 2) }}</td>
                                    @if ($invoice->category->name === 'daily')
                                        <td>{{ optional($item->rental_start_date)->format('d/m/Y') }}</td>
                                        <td>{{ optional($item->rental_end_date)->format('d/m/Y') }}</td>
                                        <td>{{ $days }}</td>
                                    @endif
                                </tr>
                            @endforeach

                            <!-- Custom Items -->
                            @foreach ($invoice->customItems as $customItem)
                                <tr>
                                    <td class="text-nowrap text-heading">{{ $customItem->name }}</td>
                                    <td>${{ number_format($customItem->price, 2) }}</td>
                                    <td>{{ $customItem->quantity }}</td>

                                    {{-- ✅ Calculate total price based on rental days for "daily" category --}}
                                    <td>
                                        ${{ number_format($customItem->price * $customItem->quantity * ($invoice->category->name === 'daily' ? $invoice->days : 1), 2) }}
                                    </td>

                                    {{-- ✅ Display rental dates for "daily" category --}}
                                    @if ($invoice->category->name === 'daily')
                                        <td>{{ $invoice->rental_start_date->format('d/m/Y') }}</td>
                                        {{-- From Date --}}
                                        <td>{{ $invoice->rental_end_date->format('d/m/Y') }}</td>
                                        {{-- To Date --}}
                                        <td>{{ $invoice->days }}</td> {{-- Total Days --}}
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <!-- Additional Items -->
                @if ($invoice->additionalItems->isNotEmpty())
                    <div class="table-responsive border border-bottom-0 rounded mt-4">
                        <h6 class="px-3 py-2 bg-light">Additional Items</h6>
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th>Total Price</th>
                                    @if ($invoice->category->name === 'daily')
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Total Days</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->additionalItems as $addedItem)
                                    <tr>
                                        <td class="text-nowrap text-heading">{{ $addedItem->product->name }}</td>
                                        <td>${{ number_format($addedItem->price, 2) }}</td>
                                        <td>{{ $addedItem->quantity }}</td>
                                        <td>${{ number_format($addedItem->total_price, 2) }}</td>
                                        @if ($invoice->category->name === 'daily')
                                            <td>{{ optional($addedItem->rental_start_date)->format('d/m/Y') }}</td>
                                            <td>{{ optional($addedItem->rental_end_date)->format('d/m/Y') }}</td>
                                            <td>{{ $addedItem->days }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Returned Items -->
                @if ($invoice->returnDetails->isNotEmpty())
                    <div class="table-responsive border border-bottom-0 rounded mt-4">
                        <h6 class="px-3 py-2 bg-light">Returned Items</h6>
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Cost</th>
                                    @if ($invoice->category->name === 'daily')
                                        <th>From Date</th>
                                    @endif
                                    <th>Return Date</th>
                                    @if ($invoice->category->name === 'daily')
                                        <th>Total Days Used</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->returnDetails as $return)
                                    @php
                                        $pricePerDay = $return->invoiceItem
                                            ? $return->invoiceItem->price
                                            : ($return->additionalItem
                                                ? $return->additionalItem->price
                                                : ($return->customItem
                                                    ? $return->customItem->price
                                                    : 0)); // ✅ Added custom item handling

                                        $cost = $return->days_used * $return->returned_quantity * $pricePerDay;

                                        $rentalStartDate =
                                            optional($return->invoiceItem)->rental_start_date ??
                                            (optional($return->additionalItem)->rental_start_date ??
                                                (session('category') === 'season'
                                                    ? (optional($return->customItem)->invoice
                                                        ? optional($return->customItem->invoice)->created_at
                                                        : null)
                                                    : (optional($return->customItem)->invoice
                                                        ? optional($return->customItem->invoice)->rental_start_date
                                                        : null)));
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $return->invoiceItem->product->name ?? ($return->additionalItem->product->name ?? ($return->customItem->name ?? 'N/A')) }}
                                        </td>
                                        <td>{{ $return->returned_quantity }}</td>
                                        <td>${{ number_format($cost, 2) }}</td>
                                        @if ($invoice->category->name === 'daily')
                                            <td>{{ optional($rentalStartDate)->format('d/m/Y') }}</td>
                                        @endif
                                        <td>{{ optional($return->return_date)->format('d/m/Y') }}</td>
                                        @if ($invoice->category->name === 'daily')
                                            <td>{{ $return->days_used }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                @endif

                <!-- Invoice Summary -->
                <div class="table-responsive">
                    <table class="table m-0 table-borderless">
                        <tbody>
                            <tr>
                                <td class="align-top pe-6 ps-0 py-6 text-body">
                                    <p class="mb-1">
                                        <span class="me-2 h6">Salesperson:</span>
                                        <span>{{ $invoice->user->name ?? 'N/A' }}</span>
                                    </p>

                                    <!-- Payment Type -->
                                    @if ($invoice->payments && $invoice->payments->count())
                                        <p><strong>Payment By:</strong>
                                            {{ $invoice->payments->groupBy('payment_method')->map(function ($group, $method) {
                                                    $total = $group->sum('amount');
                                                    return ucwords(str_replace('_', ' ', $method)) . ' ($' . number_format($total, 2) . ')';
                                                })->values()->join(', ') }}
                                        </p>
                                    @endif

                                    {{-- @if ($invoice->payments && $invoice->payments->count())
                                        <p><strong>Payment By:</strong>
                                            {{ $invoice->payments->pluck('payment_method')->unique()->join(', ') }}
                                        </p>
                                    @endif --}}

                                    <!-- Note -->
                                    <form action="{{ route('invoices.updateNote', $invoice->id) }}" method="POST"
                                        class="d-flex align-items-center gap-2 mb-3">
                                        @csrf
                                        @method('PUT')

                                        <label for="note" class="me-2 mb-0"><strong>Note:</strong></label>

                                        <input type="text" name="note" id="note" class="form-control"
                                            value="{{ $invoice->note }}" placeholder="Enter a note..."
                                            style="max-width: 300px;" />

                                        <button type="submit" class="btn btn-success">Update</button>
                                    </form>

                                    <div class="mb-3">
                                        <span
                                            class="badge
            {{ $invoice->payment_status === 'fully_paid'
                ? 'bg-success'
                : ($invoice->payment_status === 'partially_paid'
                    ? 'bg-warning'
                    : 'bg-danger') }}">
                                            {{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}
                                        </span>
                                    </div>

                                </td>
                                <td class="px-0 py-3 w-px-200">
                                    <p class="mb-2">Base Total (All Items):</p>
                                    <p class="mb-2">Discount:</p>
                                    <p class="mb-2">Additional Items Cost:</p> <!-- New Line for Additional Items -->
                                    <p class="mb-2">Refund for Unused Days:</p>
                                    <p class="mb-2">Deposit:</p>
                                    <p class="mb-2">Final Total:</p>
                                    <p class="mb-2">Paid Amount:</p>
                                    <p class="mb-2 text-danger fw-bold">Balance Due:</p>
                                </td>
                                <td class="text-end px-0 py-6 w-px-100 fw-medium text-heading">
                                    <p class="fw-medium mb-2">${{ number_format($totals['subtotalForDiscount'], 2) }}</p>
                                    <p class="fw-medium mb-2">- ${{ number_format($totals['discountAmount'], 2) }}</p>
                                    <p class="fw-medium mb-2">${{ number_format($totals['additionalItemsCost'], 2) }}</p>
                                    <!-- Display Value -->
                                    <p class="fw-medium mb-2">- ${{ number_format($totals['refundForUnusedDays'], 2) }}</p>
                                    <p class="fw-medium mb-2">${{ number_format($invoice->deposit, 2) }}</p>
                                    <p class="fw-medium mb-2">${{ number_format($totals['finalTotal'], 2) }}</p>
                                    <p class="fw-medium mb-2">${{ number_format($invoice->payments->sum('amount'), 2) }}
                                    </p>
                                    <p class="fw-medium mb-0 text-danger">${{ number_format($totals['balanceDue'], 2) }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <hr class="mt-0 mb-6">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-12">
                            <span class="fw-medium text-heading">CONDITION:</span>
                            <span>I declare having received the merchandise mentioned above in good condition and I agree to
                                return it on time. I will reimburse the value of any missing, damaged, or broken
                                article.</span>
                            <br>
                            <hr>
                            <span>Mzaar Intercontinental Hotel - Tel: 71715612 | Warde - Tel: 70100015 | Mayrouba - Tel:
                                71721236</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Invoice -->

        <!-- Invoice Actions -->
        <div class="col-xl-3 col-md-4 col-12 invoice-actions">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('invoices.download', $invoice->id) }}" method="GET">
                        <button type="submit" class="btn btn-primary d-grid w-100 mb-4">Download</button>
                    </form>
                    <div class="d-flex mb-4">
                        <a class="btn btn-label-secondary d-grid w-100 me-4" target="_blank"
                            href="{{ route('invoices.print', $invoice->id) }}">Print</a>
                        <a href="{{ route('invoices.edit', $invoice->id) }}"
                            class="btn btn-label-secondary d-grid w-100">Edit</a>
                    </div>
                    <a class="btn btn-success d-grid w-100"
                        href="https://wa.me/+961{{ $invoice->customer->phone }}?text={{ urlencode('Download your invoice PDF here: ' . route('invoices.download', $invoice->id)) }}"
                        target="_blank">
                        Send via WhatsApp
                    </a>
                </div>
            </div>
        </div>
        <!-- /Invoice Actions -->
    </div>

@endsection
