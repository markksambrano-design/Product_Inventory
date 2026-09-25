@extends('layouts.app')
@section('title', 'Physical Counts')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><div><h2>Physical Counts</h2><p class="text-muted mb-0">System-versus-actual stock reconciliation history.</p></div><a href="{{ route('inventory-counts.create') }}" class="btn btn-primary">+ New Count</a></div>
<div class="card shadow-sm"><div class="card-body"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Product / Batch</th><th>System</th><th>Actual</th><th>Variance</th><th>Counted By</th><th>Remarks</th></tr></thead><tbody>
@forelse($counts as $count)<tr><td>{{ $count->count_date?->format('M d, Y') }}</td><td><strong>{{ $count->productBatch->product->product_name ?? 'N/A' }}</strong><br><small>{{ $count->productBatch->batch_number ?? 'No Batch' }}</small></td><td>{{ $count->system_quantity }}</td><td>{{ $count->actual_quantity }}</td><td><span class="fw-bold {{ $count->variance < 0 ? 'text-danger' : ($count->variance > 0 ? 'text-success' : '') }}">{{ $count->variance > 0 ? '+' : '' }}{{ $count->variance }}</span></td><td>{{ $count->user->name ?? 'System' }}</td><td>{{ $count->remarks ?? '-' }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-5">No physical counts found.</td></tr>@endforelse
</tbody></table></div>{{ $counts->links() }}</div></div>
@endsection
