@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Inventory</h2>
        <p class="text-muted mb-0">Track all stock movements.</p>
    </div>

    <div>
        <a href="{{ route('stock-in.create') }}" class="btn btn-success">
            + Stock In
        </a>

        <a href="{{ route('stock-out.create') }}" class="btn btn-danger">
            - Stock Out
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('inventory.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search Product</label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Product name or code"
                        value="{{ $search }}"
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label">Transaction</label>

                    <select name="type" class="form-select">
                        <option value="">All</option>
                        <option value="stock_in" {{ $type === 'stock_in' ? 'selected' : '' }}>
                            Stock In
                        </option>
                        <option value="stock_out" {{ $type === 'stock_out' ? 'selected' : '' }}>
                            Stock Out
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date From</label>

                    <input
                        type="date"
                        name="date_from"
                        class="form-control"
                        value="{{ $dateFrom }}"
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date To</label>

                    <input
                        type="date"
                        name="date_to"
                        class="form-control"
                        value="{{ $dateTo }}"
                    >
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100">
                        Filter
                    </button>
                </div>
            </div>

            @if($search || $type || $dateFrom || $dateTo)
                <div class="mt-3">
                    <a
                        href="{{ route('inventory.index') }}"
                        class="btn btn-sm btn-outline-secondary"
                    >
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Details</th>
                        <th>Reference</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($movement['date'])->format('M d, Y') }}
                        </td>

                        <td>
                            @if($movement['type_key'] === 'stock_in')
                                <span class="badge bg-success">Stock In</span>
                            @else
                                <span class="badge bg-danger">Stock Out</span>
                            @endif
                        </td>

                        <td>
                            <strong>{{ $movement['product']->product_name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $movement['product']->product_code ?? '' }}
                            </small>
                        </td>

                        <td>{{ $movement['product']->category_label }}</td>

                        <td>
                            @if($movement['type_key'] === 'stock_in')
                                <span class="text-success fw-bold">
                                    +{{ $movement['quantity'] }}
                                </span>
                            @else
                                <span class="text-danger fw-bold">
                                    -{{ $movement['quantity'] }}
                                </span>
                            @endif

                            {{ $movement['product']->unit ?? '' }}
                        </td>

                        <td>
                            @if($movement['type_key'] === 'stock_in')
                                Supplier: {{ $movement['supplier'] ?? 'N/A' }}
                            @else
                                Reason: {{ $movement['reason'] ?? 'N/A' }}
                            @endif

                            @if($movement['remarks'])
                                <br>
                                <small class="text-muted">
                                    {{ $movement['remarks'] }}
                                </small>
                            @endif
                        </td>

                        <td>{{ $movement['reference_number'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            No inventory movements found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $movements->links() }}
        </div>
    </div>
</div>

@endsection
