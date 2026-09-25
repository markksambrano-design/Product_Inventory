@extends('layouts.app')

@section('title', 'Batch Inventory')
@section('page_description', 'Monitor product batches and available quantities.')

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Available Batches</p>
                <h3>{{ $totalBatches }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Expiring Within 30 Days</p>
                <h3 class="text-warning">{{ $expiringBatches }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2">Expired Batches</p>
                <h3 class="text-danger">{{ $expiredBatches }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-2">No Expiration</p>
                <h3 class="text-secondary">{{ $noExpirationBatches }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('batch-inventory.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Product, code or batch number"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Batches</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>
                            Expired
                        </option>
                        <option value="expiring" {{ $status === 'expiring' ? 'selected' : '' }}>
                            Expiring Within 30 Days
                        </option>
                        <option value="safe" {{ $status === 'safe' ? 'selected' : '' }}>
                            Safe
                        </option>
                        <option
                            value="no_expiration"
                            {{ $status === 'no_expiration' ? 'selected' : '' }}
                        >
                            No Expiration
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark me-2">Filter</button>
                    <a
                        href="{{ route('batch-inventory.index') }}"
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
                        <th>Product</th>
                        <th>Category</th>
                        <th>Batch</th>
                        <th>Received</th>
                        <th>Available Qty</th>
                        <th>Expiration</th>
                        <th>Days Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($batches as $batch)
                    @php
                        $daysRemaining = null;

                        if ($batch->expiration_date) {
                            $daysRemaining = now()
                                ->startOfDay()
                                ->diffInDays(
                                    $batch->expiration_date,
                                    false
                                );
                        }
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $batch->product->product_name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $batch->product->product_code ?? '' }}
                            </small>
                        </td>

                        <td>{{ $batch->product->category_label }}</td>

                        <td>
                            <strong>{{ $batch->batch_number ?? 'No Batch' }}</strong>
                        </td>

                        <td>{{ $batch->received_date?->format('M d, Y') ?? '-' }}</td>

                        <td>
                            {{ $batch->quantity }}
                            {{ $batch->product->unit ?? '' }}
                        </td>

                        <td>
                            {{ $batch->expiration_date?->format('M d, Y') ?? 'No Expiration' }}
                        </td>

                        <td>
                            @if($daysRemaining === null)
                                -
                            @elseif($daysRemaining < 0)
                                <span class="text-danger">
                                    {{ abs($daysRemaining) }} days ago
                                </span>
                            @elseif($daysRemaining == 0)
                                <span class="text-danger fw-bold">Today</span>
                            @else
                                {{ $daysRemaining }} days
                            @endif
                        </td>

                        <td>
                            @if($daysRemaining === null)
                                <span class="badge bg-secondary">No Expiration</span>
                            @elseif($daysRemaining < 0)
                                <span class="badge bg-danger">Expired</span>
                            @elseif($daysRemaining <= 7)
                                <span class="badge bg-warning text-dark">Critical</span>
                            @elseif($daysRemaining <= 30)
                                <span class="badge bg-primary">Expiring Soon</span>
                            @else
                                <span class="badge bg-success">Safe</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No available batches found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $batches->links() }}
        </div>
    </div>
</div>

@endsection
