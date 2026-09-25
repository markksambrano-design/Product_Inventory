@extends('layouts.app')

@section('title', 'Dispose Expired Stock')

@section('content')
<div class="mb-4">
    <h2>Dispose Expired Stock</h2>
    <p class="text-muted">Remove expired batch stock with a traceable record.</p>
</div>

<div class="card shadow-sm"><div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif

    <form action="{{ route('expired-disposals.store') }}" method="POST">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Expired Batch</label>
                <select name="product_batch_id" class="form-select" required>
                    <option value="">Select expired batch</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ old('product_batch_id', request('batch')) == $batch->id ? 'selected' : '' }}>
                            {{ $batch->product->product_name }} — {{ $batch->batch_number ?? 'No Batch' }} — {{ $batch->quantity }} {{ $batch->product->unit }} — Expired {{ $batch->expiration_date->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quantity to Dispose</label>
                <input type="number" name="quantity" step="0.01" min="0.01" class="form-control" value="{{ old('quantity') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Disposal Date</label>
                <input type="date" name="disposal_date" class="form-control" value="{{ old('disposal_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Optional disposal notes">
            </div>
        </div>
        <div class="mt-4">
            <button class="btn btn-danger">Dispose Stock</button>
            <a href="{{ route('expired-disposals.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
