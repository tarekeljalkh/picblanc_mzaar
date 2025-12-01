<form action="{{ route('invoices.process-returns', $invoice->id) }}" method="POST">
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
                @foreach ($invoice->invoiceItems as $item)
                    @if ($item->quantity - $item->returned_quantity > 0)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input return-checkbox"
                                    name="returns[original][{{ $item->id }}][selected]" value="1"
                                    data-rental-end-date="{{ optional($item->rental_end_date)->format('Y-m-d') }}"
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
                                    <input type="date" class="form-control return-date"
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
                                    data-rental-end-date="{{ optional($addedItem->rental_end_date)->format('Y-m-d') }}"
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
                                    <input type="date" class="form-control return-date"
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
                                    data-rental-end-date="{{ optional($customItem->rental_end_date ?? $invoice->rental_end_date)->format('Y-m-d') }}"
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
                                    <input type="date" class="form-control return-date"
                                        name="returns[custom][{{ $customItem->id }}][return_date]"
                                        data-start-date="{{ $customItem->rental_start_date ?? $invoice->rental_start_date }}"
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
        <button type="submit" class="btn btn-warning">Save</button>
    </div>
</form>


@push('scripts')
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function initializeFlatpickr() {
                document.querySelectorAll('.return-date').forEach(input => {
                    const startDate = input.getAttribute('data-start-date');

                    flatpickr(input, {
                        dateFormat: 'Y-m-d',
                        minDate: startDate || null, // ✅ Fallback to current date if undefined
                        onChange: function() {
                            calculateDaysOfUse(input);
                        },
                    });
                });
            }

            initializeFlatpickr();

            document.getElementById('return-all-checkbox').addEventListener('change', function() {
                const isChecked = this.checked;

                document.querySelectorAll('.return-checkbox').forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = isChecked;

                        const row = cb.closest('tr');
                        const quantityInput = row.querySelector('.return-quantity');
                        const dateInput = row.querySelector('.return-date');
                        const daysOfUseInput = row.querySelector('.days-of-use');
                        const rentalEndDate = cb.dataset.rentalEndDate;

                        if (isChecked) {
                            quantityInput.removeAttribute('disabled');
                            dateInput.removeAttribute('disabled');
                            daysOfUseInput?.removeAttribute('disabled');

                            quantityInput.value = quantityInput.getAttribute('max');

                            const formattedDate = rentalEndDate || new Date().toISOString().split(
                                'T')[0];
                            dateInput.value = formattedDate;
                            calculateDaysOfUse(dateInput);

                            flatpickr(dateInput, {
                                enableTime: false,
                                dateFormat: 'Y-m-d',
                                minDate: dateInput.getAttribute('data-start-date') || null,
                                onChange: function() {
                                    calculateDaysOfUse(dateInput);
                                },
                            });

                            if (daysOfUseInput && !daysOfUseInput.value) {
                                daysOfUseInput.value = 1;
                            }

                        } else {
                            cb.checked = false;

                            quantityInput.setAttribute('disabled', true);
                            quantityInput.value = '';

                            dateInput.setAttribute('disabled', true);
                            dateInput.value = '';

                            if (daysOfUseInput) {
                                daysOfUseInput.setAttribute('disabled', true);
                                daysOfUseInput.value = '';
                            }
                        }
                    }
                });
            });

            function calculateDaysOfUse(input) {
                const row = input.closest('tr');
                const rentalStartDateStr = input.getAttribute('data-start-date');
                const rentalStartDate = rentalStartDateStr ? new Date(rentalStartDateStr) : new Date();
                const returnDate = new Date(input.value);
                const daysOfUseInput = row.querySelector('.days-of-use');

                if (!isNaN(rentalStartDate) && !isNaN(returnDate) && returnDate >= rentalStartDate) {
                    const diffTime = returnDate - rentalStartDate;
                    const daysUsed = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    daysOfUseInput.value = Math.max(1, daysUsed);
                } else {
                    daysOfUseInput.value = '';
                }
            }

        });

        // "Return All" checkbox logic
        document.getElementById('return-all-checkbox').addEventListener('change', function() {
            const isChecked = this.checked;

            document.querySelectorAll('.return-checkbox').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = isChecked;
                    cb.dispatchEvent(new Event('change'));

                    const row = cb.closest('tr');
                    const quantityInput = row.querySelector('.return-quantity');
                    const maxQuantity = quantityInput.getAttribute('max');
                    quantityInput.value = isChecked ? maxQuantity : '';

                    const dateInput = row.querySelector('.return-date');
                    const rentalEndDate = cb.dataset.rentalEndDate; // ✅ Correct now

                    if (isChecked && dateInput) {
                        const formatted = rentalEndDate ? rentalEndDate : new Date().toISOString().split(
                            'T')[0];
                        dateInput.value = formatted;
                        calculateDaysOfUse(dateInput);
                    }

                    const daysOfUseInput = row.querySelector('.days-of-use');
                    if (isChecked && daysOfUseInput) {
                        daysOfUseInput.value = 1;
                    } else if (daysOfUseInput) {
                        daysOfUseInput.value = '';
                    }
                }
            });
        });
    </script>
@endpush
