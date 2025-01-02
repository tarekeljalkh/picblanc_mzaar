@extends('layouts.master')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit: {{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-6">
        <div class="col-md">
            <div class="card">
                <h5 class="card-header">Edit Product</h5>
                <div class="card-body">
                    <form action="{{ route('products.update', $product->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Laravel method directive to spoof PUT request --}}

                        {{-- Name --}}
                        <div class="mb-4 row">
                            <label for="name" class="col-md-2 col-form-label">Name</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="name" name="name"
                                    value="{{ old('name', $product->name) }}" />
                            </div>
                        </div>
                        {{-- End Name --}}

                        {{-- Description --}}
                        <div class="mb-4 row">
                            <label for="description" class="col-md-2 col-form-label">Description</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="description" name="description"
                                    value="{{ old('description', $product->description) }}" />
                            </div>
                        </div>
                        {{-- End Description --}}

                        {{-- Price --}}
                        <div class="mb-4 row">
                            <label for="price" class="col-md-2 col-form-label">Price</label>
                            <div class="col-md-10">
                                <input class="form-control" type="number" step="0.01" id="price" name="price"
                                    value="{{ old('price', $product->price) }}" />
                            </div>
                        </div>
                        {{-- End Price --}}

                        {{-- Update Button --}}
                        <div class="mt-4 row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                        {{-- End Update Button --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
