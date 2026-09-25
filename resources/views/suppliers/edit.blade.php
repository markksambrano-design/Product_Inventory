@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')
<h2 class="mb-4">Edit Supplier</h2>
@include('suppliers.form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT', 'supplier' => $supplier])
@endsection
