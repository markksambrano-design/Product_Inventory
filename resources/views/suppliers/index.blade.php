@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Suppliers</h2>
        <p class="text-muted mb-0">
            Manage product suppliers.
        </p>
    </div>

    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        + Add Supplier
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <strong>{{ $supplier->supplier_name }}</strong>
                        </td>

                        <td>{{ $supplier->contact_person ?? '-' }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>{{ $supplier->email ?? '-' }}</td>

                        <td>
                            @if($supplier->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('suppliers.edit', $supplier) }}"
                                class="btn btn-sm btn-warning"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('suppliers.destroy', $supplier) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Delete this supplier?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No suppliers found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $suppliers->links() }}
    </div>
</div>

@endsection
