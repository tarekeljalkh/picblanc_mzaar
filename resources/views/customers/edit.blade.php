@extends('layouts.master')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit: {{ $customer->name }}</li>
        </ol>
    </nav>

    <div class="row g-6">
        <div class="col-md">
            <div class="card">
                <h5 class="card-header">Edit Customer</h5>
                <div class="card-body">
                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>There were some problems with your input:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('customers.update', $customer->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Required for updating data --}}

                        {{-- Name --}}
                        <div class="mb-4 row">
                            <label for="name" class="col-md-2 col-form-label">Name</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="name" name="name"
                                    value="{{ old('name', $customer->name) }}" required minlength="3" />
                            </div>
                        </div>
                        {{-- End Name --}}

                        {{-- Phone --}}
                        <div class="mb-4 row">
                            <label for="phone" class="col-md-2 col-form-label">Phone</label>
                            <div class="col-md-10">
                                <input class="form-control" type="number" id="phone" name="phone"
                                    value="{{ old('phone', $customer->phone) }}" required minlength="10" maxlength="15" />
                            </div>
                        </div>
                        {{-- End Phone --}}

                        {{-- Phone2 --}}
                        <div class="mb-4 row">
                            <label for="phone2" class="col-md-2 col-form-label">Second Phone</label>
                            <div class="col-md-10">
                                <input class="form-control" type="number" id="phone2" name="phone2"
                                    value="{{ old('phone2', $customer->phone2) }}" required minlength="10" maxlength="15" />
                            </div>
                        </div>
                        {{-- End Phone2 --}}


                        {{-- Address --}}
                        <div class="mb-4 row">
                            <label for="address" class="col-md-2 col-form-label">Address</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="address" name="address"
                                    value="{{ old('address', $customer->address) }}" required />
                            </div>
                        </div>
                        {{-- End Address --}}

                        {{-- File Upload (Deposit Card) --}}
                        <div class="mb-4 row">
                            <label for="deposit_card" class="col-md-2 col-form-label">Update Card</label>
                            <div class="col-md-10">
                                <input class="form-control" type="file" id="deposit_card" name="deposit_card"
                                    accept="image/*" />
                                {{-- Show existing image if available, otherwise fallback to placeholder --}}
                                @if ($customer->deposit_card)
                                    <img src="{{ asset($customer->deposit_card) }}" alt="Customer Card" width="100"
                                        class="mt-2">
                                @else
                                    <img src="{{ asset('no_card.jpg') }}" alt="No Card Available" width="100"
                                        class="mt-2">
                                @endif
                            </div>
                        </div>
                        {{-- End File Upload --}}


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
