@extends('layouts.app')
@section('title', 'Physical Count')
@section('content')
<div class="mb-4"><h2>Physical Inventory Count</h2><p class="text-muted">Enter the actual batch quantity; variance is recorded automatically.</p></div>
<div class="card shadow-sm"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('inventory-counts.store') }}">@csrf<input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
<div class="row g-3">
<div class="col-md-8"><label class="form-label">Product Batch</label><select name="product_batch_id" class="form-select" required><option value="">Select batch</option>@foreach($batches as $batch)<option value="{{ $batch->id }}" {{ old('product_batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->product->product_name }} — {{ $batch->batch_number ?? 'No Batch' }} — System: {{ $batch->quantity }} {{ $batch->product->unit }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Actual Quantity</label><input type="number" step="0.01" min="0" name="actual_quantity" value="{{ old('actual_quantity') }}" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Count Date</label><input type="date" name="count_date" value="{{ old('count_date', today()->toDateString()) }}" class="form-control" required></div>
<div class="col-md-8"><label class="form-label">Remarks</label><input name="remarks" value="{{ old('remarks') }}" class="form-control" placeholder="Reason or count notes"></div>
</div><div class="mt-4"><button class="btn btn-primary">Save & Reconcile</button> <a href="{{ route('inventory-counts.index') }}" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
@endsection
