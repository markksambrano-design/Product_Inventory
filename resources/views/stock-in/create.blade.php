@extends('layouts.app')

@section('title', 'Stock In')

@section('content')

<div class="mb-4">
    <h2>Stock In</h2>

    <p class="text-muted">
        Add new stock to an existing product.
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

        <form action="{{ route('stock-in.store') }}" method="POST">
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
                                -
                                Stock: {{ $product->quantity }}
                                {{ $product->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Supplier</label>

                    <select name="supplier_id" class="form-select">
                        <option value="">Select Supplier</option>

                        @foreach($suppliers as $supplier)
                            <option
                                value="{{ $supplier->id }}"
                                {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
                            >
                                {{ $supplier->supplier_name }}
                            </option>
                        @endforeach
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
                    <label class="form-label">Cost Price</label>

                    <input
                        type="number"
                        name="cost_price"
                        step="0.01"
                        min="0"
                        class="form-control"
                        value="{{ old('cost_price') }}"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Batch Number</label>

                    <input
                        type="text"
                        name="batch_number"
                        class="form-control"
                        value="{{ old('batch_number') }}"
                        placeholder="Example: BATCH-001"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Expiration Date</label>

                    <input
                        type="date"
                        name="expiration_date"
                        class="form-control"
                        value="{{ old('expiration_date') }}"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Stock In Date</label>

                    <input
                        type="date"
                        name="stock_in_date"
                        class="form-control"
                        value="{{ old('stock_in_date', date('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Reference Number</label>

                    <input
                        type="text"
                        name="reference_number"
                        class="form-control"
                        placeholder="INV-00001"
                        value="{{ old('reference_number') }}"
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
                <button class="btn btn-primary">Add Stock</button>

                <a href="{{ route('stock-in.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
