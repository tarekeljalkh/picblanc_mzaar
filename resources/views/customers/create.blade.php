@extends('layouts.master')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
        </ol>
    </nav>

    <div class="row g-6">
        <div class="col-md">
            <div class="card">
                <h5 class="card-header">Add New Customer</h5>
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

                    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- Name --}}
                        <div class="mb-4 row">
                            <label for="name" class="col-md-2 col-form-label">Name</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="name" name="name" />
                            </div>
                        </div>
                        {{-- End Name --}}


                        {{-- Phone --}}
                        <div class="mb-4 row">
                            <label for="phone" class="col-md-2 col-form-label">Phone</label>
                            <div class="col-md-10">
                                <input class="form-control" type="number" id="phone" name="phone" />
                            </div>
                        </div>
                        {{-- End Phone --}}

                        {{-- Phone2 --}}
                        <div class="mb-4 row">
                            <label for="phone2" class="col-md-2 col-form-label">Second Phone</label>
                            <div class="col-md-10">
                                <input class="form-control" type="number" id="phone2" name="phone2" />
                            </div>
                        </div>
                        {{-- End Phone --}}


                        {{-- Address --}}
                        <div class="mb-4 row">
                            <label for="address" class="col-md-2 col-form-label">Address</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" id="address" name="address" />
                            </div>
                        </div>
                        {{-- End Address --}}

                        {{-- Card Upload --}}
                        <!-- Mobile Camera Access or File Upload -->
                        <div class="mb-4 row">
                            <label for="deposit_card" class="col-md-2 col-form-label">Upload ID Card</label>
                            <div class="col-md-10">
                                <!-- 'accept="image/*"' allows only image files, 'capture="camera"' opens the mobile camera by default -->
                                <input class="form-control" type="file" id="deposit_card" name="deposit_card"
                                    accept="image/*" capture="camera" required />
                            </div>
                        </div>
                        {{-- End Card Upload --}}

                        {{-- Create Button --}}
                        <div class="mt-4 row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </div>
                        {{-- End Create Button --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
