@extends('layouts.app')

@section('title', 'Stock In History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Stock In History</h2>
        <p class="text-muted mb-0">
            View all incoming stock transactions.
        </p>
    </div>

    <a href="{{ route('stock-in.create') }}" class="btn btn-primary">
        + Add Stock
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Quantity</th>
                        <th>Cost Price</th>
                        <th>Expiration</th>
                        <th>Reference</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($stockIns as $stockIn)
                    <tr>
                        <td>{{ $stockIn->stock_in_date?->format('M d, Y') }}</td>

                        <td>
                            <strong>{{ $stockIn->product->product_name ?? 'N/A' }}</strong>
                        </td>

                        <td>{{ $stockIn->supplier->supplier_name ?? 'No Supplier' }}</td>

                        <td>
                            <span class="badge bg-success">
                                +{{ $stockIn->quantity }}
                            </span>
                        </td>

                        <td>
                            @if($stockIn->cost_price !== null)
                                ₱{{ number_format($stockIn->cost_price, 2) }}
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $stockIn->expiration_date?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $stockIn->reference_number ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No stock in records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $stockIns->links() }}
    </div>
</div>

@endsection
