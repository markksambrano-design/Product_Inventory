@extends('layouts.app')

@section('title', 'Stock Adjustment')

@section('content')

<div class="mb-4">
    <h2>Stock Adjustment</h2>

    <p class="text-muted">
        Correct inventory discrepancies without manually editing product stock.
    </p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stock-adjustments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Batch</label>

                    <select name="product_batch_id" class="form-select" required>
                        <option value="">Select Product Batch</option>

                        @foreach($batches as $batch)
                            <option
                                value="{{ $batch->id }}"
                                {{ old('product_batch_id') == $batch->id ? 'selected' : '' }}
                            >
                                {{ $batch->product->product_name }} — {{ $batch->batch_number ?? 'No Batch' }} — Stock: {{ $batch->quantity }} {{ $batch->product->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Adjustment Type</label>

                    <select name="type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option
                            value="increase"
                            {{ old('type') === 'increase' ? 'selected' : '' }}
                        >
                            Increase Stock (+)
                        </option>
                        <option
                            value="decrease"
                            {{ old('type') === 'decrease' ? 'selected' : '' }}
                        >
                            Decrease Stock (-)
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity</label>

                    <input
                        type="number"
                        name="quantity"
                        step="0.01"
                        min="0.01"
                        class="form-control"
                        value="{{ old('quantity') }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Adjustment Date</label>

                    <input
                        type="date"
                        name="adjustment_date"
                        class="form-control"
                        value="{{ old('adjustment_date', date('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Reason</label>

                    <select name="reason" class="form-select" required>
                        <option value="">Select Reason</option>
                        <option value="Physical Count Correction">
                            Physical Count Correction
                        </option>
                        <option value="Damaged Item">Damaged Item</option>
                        <option value="Missing Item">Missing Item</option>
                        <option value="Data Entry Error">Data Entry Error</option>
                        <option value="Returned Item">Returned Item</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>

                    <textarea
                        name="remarks"
                        class="form-control"
                        rows="3"
                    >{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    Save Adjustment
                </button>

                <a
                    href="{{ route('stock-adjustments.index') }}"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
