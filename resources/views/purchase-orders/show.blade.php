@extends('layouts.app')
@section('title', $purchaseOrder->po_number)
@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2>{{ $purchaseOrder->po_number }}</h2>
        <p class="text-muted">{{ $purchaseOrder->supplier->supplier_name }} · {{ ucfirst($purchaseOrder->status) }} · Approval: {{ ucfirst($purchaseOrder->approval_status ?? 'legacy') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if(($purchaseOrder->approval_status ?? null) === 'pending')
            <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">@csrf<button class="btn btn-primary">Approve</button></form>
        @endif
        @if(!in_array($purchaseOrder->status, ['received', 'cancelled']) && ($purchaseOrder->approval_status ?? null) === 'approved')
            <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">@csrf<button class="btn btn-success" onclick="return confirm('Receive remaining items and add them to stock?')">Receive Remaining</button></form>
        @endif
        @if(!in_array($purchaseOrder->status, ['received', 'cancelled']))
            <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}">
                @csrf
                <input type="hidden" name="cancellation_reason" id="cancellation_reason">
                <button class="btn btn-outline-danger" onclick="const reason=prompt('Cancellation reason:'); if(!reason || reason.length < 5) return false; document.getElementById('cancellation_reason').value=reason; return true;">Cancel</button>
            </form>
        @endif
    </div>
</div>
<div class="card shadow-sm"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Product</th><th>Ordered</th><th>Received</th><th>Unit Cost</th><th>Expiration</th><th>Subtotal</th></tr></thead><tbody>
@foreach($purchaseOrder->items as $item)<tr><td>{{ $item->product->product_name }}</td><td>{{ $item->quantity }}</td><td>{{ $item->received_quantity }}</td><td>₱{{ number_format((float) $item->unit_cost, 2) }}</td><td>{{ $item->expiration_date?->format('M d, Y') ?? '-' }}</td><td>₱{{ number_format((float) $item->quantity * (float) $item->unit_cost, 2) }}</td></tr>@endforeach
</tbody><tfoot><tr><th colspan="5" class="text-end">Total</th><th>₱{{ number_format((float) $purchaseOrder->total_amount, 2) }}</th></tr></tfoot></table></div></div>
<div class="card shadow-sm mt-4"><div class="card-body"><h5>Supplier Delivery History</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Receipt</th><th>Date</th><th>Received By</th><th>Remarks</th></tr></thead><tbody>
@forelse($purchaseOrder->receipts as $receipt)<tr><td>{{ $receipt->reference_number }}</td><td>{{ $receipt->received_date?->format('M d, Y') }}</td><td>{{ $receipt->receiver?->name ?? 'System' }}</td><td>{{ $receipt->remarks ?? '-' }}</td></tr>@empty<tr><td colspan="4">No deliveries recorded yet.</td></tr>@endforelse
</tbody></table></div>@if($purchaseOrder->cancellation_reason)<p class="text-danger mb-0"><strong>Cancellation reason:</strong> {{ $purchaseOrder->cancellation_reason }}</p>@endif</div></div>
@endsection
