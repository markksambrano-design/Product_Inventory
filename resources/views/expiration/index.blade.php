@extends('layouts.app')

@section('title', 'Expiration Monitoring')

@section('content')

<div class="mb-4">
    <h2>Expiration Monitoring</h2>

    <p class="text-muted mb-0">
        Monitor expired and soon-to-expire products.
    </p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Expired</p>
                <h3 class="text-danger">{{ $expiredCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Within 7 Days</p>
                <h3 class="text-warning">{{ $sevenDayCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Within 30 Days</p>
                <h3 class="text-primary">{{ $thirtyDayCount }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Safe</p>
                <h3 class="text-success">{{ $safeCount }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('expiration.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Search Product</label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $search }}"
                        placeholder="Product name, code or brand"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Expiration Status</label>

                    <select name="status" class="form-select">
                        <option value="">All Products</option>
                        <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>
                            Expired
                        </option>
                        <option value="7_days" {{ $status === '7_days' ? 'selected' : '' }}>
                            Expiring Within 7 Days
                        </option>
                        <option value="30_days" {{ $status === '30_days' ? 'selected' : '' }}>
                            Expiring Within 30 Days
                        </option>
                        <option value="safe" {{ $status === 'safe' ? 'selected' : '' }}>
                            Safe
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100">
                        Filter
                    </button>
                </div>
            </div>

            @if($search || $status)
                <div class="mt-3">
                    <a
                        href="{{ route('expiration.index') }}"
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
                        <th>Product</th>
                        <th>Category</th>
                        <th>Batch</th>
                        <th>Stock</th>
                        <th>Expiration Date</th>
                        <th>Days Remaining</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($batches as $batch)
                    @php
                        $expiry = $batch->expiration_date;
                        $today = \Carbon\Carbon::today();

                        $daysRemaining = $today->diffInDays(
                            $expiry,
                            false
                        );
                    @endphp

                    <tr>
                        <td>
                            <strong>{{ $batch->product->product_name }}</strong>
                            <br>
                            <small class="text-muted">{{ $batch->product->product_code }}</small>
                        </td>
                        <td>
                            @if($daysRemaining < 0)
                                <a href="{{ route('expired-disposals.create', ['batch' => $batch->id]) }}" class="btn btn-sm btn-danger">Dispose</a>
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $batch->product->category_label }}</td>

                        <td>{{ $batch->batch_number ?? 'No Batch' }}</td>

                        <td>
                            {{ $batch->quantity }}
                            {{ $batch->product->unit }}
                        </td>

                        <td>{{ $expiry->format('M d, Y') }}</td>

                        <td>
                            @if($daysRemaining < 0)
                                <span class="text-danger fw-bold">
                                    {{ abs($daysRemaining) }} days ago
                                </span>
                            @elseif($daysRemaining == 0)
                                <span class="text-danger fw-bold">Today</span>
                            @else
                                {{ $daysRemaining }} days
                            @endif
                        </td>

                        <td>
                            @if($daysRemaining < 0)
                                <span class="badge bg-danger">Expired</span>
                            @elseif($daysRemaining <= 7)
                                <span class="badge bg-warning text-dark">Expiring Soon</span>
                            @elseif($daysRemaining <= 30)
                                <span class="badge bg-primary">Within 30 Days</span>
                            @else
                                <span class="badge bg-success">Safe</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No expiration records found.
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
