@extends('layouts.app')

@section('title', 'Products')

@section('content')
<style>.product-thumb{width:46px;height:46px;flex:0 0 46px;display:grid;place-items:center;overflow:hidden;border:1px solid #e2e8f0;border-radius:11px;color:#6574df;background:linear-gradient(145deg,#f1f4ff,#e8edff)}.product-thumb img{width:100%;height:100%;object-fit:cover}.product-thumb i{font-size:1.1rem}.product-name-cell{display:flex;align-items:center;gap:11px}</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Products</h2>
        <p class="text-muted mb-0">Manage all inventory products.</p>
    </div>

    <a href="{{ route('products.create') }}" class="btn btn-primary">
        + Add Product
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.scan') }}" method="GET" class="row g-2 mb-3">
            <div class="col-md-5"><input name="barcode" class="form-control" placeholder="Scan or enter barcode" autocomplete="off" required autofocus></div>
            <div class="col-md-2"><button class="btn btn-success">Find Barcode</button></div>
        </form>
        <form action="{{ route('products.index') }}" method="GET" class="row mb-4">
            <div class="col-md-5">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search product..."
                    value="{{ $search }}"
                >
            </div>

            <div class="col-md-2">
                <button class="btn btn-dark">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th width="200">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->product_code }}</td>

                        <td><div class="product-name-cell">
                            <span class="product-thumb"><img src="{{ $product->image_url }}" alt="{{ $product->product_name }}"></span>
                            <span><strong>{{ $product->product_name }}</strong>@if($product->brand)<br><small class="text-muted">{{ $product->brand }}</small>@endif</span>
                        </div>
                        </td>

                        <td>{{ $product->category_label }}</td>
                        <td>{{ $product->unit }}</td>

                        <td>
                            @if($product->quantity <= 0)
                                <span class="badge bg-danger">
                                    {{ $product->quantity }} Out of Stock
                                </span>
                            @elseif($product->quantity <= $product->minimum_stock)
                                <span class="badge bg-warning text-dark">
                                    {{ $product->quantity }} Low Stock
                                </span>
                            @else
                                <span class="badge bg-success">
                                    {{ $product->quantity }}
                                </span>
                            @endif
                        </td>

                        <td>₱{{ number_format($product->selling_price, 2) }}</td>

                        <td>
                            @if($product->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a
                                href="{{ route('products.show', $product) }}"
                                class="btn btn-sm btn-info"
                            >
                                View
                            </a>

                            <a
                                href="{{ route('products.edit', $product) }}"
                                class="btn btn-sm btn-warning"
                            >
                                Edit
                            </a>

                            @if(auth()->user()->role === 'admin')
                                <form
                                    action="{{ route('products.destroy', $product) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete this product?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No products found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</div>

@endsection
