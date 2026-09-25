@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="mb-4">
    <h2>Edit Product</h2>
    <p class="text-muted">Update product information.</p>
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

        <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Code</label>
                    <input
                        type="text"
                        name="product_code"
                        class="form-control"
                        value="{{ old('product_code', $product->product_code) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Name</label>
                    <input
                        type="text"
                        name="product_name"
                        class="form-control"
                        value="{{ old('product_name', $product->product_name) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <input
                        type="text"
                        name="brand"
                        class="form-control"
                        value="{{ old('brand', $product->brand) }}"
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-select" required>
                        <option value="">Select Unit</option>
                        @foreach(['Piece', 'Pack', 'Bottle', 'Can', 'Box', 'Kilogram', 'Liter', 'Sachet'] as $unit)
                            <option
                                value="{{ $unit }}"
                                {{ old('unit', $product->unit) === $unit ? 'selected' : '' }}
                            >
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cost Price</label>
                    <input
                        type="number"
                        name="cost_price"
                        step="0.01"
                        min="0"
                        class="form-control"
                        value="{{ old('cost_price', $product->cost_price) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Selling Price</label>
                    <input
                        type="number"
                        name="selling_price"
                        step="0.01"
                        min="0"
                        class="form-control"
                        value="{{ old('selling_price', $product->selling_price) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Current Stock</label>
                    <input
                        type="text"
                        class="form-control"
                        value="{{ $product->quantity }} {{ $product->unit }}"
                        disabled
                    >

                    <small class="text-muted">
                        Stock can only be changed through Stock In or Stock Out.
                    </small>

                    <div class="mt-2">
                        <a
                            href="{{ route('stock-in.create', ['product' => $product->id]) }}"
                            class="btn btn-sm btn-success"
                        >
                            + Stock In
                        </a>

                        <a
                            href="{{ route('stock-out.create', ['product' => $product->id]) }}"
                            class="btn btn-sm btn-danger"
                        >
                            - Stock Out
                        </a>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Minimum Stock</label>
                    <input
                        type="number"
                        name="minimum_stock"
                        step="0.01"
                        min="0"
                        class="form-control"
                        value="{{ old('minimum_stock', $product->minimum_stock) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Barcode</label>
                    <input
                        type="text"
                        name="barcode"
                        class="form-control"
                        value="{{ old('barcode', $product->barcode) }}"
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Image</label>
                    <input id="productImage" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Leave empty to keep the current image.</small>
                    @if($product->image)<img id="imagePreview" src="{{ $product->image_url }}" class="mt-2 rounded border" style="width:110px;height:110px;object-fit:cover" alt="{{ $product->product_name }}">@else<img id="imagePreview" class="mt-2 rounded border d-none" style="width:110px;height:110px;object-fit:cover" alt="Product preview">@endif
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                    >{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="status"
                            id="status"
                            {{ old('status', $product->status) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="status">
                            Active Product
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Product</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('productImage').addEventListener('change', function () {
    const preview = document.getElementById('imagePreview');
    const file = this.files[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
});
</script>

@endsection
