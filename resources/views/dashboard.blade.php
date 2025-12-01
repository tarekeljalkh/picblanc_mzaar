@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <!-- Card Widgets -->
    <div class="card mb-6">
        <div class="card-widget-separator-wrapper">
            <div class="card-body card-widget-separator">
                <div class="row g-4">
                    <!-- Customers Card -->
                    <div class="col-md-6 col-lg-6">
                        <a href="{{ route('customers.index') }}" class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-primary">{{ $customersCount }}</h4>
                                        <p class="mb-0 fw-bold">Customers</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-primary text-white">
                                            <i class="bx bx-user bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Invoices Card -->
                    <div class="col-md-6 col-lg-6">
                        <a href="{{ route('invoices.index') }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-success">{{ $invoicesCount }}</h4>
                                        <p class="mb-0 fw-bold">Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-success text-white">
                                            <i class="bx bx-file bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Paid Invoices Card -->
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('invoices.index', ['payment_status' => 'fully_paid']) }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-info">{{ $totalPaid }}</h4>
                                        <p class="mb-0 fw-bold">Paid Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-info text-white">
                                            <i class="bx bx-check-double bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Partially Paid Invoices Card -->
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('invoices.index', ['payment_status' => 'partially_paid']) }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-warning">{{ $totalPartiallyPaid }}</h4>
                                        <p class="mb-0 fw-bold">Partially Paid Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-warning text-white">
                                            <i class="bx bx-time bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Unpaid Invoices Card -->
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('invoices.index', ['payment_status' => 'unpaid']) }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-danger">{{ $totalUnpaid }}</h4>
                                        <p class="mb-0 fw-bold">Unpaid Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-danger text-white">
                                            <i class="bx bx-x-circle bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Not Returned Invoices Card -->
                    <div class="col-md-6 col-lg-6">
                        <a href="{{ route('invoices.index', ['status' => 'not_returned']) }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-secondary">{{ $notReturnedCount }}</h4>
                                        <p class="mb-0 fw-bold">Not Returned Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-secondary text-white">
                                            <i class="bx bx-undo bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Returned Invoices Card -->
                    <div class="col-md-6 col-lg-6">
                        <a href="{{ route('invoices.index', ['status' => 'returned']) }}"
                            class="text-decoration-none">
                            <div class="card shadow-sm h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-dark">{{ $returnedCount }}</h4>
                                        <p class="mb-0 fw-bold">Returned Invoices</p>
                                    </div>
                                    <div class="ms-auto avatar">
                                        <span class="avatar-initial rounded-circle bg-dark text-white">
                                            <i class="bx bx-check bx-lg"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
