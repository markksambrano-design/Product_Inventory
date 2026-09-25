@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Stock Adjustments</h2>

        <p class="text-muted mb-0">
            View inventory correction history.
        </p>
    </div>

    <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary">
        + New Adjustment
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
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($adjustments as $adjustment)
                    <tr>
                        <td>{{ $adjustment->adjustment_date?->format('M d, Y') }}</td>

                        <td>
                            <strong>
                                {{ $adjustment->product->product_name ?? 'N/A' }}
                            </strong>
                        </td>

                        <td>
                            @if($adjustment->type === 'increase')
                                <span class="badge bg-success">Increase</span>
                            @else
                                <span class="badge bg-danger">Decrease</span>
                            @endif
                        </td>

                        <td>
                            @if($adjustment->type === 'increase')
                                <span class="text-success fw-bold">
                                    +{{ $adjustment->quantity }}
                                </span>
                            @else
                                <span class="text-danger fw-bold">
                                    -{{ $adjustment->quantity }}
                                </span>
                            @endif

                            {{ $adjustment->product->unit ?? '' }}
                        </td>

                        <td>{{ $adjustment->reason }}</td>
                        <td>{{ $adjustment->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No stock adjustments found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $adjustments->links() }}
    </div>
</div>

@endsection
