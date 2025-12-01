@extends('layouts.master')

@section('title', 'POS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <!-- Left Column: Products -->
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <!-- Real-Time Search Input -->
                    <input type="text" id="search-product" class="form-control" placeholder="Search products...">
                </div>

                <!-- Products Grid -->
                <div class="row" id="product-list">
                    @foreach ($products as $product)
                        <div class="col-md-3 col-sm-6 col-12 mb-4 product-card-container">
                            <div class="card product-card clickable-card" data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}" data-price="{{ $product->price }}"
                                data-type="{{ $product->type }}">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text">{{ $product->description }}</p>
                                    <p class="card-text">${{ number_format($product->price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Customer Selection and Cart -->
            <div class="col-md-4" style="background-color: #ffffff;">
                <h4 class="my-4">Select Customer</h4>
                <div class="mb-6 d-flex justify-content-between">
                    <!-- Customer Dropdown with Search -->
                    <select id="customer-select" class="select2 form-select form-select-lg" data-allow-clear="true">
                        <option value="" disabled selected>Select a customer</option>
                        @foreach ($customers as $customer)
                            @php
                                $phones = collect([$customer->phone, $customer->phone2])
                                    ->filter()
                                    ->implode(', '); // Combine phone and phone2
                            @endphp
                            <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}"
                                {{ session('new_customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} ({{ $phones }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Button to Create New Customer -->
                    <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                        Create New Customer
                    </button>
                </div>

                <!-- Cart Section -->
                <h4>Your Cart</h4>
                <div id="cart" class="border p-3 mb-4" style="min-height: 200px;">
                    <p>No items in the cart.</p>
                </div>

                <!-- Rental Dates and Days Calculation -->
                @if (session('category') === 'daily')
                    <div class="mb-3">
                        <label for="rental-start-date" class="form-label">Rental Start Date</label>
                        <input type="text" class="form-control" id="rental-start-date">
                    </div>

                    <div class="mb-3">
                        <label for="rental-end-date" class="form-label">Rental End Date</label>
                        <input type="text" class="form-control" id="rental-end-date">
                    </div>

                    <div class="mb-3">
                        <label for="rental-days" class="form-label">Days</label>
                        <input type="number" class="form-control" id="rental-days">
                    </div>
                @endif

                <button class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#addCustomItemModal">
                    Add Custom Product
                </button>
                <br><br>

                <!-- Discount, and Total Amount -->
                <div class="mb-3">
                    <label for="total-discount" class="form-label">Total Discount (%)</label>
                    <input type="number" class="form-control" id="total-discount" value="0">
                </div>

                <!-- Deposit -->
                <div class="mb-3">
                    <label for="deposit" class="form-label">Deposit ($)</label>
                    <input type="number" class="form-control" id="deposit" value="0" min="0">
                </div>

                <!-- Payment Amount -->

                <div class="mb-3">
                    <label for="payment-amount" class="form-label">Payment Amount ($)</label>
                    <input type="number" class="form-control" id="payment-amount" value="0" min="0">
                </div>


                <!-- Payment Method -->
                <div class="mb-4">
                    <label class="form-label">Payment Method</label><br>
                    <input class="form-check-input" type="radio" name="payment_method" id="cash-radio" value="cash"
                        checked>
                    <label class="form-check-label" for="cash-radio">Cash</label>

                    <input class="form-check-input ms-2" type="radio" name="payment_method" id="credit-radio"
                        value="credit_card">
                    <label class="form-check-label" for="credit-radio">Credit Card</label>
                </div>

                <!-- Total Amount -->
                <div class="mb-3">
                    <label for="total-amount" class="form-label">Total Amount</label>
                    <input type="text" class="form-control" id="total-amount" readonly>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="note" class="form-label">Note</label>
                    <input type="text" class="form-control" id="note">
                </div>

                <!-- Checkout Button -->
                <button class="btn w-100" id="checkout-btn" disabled>Checkout</button>
            </div>
        </div>
    </div>

    <!-- Modal for creating a new customer -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCustomerModalLabel">Create New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('pos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="customer_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="customer_phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone2" class="form-label">Second Phone</label>
                            <input type="text" class="form-control" id="customer_phone2" name="phone2">
                        </div>
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="customer_address" name="address">
                        </div>
                        <div class="mb-3">
                            <label for="deposit_card" class="form-label">Deposit Card</label>
                            <input type="file" class="form-control" id="deposit_card" name="deposit_card">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal for Adding Custom Item -->
    <div class="modal fade" id="addCustomItemModal" tabindex="-1" aria-labelledby="addCustomItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomItemModalLabel">Add Custom Item</h5>
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
                        <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
        <script src="{{ asset('assets/js/forms-selects.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
        <script>
            let cart = [];

            $(document).ready(function() {
                // Initialize Select2 for customer selection
                $('#customer-select').select2({
                    placeholder: 'Select A Customer',
                    allowClear: true,
                }).on('change', function() {
                    validateCheckoutButton();
                });

                // Initialize Flatpickr for rental dates (only if required for 'daily')
                const category = '{{ session('category', 'daily') }}'; // Retrieve the category from the session
                if (category === 'daily') {
                    flatpickr("#rental-start-date, #rental-end-date", {
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "F j, Y",
                        allowInput: true,
                        onChange: function() {
                            calculateDays();
                            calculateTotalAmount();
                            validateCheckoutButton();
                        },
                    });
                }

                // Validate checkout button
                function validateCheckoutButton() {
                    const isCartEmpty = cart.length === 0; // Check if cart is empty
                    const customerSelected = $('#customer-select').val(); // Check if customer is selected
                    const remainingBalance = parseFloat($('#remaining_balance').text()) || 0; // Get remaining balance
                    const rentalStartDate = $('#rental-start-date').val(); // Check rental start date
                    const rentalEndDate = $('#rental-end-date').val(); // Check rental end date

                    let isValid = customerSelected && !isCartEmpty;

                    // Additional validation: rental dates must be selected if category is daily
                    if (category === 'daily' && (!rentalStartDate || !rentalEndDate)) {
                        isValid = false;
                        $('#checkout-btn').hide(); // Hide the button if dates are missing
                        return; // Stop further validation
                    }

                    $('#checkout-btn').show(); // Ensure the button is visible if all conditions are met

                    // Additional validation for remaining balance
                    if (isValid && remainingBalance < 0) {
                        isValid = false;
                        alert('Payment amount exceeds the total. Please adjust the payment.');
                    }

                    // Enable or disable the button based on validity
                    $('#checkout-btn').prop('disabled', !isValid);

                    // Update button text and styling
                    const checkoutBtn = $('#checkout-btn');
                    if (isValid) {
                        checkoutBtn.removeClass('btn-danger').addClass('btn-success').text('Checkout');
                    } else {
                        checkoutBtn.removeClass('btn-success').addClass('btn-danger').text('Invalid or Incomplete');
                    }
                }

                // Real-time product search
                $('#search-product').on('input', function() {
                    const searchTerm = $(this).val().toLowerCase();
                    $('#product-list .product-card-container').each(function() {
                        const productName = $(this).find('.card-title').text().toLowerCase();
                        $(this).toggle(productName.includes(searchTerm));
                    });
                });

                // Add product to cart
                $('.clickable-card').on('click', function() {
                    const productId = $(this).data('id');
                    const productName = $(this).data('name');
                    const productPrice = parseFloat($(this).data('price'));
                    const productType = $(this).data('type');

                    let productInCart = cart.find(item => item.id === productId);
                    if (productInCart) {
                        productInCart.quantity += 1;
                    } else {
                        cart.push({
                            id: productId,
                            name: productName,
                            price: productPrice,
                            type: productType,
                            quantity: 1,
                        });
                    }
                    renderCart();
                    validateCheckoutButton();
                });

                // Add custom item to cart (Season category only)
                $('#customItemForm').on('submit', function(e) {
                    e.preventDefault();
                    const name = $('#custom-item-name').val();
                    const price = parseFloat($('#custom-item-price').val());
                    const quantity = parseInt($('#custom-item-quantity').val());

                    cart.push({
                        id: null, // Custom items have no product ID
                        name: name,
                        price: price,
                        quantity: quantity,
                        type: 'custom' // Mark as custom
                    });

                    renderCart();
                    calculateTotalAmount();
                    $('#addCustomItemModal').modal('hide'); // Close the modal
                    validateCheckoutButton(); // Re-validate checkout button
                });

                // $('#customItemForm').on('submit', function(e) {
                //     e.preventDefault();
                //     const name = $('#custom-item-name').val();
                //     const price = parseFloat($('#custom-item-price').val());
                //     const quantity = parseInt($('#custom-item-quantity').val());

                //     cart.push({
                //         id: null, // Custom items have no product ID
                //         name: name,
                //         price: price,
                //         quantity: quantity,
                //         type: 'custom' // Mark as custom
                //     });

                //     renderCart();
                //     calculateTotalAmount();
                //     $('#addCustomItemModal').modal('hide'); // Close the modal
                // });

                // Trigger recalculations when discount, deposit, payment amount, or days change
                $('#total-discount, #deposit, #payment-amount, #rental-days').on('input', function() {
                    calculateTotalAmount();
                    validateCheckoutButton();
                });

                // Function to calculate rental days
                function calculateDays() {
                    const startDate = new Date($('#rental-start-date').val());
                    const endDate = new Date($('#rental-end-date').val());
                    let days = 0;

                    if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
                        const diffTime = endDate - startDate;
                        const totalHours = diffTime / (1000 * 60 * 60);
                        days = Math.ceil(totalHours / 24) + 1; // Round up to ensure partial days count
                        days = Math.max(1, days); // Ensure at least 1 day
                    }

                    $('#rental-days').val(days); // Update the days input field
                    calculateTotalAmount();
                }

                // Function to calculate total amount and remaining balance
                function calculateTotalAmount() {
                    const days = parseInt($('#rental-days').val()) || 1; // Rental days
                    const totalDiscount = parseFloat($('#total-discount').val()) || 0; // Discount %
                    const deposit = parseFloat($('#deposit').val()) || 0; // Deposit amount
                    const paymentAmount = parseFloat($('#payment-amount').val()) || 0; // Payment amount

                    // Calculate subtotal from cart items
                    let total = cart.reduce((sum, item) => {
                        if (item.type === 'fixed') {
                            return sum + (item.price * item.quantity); // Fixed products
                        } else {
                            return sum + (item.price * item.quantity * days); // Per-day products
                        }
                    }, 0);

                    // Apply discount
                    const discountAmount = (total * totalDiscount) / 100;

                    // Calculate grand total (subtotal - discount - deposit)
                    let grandTotal = total - discountAmount - deposit;

                    // Subtract payment amount from grand total
                    grandTotal -= paymentAmount;

                    // Ensure grand total is not negative
                    grandTotal = Math.max(0, grandTotal);

                    // Update Total Amount in the input field
                    $('#total-amount').val(grandTotal.toFixed(2));

                    // Update Remaining Balance display dynamically
                    if ($('#remaining_balance').length === 0) {
                        $('#payment-amount').after(`
                        <div class="mt-2">
                            <label class="form-label">Remaining Balance ($)</label>
                            <span id="remaining_balance" class="form-control" readonly>0.00</span>
                        </div>
                    `);
                    }

                    $('#remaining_balance').text(grandTotal.toFixed(2)); // Update remaining balance display
                }

                // Render the cart dynamically
                function renderCart() {
                    let cartHtml = '';
                    if (cart.length > 0) {
                        cart.forEach((item, index) => {
                            cartHtml += `
                            <div class="cart-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>${item.name}</strong></span>
                                    <button class="btn btn-danger btn-sm remove-from-cart" data-index="${index}">Remove</button>
                                </div>
                                <div class="form-row mt-2">
                                    <div class="d-flex justify-content-between">
                                        <div class="col">
                                            <label>Qty</label>
                                            <input type="number" class="form-control form-control-sm quantity" data-index="${index}" value="${item.quantity}" />
                                        </div>
                                        <div class="col">
                                            <label>Price</label>
            <input type="number" class="form-control form-control-sm price" data-index="${index}" value="${item.price.toFixed(2)}" step="0.01" min="0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                    } else {
                        cartHtml = '<p>No items in the cart.</p>';
                    }

                    $('#cart').html(cartHtml);
                    attachCartListeners();
                    calculateTotalAmount();
                }

                // Attach event listeners for cart actions
                function attachCartListeners() {
                    $('.quantity').on('input', function() {
                        const index = $(this).data('index');
                        cart[index].quantity = parseFloat($(this).val()) || 1;
                        renderCart();
                        validateCheckoutButton();
                    });

                    $('.price').on('input', function() {
                        const index = $(this).data('index');
                        const newPrice = parseFloat($(this).val()) || 0;
                        cart[index].price = newPrice;
                        calculateTotalAmount(); // Recalculate totals
                    });

                    $('.remove-from-cart').on('click', function() {
                        const index = $(this).data('index');
                        cart.splice(index, 1);
                        renderCart();
                        validateCheckoutButton();
                    });
                }

                // Checkout button click handler
                $('#checkout-btn').on('click', function() {
                    const selectedCustomer = $('#customer-select').val();
                    const paymentMethod = $('input[name="payment_method"]:checked').val();

                    const startDateWithTime = $('#rental-start-date').val();
                    const endDateWithTime = $('#rental-end-date').val();

                    $.ajax({
                        url: '{{ route('pos.checkout') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            cart: cart,
                            customer_id: selectedCustomer,
                            total_discount: $('#total-discount').val(),
                            deposit: $('#deposit').val(),
                            payment_method: paymentMethod,
                            rental_days: $('#rental-days').val(),
                            rental_start_date: startDateWithTime,
                            rental_end_date: endDateWithTime,
                            total_amount: $('#total-amount').val(),
                            note: $('#note').val(),
                            payment_amount: $('#payment-amount').val(),
                        },
                        success: function(response) {
                            window.location.href = '{{ route('invoices.show', ':id') }}'.replace(
                                ':id', response.invoice_id);
                        },
                        error: function() {
                            alert('Error processing checkout.');
                        },
                    });
                });

                validateCheckoutButton();
            });
        </script>
    @endpush

@endsection
