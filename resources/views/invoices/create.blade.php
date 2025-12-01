@extends('layouts.master')

@section('title', 'Create Invoice')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-invoice.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Invoice</li>
        </ol>
    </nav>

    <div class="row g-6">
        <div class="col-md">
            <div class="card">
                <h5 class="card-header">Create New Invoice</h5>
                <div class="card-body">
                    <form action="{{ route('invoices.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Select Existing Customer --}}
                        <div class="mb-4 row">
                            <label for="select_customer" class="col-md-2 col-form-label">Select Existing Customer</label>
                            <div class="col-md-10">
                                <select class="form-select" id="select_customer" name="customer_id">
                                    <option value="">Select Existing Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-name="{{ $customer->name }}"
                                            data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}">
                                            {{ $customer->name }} ({{ $customer->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-warning mt-2" id="clear_customer_form">Clear
                                    Form</button>
                            </div>
                        </div>

                        {{-- Customer Information --}}
                        <div class="mb-4 row">
                            <label for="customer_name" class="col-md-2 col-form-label">Customer Name</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="customer_name" name="customer_name" />
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label for="customer_phone" class="col-md-2 col-form-label">Customer Phone</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="customer_phone" name="customer_phone" />
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label for="customer_address" class="col-md-2 col-form-label">Customer Address</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="customer_address" name="customer_address" />
                            </div>
                        </div>

                        {{-- Rental Dates --}}
                        @if (session('category') === 'daily')
                            <div class="mb-4 row">
                                <label for="rental_start_date" class="col-md-2 col-form-label">Rental Start Date</label>
                                <div class="col-md-10">
                                    <input class="form-control" type="datetime-local" id="rental_start_date"
                                        name="rental_start_date" required />
                                </div>
                            </div>

                            <div class="mb-4 row">
                                <label for="rental_end_date" class="col-md-2 col-form-label">Rental End Date</label>
                                <div class="col-md-10">
                                    <input class="form-control" type="datetime-local" id="rental_end_date"
                                        name="rental_end_date" required />
                                </div>
                            </div>
                        @endif

                        {{-- Invoice Items --}}
                        <div class="mb-4 row">
                            <label for="items" class="col-md-2 col-form-label">Invoice Items</label>
                            <div class="col-md-10">
                                <table class="table" id="invoice-items-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-select product-select" name="products[]">
                                                    <option value="">Select Product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            data-price="{{ $product->price }}"
                                                            data-type="{{ $product->type }}">{{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control quantity" name="quantities[]"
                                                    value="1" /></td>
                                            <td><input type="text" class="form-control price" name="prices[]"
                                                    value="0.00" /></td>
                                            <td><input type="text" class="form-control total-price" name="total_price[]"
                                                    value="0.00" readonly /></td>
                                            <td><button type="button" class="btn btn-danger remove-item">Remove</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br>
                                <button type="button" class="btn btn-success" id="add-item">Add Item</button>

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#customItemModal">
                                    Add Custom Item
                                </button>

                            </div>
                        </div>

                        {{-- Discount --}}

                        <div class="mb-4 row">
                            <label for="total_discount" class="col-md-2 col-form-label">Total Discount (%)</label>
                            <div class="col-md-10">
                                <input type="number" class="form-control" id="total_discount" name="total_discount"
                                    value="0" min="0" max="100" />
                            </div>
                        </div>

                        {{-- Deposit --}}
                        <div class="mb-4 row">
                            <label for="deposit" class="col-md-2 col-form-label">Deposit</label>
                            <div class="col-md-10">
                                <input type="number" class="form-control" id="deposit" name="deposit" value="0"
                                    min="0" />
                            </div>
                        </div>


                        {{-- Days --}}
                        @if (session('category') === 'daily')
                            <div class="mb-4 row">
                                <label for="days" class="col-md-2 col-form-label">Days</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="days" name="days"
                                        value="0" />
                                </div>
                            </div>
                        @endif


                        {{-- Payment Amount --}}
                        <div class="mb-4 row">
                            <label for="payment_amount" class="col-md-2 col-form-label">Payment Amount</label>
                            <div class="col-md-10">
                                <input type="number" class="form-control" id="payment_amount" name="payment_amount"
                                    value="0" min="0" />
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label for="total_amount" class="col-md-2 col-form-label">Total Amount</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="total_amount" name="total_amount"
                                    value="0.00"/>
                            </div>
                        </div>

                        <div class="mb-4 row">
                            <label class="col-md-2 col-form-label">Remaining Balance</label>
                            <div class="col-md-10">
                                <p id="remaining_balance" class="form-control-static">0.00</p>
                            </div>
                        </div>



                        {{-- Payment Method --}}
                        <div class="mb-4 row">
                            <label class="col-md-2 col-form-label">Payment Method</label>
                            <div class="col-md-10">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                        id="payment_cash" value="cash" checked>
                                    <label class="form-check-label" for="payment_cash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                        id="payment_credit_card" value="credit_card">
                                    <label class="form-check-label" for="payment_credit_card">Credit Card</label>
                                </div>
                            </div>
                        </div>


                        <div class="mb-4 row">
                            <label for="note" class="col-md-2 col-form-label">Note</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="note" name="note" />
                            </div>
                        </div>


                        {{-- Create Button --}}
                        <div class="mt-4 row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary" id="create-invoice-button" disabled>Create
                                    Invoice</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="customItemModal" tabindex="-1" aria-labelledby="customItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customItemModalLabel">Add Custom Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="customItemForm">
                        <div class="mb-3">
                            <label for="custom-item-name" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="custom-item-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="custom-item-price" class="form-label">Price ($)</label>
                            <input type="number" class="form-control" id="custom-item-price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="custom-item-quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="custom-item-quantity" value="1"
                                min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add to Invoice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Include jQuery, Flatpickr, and Select2 CDN --}}
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
    <script>
        const category = "{{ session('category', 'daily') }}";
        let customItemIndex = 0; // Track custom items

        // Initialize date pickers for rental start and end dates
        if (category === 'daily') {
            flatpickr("#rental_start_date, #rental_end_date", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                allowInput: true,
                onChange: function() {
                    updateDaysFromDates();
                    calculateInvoiceTotal();
                }
            });
        }

        // Initialize Select2 for customer selection
        $('#select_customer').select2({
            placeholder: 'Select Existing Customer',
            allowClear: true
        });

        // Handle customer selection changes
        $('#select_customer').on('change', function() {
            const selectedCustomer = $('#select_customer option:selected');
            $('#customer_name').val(selectedCustomer.data('name') || '');
            $('#customer_phone').val(selectedCustomer.data('phone') || '');
            $('#customer_address').val(selectedCustomer.data('address') || '');
            checkFormValidity();
        });

        // Clear customer form
        $('#clear_customer_form').on('click', function() {
            $('#select_customer').val(null).trigger('change');
            $('#customer_name, #customer_phone, #customer_address').val('');
            checkFormValidity();
        });

        // Update days field based on rental start and end dates
        function updateDaysFromDates() {
            const startDate = new Date($('#rental_start_date').val());
            const endDate = new Date($('#rental_end_date').val());

            if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                $('#days').val(diffDays);
            }
        }

        // Calculate total for individual rows
        function calculateRowTotal(row) {
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const days = Math.max(parseInt($('#days').val()) || 1, 1);
            row.find('.total-price').val((quantity * price * days).toFixed(2));
            calculateInvoiceTotal();
        }

        function checkFormValidity() {
            const hasCustomer = $('#select_customer').val() || ($('#customer_name').val() && $('#customer_phone').val() &&
                $('#customer_address').val());
            let hasProductsOrCustomItems = false;

            // Check if there are products in the table
            $('#invoice-items-table .product-select').each(function() {
                if ($(this).val() && parseFloat($(this).closest('tr').find('.quantity').val()) > 0) {
                    hasProductsOrCustomItems = true;
                    return false; // Break loop
                }
            });

            // Check if there are custom items in the table
            $('#invoice-items-table tbody tr').each(function() {
                const isCustomItem = !$(this).find('.product-select').length;
                const quantity = parseFloat($(this).find('.quantity').val()) || 0;

                if (isCustomItem && quantity > 0) {
                    hasProductsOrCustomItems = true;
                    return false; // Break loop
                }
            });

            const totalAmount = parseFloat($('#total_amount').val()) || 0;
            const paymentAmount = parseFloat($('#payment_amount').val()) || 0;

            const isValidPayment = paymentAmount <= totalAmount;
            const isFormValid = hasCustomer && hasProductsOrCustomItems && isValidPayment;

            $('#create-invoice-button').prop('disabled', !isFormValid);
        }

        // Event handlers for payment amount
        $('#payment_amount').on('input', function() {
            calculateInvoiceTotal();
            checkFormValidity();
        });

        // Other necessary event handlers
        $('#total_discount, #deposit, #days').on('input', function() {
            calculateInvoiceTotal();
            checkFormValidity();
        });

        // Calculate the overall invoice total
        function calculateInvoiceTotal() {
            let subtotal = 0;
            let fixedTotal = 0;
            const days = Math.max(parseInt($('#days').val()) || 1, 1);

            $('#invoice-items-table tbody tr').each(function() {
                const row = $(this);
                const productType = row.find('.product-select option:selected').data('type');
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;

                if (productType === 'fixed') {
                    fixedTotal += quantity * price;
                } else {
                    subtotal += quantity * price * days;
                }

                row.find('.total-price').val((quantity * price * days).toFixed(2));
            });

            const discount = parseFloat($('#total_discount').val()) || 0;
            const discountAmount = (subtotal * discount) / 100;
            const deposit = parseFloat($('#deposit').val()) || 0;
            const paymentAmount = parseFloat($('#payment_amount').val()) || 0;

            const totalBeforePayment = (subtotal - discountAmount) + fixedTotal - deposit;
            const totalAfterPayment = totalBeforePayment - paymentAmount;

            const finalTotal = totalAfterPayment > 0 ? totalAfterPayment : 0;

            $('#total_amount').val(finalTotal.toFixed(2));
            $('#remaining_balance').text(finalTotal.toFixed(2));

            checkFormValidity();
        }

        // Product and quantity changes
        $(document).on('change', '.product-select', function() {
            const row = $(this).closest('tr');
            const price = parseFloat($(this).find('option:selected').data('price')) || 0;
            row.find('.price').val(price.toFixed(2));
            calculateRowTotal(row);
        });

        $(document).on('input', '.quantity', function() {
            calculateRowTotal($(this).closest('tr'));
        });

        $(document).on('input', '.price', function() {
    calculateRowTotal($(this).closest('tr'));
});

        // Add a new item row
        $('#add-item').on('click', function() {
            const newRow = `
                <tr>
                    <td>
                        <select class="form-select product-select" name="products[]">
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-type="{{ $product->type }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" class="form-control quantity" name="quantities[]" value="1" /></td>
<td><input type="number" class="form-control price" name="prices[]" value="0.00" step="0.01" min="0" /></td>
                    <td><input type="text" class="form-control total-price" name="total_price[]" value="0.00" readonly /></td>
                    <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
                </tr>`;
            $('#invoice-items-table tbody').append(newRow);
            checkFormValidity();
        });

        // Remove an item row
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            calculateInvoiceTotal();
        });

        // Custom Item Addition
        $('#customItemForm').on('submit', function(e) {
            e.preventDefault();

            const itemName = $('#custom-item-name').val();
            const itemPrice = parseFloat($('#custom-item-price').val());
            const itemQuantity = parseInt($('#custom-item-quantity').val());
            const days = Math.max(parseInt($('#days').val()) || 1, 1);

            const newRow = `
                <tr>
                    <td><input type="text" class="form-control" name="custom_items[${customItemIndex}][name]" value="${itemName}" readonly></td>
                    <td><input type="number" class="form-control quantity" name="custom_items[${customItemIndex}][quantity]" value="${itemQuantity}" readonly></td>
                    <td><input type="number" class="form-control price" name="custom_items[${customItemIndex}][price]" value="${itemPrice.toFixed(2)}" readonly></td>
                    <td><input type="number" class="form-control total-price" name="custom_items[${customItemIndex}][total]" value="${(itemPrice * itemQuantity * days).toFixed(2)}" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-item">Remove</button></td>
                </tr>`;

            $('#invoice-items-table tbody').append(newRow);
            customItemIndex++;

            $('#customItemModal').modal('hide');
            $('#customItemForm')[0].reset();

            calculateInvoiceTotal();
            checkFormValidity();
        });

        // Trigger recalculations on input changes
        $('#total_discount, #deposit, #payment_amount, #days').on('input', calculateInvoiceTotal);
    </script>


@endsection
