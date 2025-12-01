@extends('layouts.master')

@section('title', 'Profile Settings')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </nav>


            <!-- Profile Information Section -->
            <div class="card mb-6">
                <h5 class="card-header">Profile Information</h5>
                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}" class="pt-3">
                        @csrf
                        @method('patch')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required />
                                @error('name')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required />
                                @error('email')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="card mb-6">
                <h5 class="card-header">Update Password</h5>
                <div class="card-body">
                    <form method="post" action="{{ route('password.update') }}" class="pt-3">
                        @csrf
                        @method('put')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required />
                                @error('current_password')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" required />
                                @error('password')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required />
                                @error('password_confirmation')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Create New Year Database -->
<div class="card mb-6 mt-5">
    <h5 class="card-header">Create New Year Database</h5>
    <div class="card-body">
        <form method="POST" action="{{ route('year.create') }}" class="pt-3">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="year" class="form-label">New Year</label>
                    <input
                        type="number"
                        id="year"
                        name="year"
                        min="{{ date('Y') }}"
                        class="form-control @error('year') is-invalid @enderror"
                        placeholder="{{ date('Y') + 1 }}"
                        required
                    />
                    @error('year')
                        <div class="text-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-duplicate"></i> Create New Year
                    </button>
                </div>
            </div>

            <small class="text-muted d-block mt-3">
                This will duplicate the current database, keep customers & products, and
                reset invoices & related tables for the new year.
            </small>
        </form>
    </div>
</div>


            <!-- Delete Account Section -->
            {{-- <div class="card mb-6">
                <h5 class="card-header">Delete Account</h5>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading">Are you sure you want to delete your account?</h6>
                        <p>Once your account is deleted, all of its resources and data will be permanently removed. Before proceeding, download any data you wish to retain.</p>
                    </div>

                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="mt-3">
                            <label for="password" class="form-label">Confirm your password to continue:</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required />
                            @error('password')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="submit" class="btn btn-danger">Delete Account</button>
                            <a href="#" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div> --}}
        </div>
    </div>
@endsection
