<form action="{{ route('invoices.add-items', $invoice->id) }}" method="POST" id="addItemsForm">
    @csrf
    <table class="table" id="itemsTable">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                @if (session('category') === 'daily')
                    <th>Days</th>
                    <th>Rental Start Date</th>
                    <th>Rental End Date</th>
                @else
                    <th>Season</th>
                @endif
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <select name="products[0][product_id]" class="form-select product-select" required>
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="products[0][quantity]" class="form-control quantity-input" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" name="products[0][price]" class="form-control price-input" value="0.00" readonly>
                </td>
                @if (session('category') === 'daily')
                    <td>
                        <input type="number" name="products[0][days]" class="form-control days-input" value="1" min="1" data-manual="false">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[0][rental_start_date]" class="form-control rental-start-date">
                    </td>
                    <td>
                        <input type="datetime-local" name="products[0][rental_end_date]" class="form-control rental-end-date">
                    </td>
                @else
                    <td>
                        <input type="text" value="Seasonal Rental" class="form-control" readonly>
                    </td>
                @endif
                <td>
                    <input type="text" class="form-control total-price" value="0.00" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row">Remove</button>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                <td>
                    <input type="text" id="grandTotal" class="form-control" value="0.00" readonly>
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-success" id="addRow">Add Row</button>
        <button type="submit" class="btn btn-primary">Add Items</button>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIndex = 1;
        const isSeasonal = @json(session('category') === 'season');

        initializeFlatpickr();

        // Add a new row
        document.getElementById('addRow').addEventListener('click', function () {
            const tableBody = document.querySelector('#itemsTable tbody');
            let newRow = `
                <tr>
                    <td>
                        <select name="products[${rowIndex}][product_id]" class="form-select product-select" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="products[${rowIndex}][quantity]" class="form-control quantity-input" value="1" min="1" required>
                    </td>
                    <td>
                        <input type="text" name="products[${rowIndex}][price]" class="form-control price-input" value="0.00" readonly>
                    </td>`;
            if (isSeasonal) {
                newRow += `<td><input type="text" value="Seasonal Rental" class="form-control" readonly></td>`;
            } else {
                newRow += `
                    <td><input type="number" name="products[${rowIndex}][days]" class="form-control days-input" value="1" min="1" data-manual="false"></td>
                    <td><input type="datetime-local" name="products[${rowIndex}][rental_start_date]" class="form-control rental-start-date"></td>
                    <td><input type="datetime-local" name="products[${rowIndex}][rental_end_date]" class="form-control rental-end-date"></td>`;
            }

            newRow += `
                    <td><input type="text" class="form-control total-price" value="0.00" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                </tr>`;
            tableBody.insertAdjacentHTML('beforeend', newRow);
            initializeFlatpickr();
            rowIndex++;
        });

        // Listen for input changes
        document.getElementById('itemsTable').addEventListener('input', function (e) {
            const row = e.target.closest('tr');
            const isDaysField = e.target.classList.contains('days-input');
            const isStartEndField = e.target.classList.contains('rental-start-date') || e.target.classList.contains('rental-end-date');

            if (isDaysField) {
                // Mark the days field as manually adjusted
                e.target.setAttribute('data-manual', 'true');
            } else if (isStartEndField) {
                // Reset manual override when start/end dates are adjusted
                const daysInput = row.querySelector('.days-input');
                daysInput.setAttribute('data-manual', 'false');
                calculateDays(row); // Recalculate days
            }

            updateRowTotals(row);
            calculateGrandTotal();
        });

        // Update totals for a row
        function updateRowTotals(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const daysInput = row.querySelector('.days-input');
            const totalPriceInput = row.querySelector('.total-price');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            const quantity = parseFloat(quantityInput.value) || 1;
            const days = parseFloat(daysInput?.value) || 1;

            const total = price * quantity * days;
            priceInput.value = price.toFixed(2);
            totalPriceInput.value = total.toFixed(2);
        }

        // Calculate days for daily rentals
        function calculateDays(row) {
            const startInput = row.querySelector('.rental-start-date');
            const endInput = row.querySelector('.rental-end-date');
            const daysInput = row.querySelector('.days-input');

            // Skip calculation if manually adjusted
            if (daysInput.getAttribute('data-manual') === 'true') return;

            const startDate = new Date(startInput?.value);
            const endDate = new Date(endInput?.value);

            if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
                const diffDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                daysInput.value = Math.max(1, diffDays); // At least 1 day
            }
        }

        // Calculate grand total
        function calculateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.total-price').forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            document.getElementById('grandTotal').value = grandTotal.toFixed(2);
        }

        function initializeFlatpickr() {
            if (!isSeasonal) {
                flatpickr('.rental-start-date, .rental-end-date', {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                });
            }
        }
    });
</script>
@endpush
