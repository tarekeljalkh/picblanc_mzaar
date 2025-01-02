@extends('layouts.master')

@section('title', 'Edit User')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit User</li>
    </ol>
</nav>

<div class="row g-6">
    <div class="col-md">
        <div class="card">
            <h5 class="card-header">Edit User</h5>
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-4 row">
                        <label for="name" class="col-md-2 col-form-label">Name</label>
                        <div class="col-md-10">
                            <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required />
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
                            <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required />
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
                            <select id="role" name="role" class="form-select" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Role --}}

                    {{-- Password (optional) --}}
                    <div class="mb-4 row">
                        <label for="password" class="col-md-2 col-form-label">Password</label>
                        <div class="col-md-10">
                            <input class="form-control" type="password" id="password" name="password" placeholder="Leave blank to keep current password" />
                            @error('password')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Password --}}

                    {{-- Confirm Password (optional) --}}
                    <div class="mb-4 row">
                        <label for="password_confirmation" class="col-md-2 col-form-label">Confirm Password</label>
                        <div class="col-md-10">
                            <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Leave blank to keep current password" />
                            @error('password_confirmation')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- End Confirm Password --}}

                    {{-- Update Button --}}
                    <div class="mt-4 row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </div>
                    {{-- End Update Button --}}
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
