@extends('layouts.master')

@section('title', 'Products')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Products</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Products ({{ $productCount }})</h5>
                <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
            </div>
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table table-striped table-bordered dt-responsive nowrap', 'style' => 'width:100%']) }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
