@extends('layouts.app')
@section('title', $product->product_name)
@section('content')
<style>.product-photo{width:100%;max-width:310px;aspect-ratio:1;display:grid;place-items:center;overflow:hidden;border:1px solid #e2e8f0;border-radius:18px;color:#6877df;background:linear-gradient(145deg,#f4f6ff,#e8edff);box-shadow:0 12px 30px rgba(34,48,80,.08)}.product-photo img{width:100%;height:100%;object-fit:cover}.product-photo i{font-size:4rem}.product-detail{padding:14px 0;border-bottom:1px solid #edf1f5}.product-detail:last-child{border:0}</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h2>{{ $product->product_name }}</h2><p class="text-muted mb-0">{{ $product->product_code }}</p></div><div>@if($product->barcode)<a href="{{ route('products.label', $product) }}" class="btn btn-outline-dark">Print Barcode</a>@endif <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit Product</a></div></div>
<div class="card shadow-sm"><div class="card-body p-4"><div class="row g-4 align-items-start"><div class="col-lg-4 d-flex justify-content-center"><div class="product-photo"><img src="{{ $product->image_url }}" alt="{{ $product->product_name }}"></div></div><div class="col-lg-8"><div class="row">
<div class="col-md-6 product-detail"><small class="text-muted">Category</small><div class="fw-bold">{{ $product->category_label }}</div></div>
<div class="col-md-6 product-detail"><small class="text-muted">Brand</small><div class="fw-bold">{{ $product->brand ?? '-' }}</div></div>
<div class="col-md-6 product-detail"><small class="text-muted">Current Stock</small><div class="fw-bold">{{ $product->quantity }} {{ $product->unit }}</div></div>
<div class="col-md-6 product-detail"><small class="text-muted">Minimum Stock</small><div class="fw-bold">{{ $product->minimum_stock }} {{ $product->unit }}</div></div>
<div class="col-md-6 product-detail"><small class="text-muted">Cost Price</small><div class="fw-bold">&#8369;{{ number_format((float) $product->cost_price, 2) }}</div></div>
<div class="col-md-6 product-detail"><small class="text-muted">Selling Price</small><div class="fw-bold text-primary">&#8369;{{ number_format((float) $product->selling_price, 2) }}</div></div>
<div class="col-12 product-detail"><small class="text-muted">Description</small><div>{{ $product->description ?? '-' }}</div></div>
</div></div></div></div></div>
@endsection
