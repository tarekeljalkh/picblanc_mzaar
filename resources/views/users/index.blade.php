@extends('layouts.master')

@section('title', 'Users')

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </nav>

    <div class="col-md">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Users ({{ $users->count() }})</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary">Add New User</a>
            </div>
            <div class="card-body">
                <table id="usersTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                    @elseif ($user->role === 'user')
                                        <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d-m-Y') }}</td>
                                <td>
                                    @if (auth()->user()->role === 'admin')
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
                                        <a href="{{ route('users.destroy', $user->id) }}"
                                            class="btn btn-danger delete-item">Delete</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Include the DataTable scripts -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables with export buttons and responsive support
            $('#usersTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true
            });
        });
    </script>
@endpush
