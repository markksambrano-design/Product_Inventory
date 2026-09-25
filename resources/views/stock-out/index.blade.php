@extends('layouts.app')

@section('title', 'Stock Out History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Stock Out History</h2>

        <p class="text-muted mb-0">
            View all outgoing stock transactions.
        </p>
    </div>

    <a href="{{ route('stock-out.create') }}" class="btn btn-danger">
        + Stock Out
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
                        <th>Quantity</th>
                        <th>Batch Used</th>
                        <th>Reason</th>
                        <th>Reference</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($stockOuts as $stockOut)
                    <tr>
                        <td>{{ $stockOut->stock_out_date?->format('M d, Y') }}</td>

                        <td>
                            <strong>{{ $stockOut->product->product_name ?? 'N/A' }}</strong>
                        </td>

                        <td>
                            <span class="badge bg-danger">
                                -{{ $stockOut->quantity }}
                            </span>
                        </td>

                        <td>
                            @forelse($stockOut->batches as $allocation)
                                <div class="mb-1">
                                    <strong>
                                        {{ $allocation->productBatch->batch_number ?? 'No Batch' }}
                                    </strong>

                                    <span class="text-danger">
                                        -{{ $allocation->quantity }}
                                    </span>

                                    @if($allocation->productBatch->expiration_date)
                                        <small class="text-muted">
                                            Exp:
                                            {{ $allocation->productBatch->expiration_date->format('M d, Y') }}
                                        </small>
                                    @endif
                                </div>
                            @empty
                                -
                            @endforelse
                        </td>

                        <td>{{ $stockOut->reason ?? '-' }}</td>
                        <td>{{ $stockOut->reference_number ?? '-' }}</td>
                        <td>{{ $stockOut->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No stock out records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $stockOuts->links() }}
    </div>
</div>

@endsection
