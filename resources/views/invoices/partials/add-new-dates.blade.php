<form action="{{ route('invoices.add-dates', $invoice->id) }}" method="POST">
    @csrf

    <div class="mb-2">
        <input type="checkbox" id="return-all-checkbox" class="form-check-input me-1">
        <label for="return-all-checkbox" class="form-check-label">Return All Items</label>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Select</th>
                    <th>Product</th>
                    @if (session('category') === 'daily')
                        <th>From Date</th>
                        <th>To Date</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <!-- Original Items -->
                @foreach ($invoice->invoiceItems as $item)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[original][{{ $item->id }}][selected]" value="1"
                                {{ old("returns.original.{$item->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $item->product->name }}</td>

                        @if (session('category') === 'daily')
                            <td>
                                <input type="date" class="form-control rental-start-date"
                                    name="returns[original][{{ $item->id }}][from]"
                                    value="{{ old("returns.original.{$item->id}.from", optional($item->rental_start_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                            <td>
                                <input type="date" class="form-control rental-end-date"
                                    name="returns[original][{{ $item->id }}][to]"
                                    value="{{ old("returns.original.{$item->id}.to", optional($item->rental_end_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                        @endif
                    </tr>
                @endforeach

                <!-- Additional Items -->
                @foreach ($invoice->additionalItems as $item)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[additional][{{ $item->id }}][selected]" value="1"
                                {{ old("returns.additional.{$item->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $item->product->name }}</td>

                        @if (session('category') === 'daily')
                            <td>
                                <input type="date" class="form-control rental-start-date"
                                    name="returns[additional][{{ $item->id }}][from]"
                                    value="{{ old("returns.additional.{$item->id}.from", optional($item->rental_start_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                            <td>
                                <input type="date" class="form-control rental-end-date"
                                    name="returns[additional][{{ $item->id }}][to]"
                                    value="{{ old("returns.additional.{$item->id}.to", optional($item->rental_end_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                        @endif
                    </tr>
                @endforeach

                <!-- Custom Items -->
                @foreach ($invoice->customItems as $item)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[custom][{{ $item->id }}][selected]" value="1"
                                {{ old("returns.custom.{$item->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $item->name }}</td>

                        @if (session('category') === 'daily')
                            <td>
                                <input type="date" class="form-control rental-start-date"
                                    name="returns[custom][{{ $item->id }}][from]"
                                    value="{{ old("returns.custom.{$item->id}.from", optional($item->rental_start_date ?? $invoice->rental_start_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                            <td>
                                <input type="date" class="form-control rental-end-date"
                                    name="returns[custom][{{ $item->id }}][to]"
                                    value="{{ old("returns.custom.{$item->id}.to", optional($item->rental_end_date ?? $invoice->rental_end_date)->format('Y-m-d')) }}"
                                    disabled>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-warning">Save</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable date inputs when checkbox toggled
    document.querySelectorAll('.return-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const row = cb.closest('tr');
            row.querySelectorAll('.rental-start-date, .rental-end-date')
                .forEach(el => el.disabled = !cb.checked);
        });
    });

    // "Return All" checkbox logic
    document.getElementById('return-all-checkbox').addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.return-checkbox').forEach(cb => {
            cb.checked = isChecked;
            cb.dispatchEvent(new Event('change'));
        });
    });
});
</script>
@endpush
