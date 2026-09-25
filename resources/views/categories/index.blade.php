@extends('layouts.app')

@section('title', 'Categories')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Categories</h2>
        <p class="text-muted mb-0">
            Manage product categories.
        </p>
    </div>

    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        + Add Category
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form
            action="{{ route('categories.index') }}"
            method="GET"
            class="row mb-4"
        >
            <div class="col-md-5">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search category..."
                    value="{{ $search }}"
                >
            </div>

            <div class="col-md-2">
                <button class="btn btn-dark">Search</button>
            </div>

            @if($search)
                <div class="col-md-2">
                    <a
                        href="{{ route('categories.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Clear
                    </a>
                </div>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <strong>{{ $category->name }}</strong>
                        </td>

                        <td>{{ $category->description ?? '-' }}</td>

                        <td>
                            <span class="badge bg-dark">
                                {{ $category->products_count }}
                            </span>
                        </td>

                        <td>
                            @if($category->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('categories.edit', $category) }}"
                                class="btn btn-sm btn-warning"
                            >
                                Edit
                            </a>

                            <form
                                action="{{ route('categories.destroy', $category) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Delete this category?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No categories found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $categories->links() }}
    </div>
</div>

@endsection
