@extends('layouts.app')

@section('title', 'Stock Out')

@section('content')

<div class="mb-4">
    <h2>Stock Out</h2>

    <p class="text-muted">
        Record products leaving the inventory.
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

        <form action="{{ route('stock-out.store') }}" method="POST">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product</label>

                    <select name="product_id" class="form-select" required>
                        <option value="">Select Product</option>

                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                {{
                                    old('product_id', request('product')) == $product->id
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                {{ $product->product_name }}
                                - Stock:
                                {{ $product->quantity }}
                                {{ $product->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
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

                <div class="col-md-6">
                    <label class="form-label">Reason</label>

                    <select name="reason" class="form-select">
                        <option value="">Select Reason</option>
                        <option value="Sold">Sold</option>
                        <option value="Damaged">Damaged</option>
                        <option value="Expired">Expired</option>
                        <option value="Returned">Returned</option>
                        <option value="Internal Use">Internal Use</option>
                        <option value="Others">Others</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock Out Date</label>

                    <input
                        type="date"
                        name="stock_out_date"
                        class="form-control"
                        value="{{ old('stock_out_date', date('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reference Number</label>

                    <input
                        type="text"
                        name="reference_number"
                        class="form-control"
                        value="{{ old('reference_number') }}"
                        placeholder="OUT-00001"
                    >
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
                <button class="btn btn-danger">Remove Stock</button>

                <a href="{{ route('stock-out.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
