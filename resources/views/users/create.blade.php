@extends('layouts.master')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
</nav>

<div class="row g-6">
    <div class="col-md">
        <div class="card">
            <h5 class="card-header">Add New User</h5>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-4 row">
                        <label for="name" class="col-md-2 col-form-label">Name</label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="name" name="name" required />
                            @error('name')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Name --}}

                    {{-- Email --}}
                    <div class="mb-4 row">
                        <label for="email" class="col-md-2 col-form-label">Email</label>
                        <div class="col-md-10">
                            <input class="form-control" type="email" id="email" name="email" required />
                            @error('email')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Email --}}

                    {{-- Role --}}
                    <div class="mb-4 row">
                        <label for="role" class="col-md-2 col-form-label">Role</label>
                        <div class="col-md-10">
                            <select class="form-select" id="role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Role --}}

                    {{-- Password --}}
                    <div class="mb-4 row">
                        <label for="password" class="col-md-2 col-form-label">Password</label>
                        <div class="col-md-10">
                            <input class="form-control" type="password" id="password" name="password" required />
                            @error('password')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Password --}}

                    {{-- Password Confirmation --}}
                    <div class="mb-4 row">
                        <label for="password_confirmation" class="col-md-2 col-form-label">Confirm Password</label>
                        <div class="col-md-10">
                            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required />
                            @error('password_confirmation')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Password Confirmation --}}

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
