@extends('layouts.app')
@section('title', 'Add Supplier')
@section('content')
<h2 class="mb-4">Add Supplier</h2>
@include('suppliers.form', ['action' => route('suppliers.store'), 'method' => 'POST', 'supplier' => null])
@endsection
