@extends('layouts.app')

@section('title', 'Users')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>User Management</h2>

        <p class="text-muted mb-0">
            Manage system administrators and staff.
        </p>
    </div>

    <a href="{{ route('users.create') }}" class="btn btn-primary">
        + Add User
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                        </td>

                        <td>{{ $user->email }}</td>

                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @else
                                <span class="badge bg-primary">Staff</span>
                            @endif
                        </td>

                        <td>{{ $user->created_at->format('M d, Y') }}</td>

                        <td>
                            <a
                                href="{{ route('users.edit', $user) }}"
                                class="btn btn-sm btn-warning"
                            >
                                Edit
                            </a>

                            @if($user->id !== auth()->id())
                                <form
                                    action="{{ route('users.destroy', $user) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this user?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No users found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</div>

@endsection
