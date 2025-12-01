@extends('layouts.printLayout')

@section('title', 'Print Invoice')

@push('styles')
    <style>
        /* ============================
       PRINT STYLES FOR 80mm RECEIPT
       ============================ */

        @media print {

            html,
            body {
                width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: "Courier New", monospace !important;
                font-size: 15px !important;
                line-height: 1.25 !important;
            }

            @page {
                size: 80mm auto !important;
                margin: 0 !important;
            }

            .invoice-preview {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
                text-align: center !important;
            }

            .invoice-preview-card {
                width: 100%;
                padding: 0 !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .invoice-preview-header img {
                width: 110px !important;
                display: block !important;
                margin: 0 auto !important;
            }

            h5,
            h6,
            p,
            th,
            td {
                font-size: 15px !important;
                margin: 2px 0 !important;
                line-height: 1.25 !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: avoid !important;
            }

            td,
            th {
                padding: 2px 0 !important;
                word-break: break-word !important;
            }

            .section-divider {
                border-top: 1px dashed #000 !important;
                margin: 6px 0 !important;
            }

            .no-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .text-right {
                text-align: right !important;
            }
        }
    </style>
@endpush



@section('content')

    <div class="invoice-preview center">

        <div class="invoice-preview-card">

            <!-- Header -->
            <div class="invoice-preview-header">
                <img src="{{ asset('logo_croped.png') }}" alt="Logo">
                <p>Mzaar Rental Shop</p>
                <p>Tel: 71715612</p>
            </div>

            <div class="section-divider"></div>

            <!-- Invoice Info -->
            <div>
                <h5>Rental Agreement #{{ $invoice->id }}</h5>
                <p>Date Created: {{ $invoice->created_at->format('M d, Y') }}</p>

                @if ($invoice->category->name === 'daily')
                    <p>Rental Start: {{ $invoice->rental_start_date->format('d/m/Y h:i A') }}</p>
                    <p>Rental End: {{ $invoice->rental_end_date->format('d/m/Y h:i A') }}</p>
                    <p>Rental Days: {{ $invoice->days }} day(s)</p>
                @else
                    <p>Category: Season</p>
                @endif
            </div>

            <div class="section-divider"></div>

            <!-- Customer -->
            <div>
                <h6>Invoice To:</h6>
                <p>{{ $invoice->customer->name }}</p>
                <p>{{ $invoice->customer->address }}</p>
                <p>{{ $invoice->customer->phone }}</p>
                <p>{{ $invoice->customer->email }}</p>
            </div>

            <div class="section-divider"></div>

            <!-- Items -->
            <div class="no-break">
                <h6>Items:</h6>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->invoiceItems as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach

                        @foreach ($invoice->customItems as $customItem)
                            <tr>
                                <td>{{ $customItem->name }}</td>
                                <td>{{ $customItem->quantity }}</td>
                                <td class="text-right">${{ number_format($customItem->price * $customItem->quantity, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($invoice->additionalItems->isNotEmpty())
                <div class="section-divider"></div>

                <div class="no-break">
                    <h6>Additional Items:</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->additionalItems as $addedItem)
                                <tr>
                                    <td>{{ $addedItem->product->name }}</td>
                                    <td>{{ $addedItem->quantity }}</td>
                                    <td class="text-right">${{ number_format($addedItem->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($invoice->returnDetails->isNotEmpty())
                <div class="section-divider"></div>

                <div class="no-break">
                    <h6>Returned Items:</h6>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th class="text-right">Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->returnDetails as $return)
                                @php
                                    $cost =
                                        $return->days_used *
                                        $return->returned_quantity *
                                        ($return->invoiceItem->price ?? ($return->additionalItem->price ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $return->invoiceItem->product->name ?? ($return->additionalItem->product->name ?? $return->customItem->name) }}
                                    </td>
                                    <td>{{ $return->returned_quantity }}</td>
                                    <td class="text-right">${{ number_format($cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="section-divider"></div>

            <!-- Summary -->
            <div class="no-break">
                <table>
                    <tbody>
                        <tr>
                            <td>Base Total:</td>
                            <td class="text-right">${{ number_format($totals['subtotalForDiscount'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right">-${{ number_format($totals['discountAmount'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Add Items Cost:</td>
                            <td class="text-right">${{ number_format($totals['additionalItemsCost'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Refund:</td>
                            <td class="text-right">-${{ number_format($totals['refundForUnusedDays'], 2) }}</td>
                        </tr>
                        <tr>
                            <td>Deposit:</td>
                            <td class="text-right">${{ number_format($invoice->deposit, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Final Total:</strong></td>
                            <td class="text-right"><strong>${{ number_format($totals['finalTotal'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Paid:</td>
                            <td class="text-right">${{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong style="color:red;">Balance:</strong></td>
                            <td class="text-right" style="color:red;">
                                <strong>${{ number_format($totals['balanceDue'], 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section-divider"></div>

            <!-- Footer -->
            <div class="footer-text center">
                <p>I declare having received the merchandise in good condition and I agree to return it on time.</p>
                <p>I will reimburse the value of any missing or damaged article.</p>
                <p>Mzaar Intercontinental Hotel - Tel: 71715612</p>
                <p>Warde - Tel: 70100015</p>
                <p>Mayrouba - Tel: 71721236</p>
            </div>

        </div>

    </div>

@endsection


@push('scripts')
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 300);
        };

        window.onafterprint = function() {
            window.close();
        };
    </script>
@endpush
