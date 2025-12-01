<div class="table-responsive border border-bottom-0 border-top-0 rounded mb-4">
    <h5>Invoice Items</h5>
    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total Price</th>
                @if (session('category') === 'daily')
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Total Days</th>
                @endif
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->invoiceItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->total_price, 2) }}</td>
                    @if ($invoice->category->name === 'daily')
                        <td>{{ optional($item->rental_start_date)->format('d/m/Y') }}</td>
                        <td>{{ optional($item->rental_end_date)->format('d/m/Y') }}</td>
                        <td>{{ $item->days }}</td>
                    @endif
                    <td>
                        <form action="{{ route('invoice-items.destroyItem', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this item?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive border border-bottom-0 border-top-0 rounded mb-4">
    <h5>Custom Items</h5>
    <table class="table">
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
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->customItems as $customItem)
                <tr>
                    <td>{{ $customItem->name }}</td>
                    <td>${{ number_format($customItem->price, 2) }}</td>
                    <td>{{ $customItem->quantity }}</td>
                    <td>${{ number_format($customItem->price * $customItem->quantity * ($invoice->category->name === 'daily' ? $invoice->days : 1), 2) }}</td>
                    @if ($invoice->category->name === 'daily')
                        <td>{{ $invoice->rental_start_date->format('d/m/Y') }}</td>
                        <td>{{ $invoice->rental_end_date->format('d/m/Y') }}</td>
                        <td>{{ $invoice->days }}</td>
                    @endif
                    <td>
                        <form action="{{ route('custom-items.destroy', $customItem->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this custom item?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($invoice->additionalItems->isNotEmpty())
    <div class="table-responsive border border-bottom-0 rounded mb-4">
        <h5>Additional Items</h5>
        <table class="table">
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->additionalItems as $addedItem)
                    <tr>
                        <td>{{ $addedItem->product->name }}</td>
                        <td>${{ number_format($addedItem->price, 2) }}</td>
                        <td>{{ $addedItem->quantity }}</td>
                        <td>${{ number_format($addedItem->total_price, 2) }}</td>
                        @if ($invoice->category->name === 'daily')
                            <td>{{ optional($addedItem->rental_start_date)->format('d/m/Y') }}</td>
                            <td>{{ optional($addedItem->rental_end_date)->format('d/m/Y') }}</td>
                            <td>{{ $addedItem->days }}</td>
                        @endif
                        <td>
                            <form action="{{ route('additional-items.destroy', $addedItem->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this additional item?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
