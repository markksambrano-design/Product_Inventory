@extends('layouts.app')

@section('title', 'Add Category')

@section('content')

<div class="mb-4">
    <h2>Add Category</h2>

    <p class="text-muted">
        Create a new product category.
    </p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('categories.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Category Name</label>

                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    placeholder="Example: Grocery"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="4"
                >{{ old('description') }}</textarea>
            </div>

            <div class="form-check mb-4">
                <input
                    type="checkbox"
                    name="status"
                    id="status"
                    class="form-check-input"
                    checked
                >

                <label class="form-check-label" for="status">
                    Active Category
                </label>
            </div>

            <button class="btn btn-primary">Save Category</button>

            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </form>
    </div>
</div>

@endsection
