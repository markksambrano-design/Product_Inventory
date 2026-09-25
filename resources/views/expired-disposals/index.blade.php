@extends('layouts.app')

@section('title', 'Expired Disposals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2>Expired Disposals</h2><p class="text-muted mb-0">History of expired inventory removed from stock.</p></div>
    <a href="{{ route('expired-disposals.create') }}" class="btn btn-danger">+ Dispose Expired Stock</a>
</div>

<div class="card shadow-sm"><div class="card-body"><div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Date</th><th>Product</th><th>Batch</th><th>Quantity</th><th>Disposed By</th><th>Remarks</th></tr></thead>
        <tbody>
        @forelse($disposals as $disposal)
            <tr>
                <td>{{ $disposal->disposal_date?->format('M d, Y') }}</td>
                <td><strong>{{ $disposal->productBatch->product->product_name ?? 'N/A' }}</strong></td>
                <td>{{ $disposal->productBatch->batch_number ?? 'No Batch' }}</td>
                <td><span class="text-danger fw-bold">-{{ $disposal->quantity }}</span> {{ $disposal->productBatch->product->unit ?? '' }}</td>
                <td>{{ $disposal->user->name ?? 'System' }}</td>
                <td>{{ $disposal->remarks ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">No disposal records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>{{ $disposals->links() }}</div></div>
@endsection
