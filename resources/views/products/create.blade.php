@extends('layouts.app')

@section('title', 'Add Product')

@section('content')

<div class="mb-4">
    <h2>Add Product</h2>

    <p class="text-muted">
        Add a new product to the inventory.
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

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Code</label>

                    <input
                        type="text"
                        name="product_code"
                        class="form-control"
                        value="{{ old('product_code') }}"
                        placeholder="PRD-0001"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        class="form-control"
                        value="{{ old('product_name') }}"
                        list="householdProductCatalog"
                        required
                    >
                    <datalist id="householdProductCatalog">
                        @foreach($productNames as $productName)
                            <option value="{{ $productName }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Category</label>

                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                {{ old('category_id') == $category->id ? 'selected' : '' }}
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
                        value="{{ old('brand') }}"
                        list="householdBrandCatalog"
                    >
                    <datalist id="householdBrandCatalog">
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}">
                        @endforeach
                    </datalist>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Unit</label>

                    <select name="unit" class="form-select" required>
                        <option value="">Select Unit</option>
                        <option value="Piece">Piece</option>
                        <option value="Pack">Pack</option>
                        <option value="Bottle">Bottle</option>
                        <option value="Can">Can</option>
                        <option value="Box">Box</option>
                        <option value="Kilogram">Kilogram</option>
                        <option value="Liter">Liter</option>
                        <option value="Sachet">Sachet</option>
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
                        value="{{ old('cost_price', 0) }}"
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
                        value="{{ old('selling_price', 0) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">Initial Stock</label>

                    <input
                        type="text"
                        class="form-control"
                        value="0"
                        disabled
                    >

                    <small class="text-muted">
                        Add inventory through Stock In after creating the product.
                    </small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Minimum Stock</label>

                    <input
                        type="number"
                        name="minimum_stock"
                        step="0.01"
                        min="0"
                        class="form-control"
                        value="{{ old('minimum_stock', 5) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">Barcode <span class="badge bg-primary-subtle text-primary ms-1">Auto-generated</span></label>

                    <input
                        type="text"
                        name="barcode"
                        class="form-control"
                        value="{{ old('barcode', $generatedBarcode) }}"
                        inputmode="numeric"
                        maxlength="13"
                        readonly
                    >
                    <small class="text-muted">A unique EAN-13 barcode is assigned automatically.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Product Image</label>
                    <input id="productImage" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small id="automaticImageStatus" class="text-muted">Enter the Product Name and Brand to match an image automatically.</small>
                    <img id="imagePreview" class="mt-2 rounded border d-none" style="width:110px;height:110px;object-fit:cover" alt="Product preview">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="col-md-12">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="status"
                            id="status"
                            checked
                        >

                        <label class="form-check-label" for="status">
                            Active Product
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button id="saveProductButton" type="submit" class="btn btn-primary" disabled>
                    Save Product
                </button>

                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const productImage = document.getElementById('productImage');
const imagePreview = document.getElementById('imagePreview');
const imageStatus = document.getElementById('automaticImageStatus');
const saveButton = document.getElementById('saveProductButton');
const productName = document.querySelector('[name="product_name"]');
const productBrand = document.querySelector('[name="brand"]');
let matchTimer;
let matchRequest;
let automaticMatch = false;

productImage.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) {
        scheduleImageMatch();
        return;
    }
    matchRequest?.abort();
    automaticMatch = false;
    imagePreview.src = URL.createObjectURL(file);
    imagePreview.classList.remove('d-none');
    imageStatus.textContent = 'Manual product image selected.';
    imageStatus.className = 'text-success';
    saveButton.disabled = false;
});

async function matchProductImage() {
    const name = productName.value.trim();
    const brand = productBrand.value.trim();
    if (productImage.files.length) return;

    automaticMatch = false;
    saveButton.disabled = true;
    if (name.length < 2 || brand.length < 2) {
        imagePreview.classList.add('d-none');
        imageStatus.textContent = 'Enter the Product Name and Brand to match an image automatically.';
        imageStatus.className = 'text-muted';
        return;
    }

    matchRequest?.abort();
    matchRequest = new AbortController();
    imageStatus.textContent = 'Matching product image...';
    imageStatus.className = 'text-muted';

    try {
        const query = new URLSearchParams({ product_name: name, brand });
        const response = await fetch(`{{ route('products.image-match') }}?${query}`, {
            headers: { 'Accept': 'application/json' },
            signal: matchRequest.signal
        });
        if (!response.ok) throw new Error('Match failed');
        const result = await response.json();
        automaticMatch = result.matched;

        if (!result.matched) {
            imagePreview.classList.add('d-none');
            imageStatus.textContent = 'No matching image found. Upload an image before saving.';
            imageStatus.className = 'text-danger';
            return;
        }

        imagePreview.src = result.image_url;
        imagePreview.classList.remove('d-none');
        imageStatus.textContent = 'Matching image found. Product is ready to save.';
        imageStatus.className = 'text-success';
        saveButton.disabled = false;
    } catch (error) {
        if (error.name !== 'AbortError') {
            imageStatus.textContent = 'Image matching failed. Upload an image before saving.';
            imageStatus.className = 'text-danger';
        }
    }
}

function scheduleImageMatch() {
    if (productImage.files.length) return;
    clearTimeout(matchTimer);
    matchTimer = setTimeout(matchProductImage, 500);
}

productName.addEventListener('input', scheduleImageMatch);
productBrand.addEventListener('input', scheduleImageMatch);
if (productName.value.trim() && productBrand.value.trim()) matchProductImage();
</script>

@endsection
