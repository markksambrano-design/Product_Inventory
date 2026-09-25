@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')

<div class="mb-4">
    <h2>Activity Logs</h2>

    <p class="text-muted">
        Monitor user activities in the system.
    </p>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('activity-logs.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Search user or activity..."
                    >
                </div>

                <div class="col-md-3">
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        <option value="Products" {{ $module === 'Products' ? 'selected' : '' }}>
                            Products
                        </option>
                        <option value="Inventory" {{ $module === 'Inventory' ? 'selected' : '' }}>
                            Inventory
                        </option>
                        <option value="Categories" {{ $module === 'Categories' ? 'selected' : '' }}>
                            Categories
                        </option>
                        <option value="Suppliers" {{ $module === 'Suppliers' ? 'selected' : '' }}>
                            Suppliers
                        </option>
                        <option value="Users" {{ $module === 'Users' ? 'selected' : '' }}>
                            Users
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-dark">Filter</button>

                    <a
                        href="{{ route('activity-logs.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            {{ $log->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </td>

                        <td>
                            <strong>{{ $log->user->name ?? 'Unknown User' }}</strong>
                        </td>

                        <td>
                            @if(($log->user->role ?? '') === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @else
                                <span class="badge bg-primary">Staff</span>
                            @endif
                        </td>

                        <td>{{ $log->action }}</td>
                        <td>{{ $log->module }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            No activity logs found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</div>

@endsection
