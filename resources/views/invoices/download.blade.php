<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice #{{ $invoice->id }}</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        table th,
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 12px;
        }

        table th {
            background-color: #f9f9f9;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-danger {
            color: red;
        }

        .text-success {
            color: green;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Invoice Header -->
        <div class="d-flex">
            <div>
                <img src="{{ public_path('logo_croped.png') }}" alt="Logo" width="150">
                <p>Mayrouba Rental Shop</p>
                <p>Tel: 71 72 12 36</p>
            </div>
            <div>
                <h5>Rental Agreement #{{ $invoice->id }}</h5>
                <p><strong>Date Created:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
                @if ($invoice->category->name === 'daily')
                    <p><strong>Rental Start:</strong> {{ $invoice->rental_start_date->format('d/m/Y h:i A') }}</p>
                    <p><strong>Rental End:</strong> {{ $invoice->rental_end_date->format('d/m/Y h:i A') }}</p>
                    <p><strong>Rental Days:</strong> {{ $invoice->days }} day(s)</p>
                @else
                    <p><strong>Category:</strong> Season</p>
                @endif
            </div>
        </div>

        <hr />

        <!-- Customer Details -->
        <div>
            <h6>Invoice To:</h6>
            <p>{{ $invoice->customer->name }}</p>
            <p>{{ $invoice->customer->address }}</p>
            <p>{{ $invoice->customer->phone }}</p>
            <p>{{ $invoice->customer->email }}</p>
        </div>

        <hr />

        <!-- Invoice Items -->
        <h6>Invoice Items</h6>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Total Price</th>
                    @if ($invoice->category->name === 'daily')
                        <th>From Date</th>
                        <th>To Date</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->price * $item->quantity * ($invoice->category->name === 'daily' ? $item->days : 1), 2) }}
                        </td>
                        @if ($invoice->category->name === 'daily')
                            <td>{{ optional($item->rental_start_date)->format('d/m/Y h:i A') }}</td>
                            <td>{{ optional($item->rental_end_date)->format('d/m/Y h:i A') }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Additional Items -->
        @if ($invoice->additionalItems->isNotEmpty())
            <h6>Additional Items</h6>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit Price</th>
                        <th>Qty</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->additionalItems as $addedItem)
                        <tr>
                            <td>{{ $addedItem->product->name }}</td>
                            <td>${{ number_format($addedItem->price, 2) }}</td>
                            <td>{{ $addedItem->quantity }}</td>
                            <td>${{ number_format($addedItem->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Invoice Summary -->
        <h6>Invoice Summary</h6>
        <table>
            @php
                $subtotalForDiscount = $totals['subtotalForDiscount'];
                $additionalItemsCost = $totals['additionalItemsCost'];
                $refundForUnusedDays = $totals['refundForUnusedDays'];
                $finalTotalCustom = $totals['finalTotalCustom'];
                $balanceDue = $totals['balanceDue'];
            @endphp
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td class="text-end">${{ number_format($subtotalForDiscount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Additional Items Cost:</strong></td>
                <td class="text-end text-success">+ ${{ number_format($additionalItemsCost, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Refund for Unused Days:</strong></td>
                <td class="text-end text-danger">- ${{ number_format($refundForUnusedDays, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Final Total:</strong></td>
                <td class="text-end">${{ number_format($finalTotalCustom, 2) }}</td>
            </tr>
            <tr>
                <td class="text-danger"><strong>Balance Due:</strong></td>
                <td class="text-end text-danger">${{ number_format($balanceDue, 2) }}</td>
            </tr>
        </table>

        <!-- Salesperson -->
        <p><strong>Salesperson:</strong> {{ $invoice->user->name ?? 'N/A' }}</p>
        <p>
            @if ($balanceDue <= 0)
                Payment: Fully Paid
            @elseif ($invoice->paid_amount > 0)
                Payment: Partially Paid
            @else
                Payment: Not Paid
            @endif
        </p>

        <hr />

        <!-- Note -->
        @if ($invoice->note)
            <p><strong>NOTE:</strong> {{ $invoice->note }}</p>
        @endif

        <!-- Conditions -->
        <p><strong>CONDITION:</strong> I declare having received the merchandise mentioned above in good condition and
            agree to return it on time. I will reimburse the value of any missing, damaged, or broken article.</p>
    </div>
</body>

</html>
