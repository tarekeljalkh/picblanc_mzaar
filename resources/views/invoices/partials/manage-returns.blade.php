<form action="{{ route('invoices.process-returns', $invoice->id) }}" method="POST">
    @csrf
    <div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Select</th>
                <th>Product</th>
                <th>Total Quantity</th>
                <th>Remaining Quantity</th>
                <th>Returned Quantity</th>
                @if (session('category') === 'daily')
                    <th>Return Date</th>
                    <th>Days of Use</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <!-- Original Items -->
            @foreach ($invoice->items as $item)
                @if ($item->quantity - $item->returned_quantity > 0)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[original][{{ $item->id }}][selected]" value="1"
                                {{ old("returns.original.{$item->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->quantity - $item->returned_quantity }}</td>
                        <td>
                            <input type="number" class="form-control return-quantity"
                                name="returns[original][{{ $item->id }}][quantity]"
                                max="{{ $item->quantity - $item->returned_quantity }}"
                                value="{{ old("returns.original.{$item->id}.quantity") }}"
                                {{ old("returns.original.{$item->id}.selected") ? '' : 'disabled' }}>
                        </td>
                        @if (session('category') === 'daily')
                            <td>
                                <input type="datetime-local" class="form-control return-date"
                                    name="returns[original][{{ $item->id }}][return_date]"
                                    data-start-date="{{ $item->rental_start_date }}"
                                    value="{{ old("returns.original.{$item->id}.return_date") }}"
                                    {{ old("returns.original.{$item->id}.selected") ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <input type="number" class="form-control days-of-use"
                                    name="returns[original][{{ $item->id }}][days_of_use]"
                                    value="{{ old("returns.original.{$item->id}.days_of_use") }}"
                                    {{ old("returns.original.{$item->id}.selected") ? '' : 'disabled' }}>
                            </td>
                        @endif
                    </tr>
                @endif
            @endforeach

            <!-- Additional Items -->
            @foreach ($invoice->additionalItems as $addedItem)
                @if ($addedItem->quantity - $addedItem->returned_quantity > 0)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[additional][{{ $addedItem->id }}][selected]" value="1"
                                {{ old("returns.additional.{$addedItem->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $addedItem->product->name }}</td>
                        <td>{{ $addedItem->quantity }}</td>
                        <td>{{ $addedItem->quantity - $addedItem->returned_quantity }}</td>
                        <td>
                            <input type="number" class="form-control return-quantity"
                                name="returns[additional][{{ $addedItem->id }}][quantity]"
                                max="{{ $addedItem->quantity - $addedItem->returned_quantity }}"
                                value="{{ old("returns.additional.{$addedItem->id}.quantity") }}"
                                {{ old("returns.additional.{$addedItem->id}.selected") ? '' : 'disabled' }}>
                        </td>
                        @if (session('category') === 'daily')
                            <td>
                                <input type="datetime-local" class="form-control return-date"
                                    name="returns[additional][{{ $addedItem->id }}][return_date]"
                                    data-start-date="{{ $addedItem->rental_start_date }}"
                                    value="{{ old("returns.additional.{$addedItem->id}.return_date") }}"
                                    {{ old("returns.additional.{$addedItem->id}.selected") ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <input type="number" class="form-control days-of-use"
                                    name="returns[additional][{{ $addedItem->id }}][days_of_use]"
                                    value="{{ old("returns.additional.{$addedItem->id}.days_of_use") }}"
                                    {{ old("returns.additional.{$addedItem->id}.selected") ? '' : 'disabled' }}>
                            </td>
                        @endif
                    </tr>
                @endif
            @endforeach

            <!-- Custom Items -->
            @foreach ($invoice->customItems as $customItem)
                @if ($customItem->quantity - $customItem->returned_quantity > 0)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input return-checkbox"
                                name="returns[custom][{{ $customItem->id }}][selected]" value="1"
                                {{ old("returns.custom.{$customItem->id}.selected") ? 'checked' : '' }}>
                        </td>
                        <td>{{ $customItem->name }}</td>
                        <td>{{ $customItem->quantity }}</td>
                        <td>{{ $customItem->quantity - $customItem->returned_quantity }}</td>
                        <td>
                            <input type="number" class="form-control return-quantity"
                                name="returns[custom][{{ $customItem->id }}][quantity]"
                                max="{{ $customItem->quantity - $customItem->returned_quantity }}"
                                value="{{ old("returns.custom.{$customItem->id}.quantity") }}"
                                {{ old("returns.custom.{$customItem->id}.selected") ? '' : 'disabled' }}>
                        </td>
                        @if (session('category') === 'daily')
                            <td>
                                <input type="datetime-local" class="form-control return-date"
                                    name="returns[custom][{{ $customItem->id }}][return_date]"
                                    data-start-date="{{ $customItem->rental_start_date }}"
                                    value="{{ old("returns.custom.{$customItem->id}.return_date") }}"
                                    {{ old("returns.custom.{$customItem->id}.selected") ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <input type="number" class="form-control days-of-use"
                                    name="returns[custom][{{ $customItem->id }}][days_of_use]"
                                    value="{{ old("returns.custom.{$customItem->id}.days_of_use") }}"
                                    {{ old("returns.custom.{$customItem->id}.selected") ? '' : 'disabled' }}>
                            </td>
                        @endif
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-warning">Process Returns</button>
    </div>
</form>


@push('scripts')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function initializeFlatpickr() {
        document.querySelectorAll('.return-date').forEach(input => {
            const startDate = input.getAttribute('data-start-date');

            flatpickr(input, {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                minDate: startDate,
                onChange: function () {
                    calculateDaysOfUse(input);
                },
            });
        });
    }

    initializeFlatpickr();

    document.querySelectorAll('.return-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const row = this.closest('tr');
            const quantityInput = row.querySelector('.return-quantity');
            const dateInput = row.querySelector('.return-date');
            const daysOfUseInput = row.querySelector('.days-of-use');

            if (this.checked) {
                // Enable inputs
                quantityInput.removeAttribute('disabled');
                dateInput.removeAttribute('disabled');
                if (daysOfUseInput) {
                    daysOfUseInput.removeAttribute('disabled');
                }

                // Reinitialize Flatpickr for the now-enabled date input
                flatpickr(dateInput, {
                    enableTime: true,
                    dateFormat: 'Y-m-d H:i',
                    minDate: dateInput.getAttribute('data-start-date'),
                    onChange: function () {
                        calculateDaysOfUse(dateInput);
                    },
                });
            } else {
                // Disable inputs and clear values
                quantityInput.setAttribute('disabled', 'true');
                quantityInput.value = '';
                dateInput.setAttribute('disabled', 'true');
                dateInput.value = '';
                if (daysOfUseInput) {
                    daysOfUseInput.setAttribute('disabled', 'true');
                    daysOfUseInput.value = '';
                }
            }
        });
    });

    function calculateDaysOfUse(input) {
        const row = input.closest('tr');
        const rentalStartDate = new Date(input.getAttribute('data-start-date'));
        const returnDate = new Date(input.value);
        const daysOfUseInput = row.querySelector('.days-of-use');

        if (!isNaN(rentalStartDate) && !isNaN(returnDate) && returnDate >= rentalStartDate) {
            const diffTime = returnDate - rentalStartDate;
            const daysUsed = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            daysOfUseInput.value = Math.max(1, daysUsed);
        }
    }
});
</script>
@endpush
