<div class="card shadow-sm"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ $action }}" method="POST">@csrf @if($method === 'PUT') @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Supplier Name</label><input name="supplier_name" class="form-control" value="{{ old('supplier_name', $supplier?->supplier_name) }}" required></div>
<div class="col-md-6"><label class="form-label">Contact Person</label><input name="contact_person" class="form-control" value="{{ old('contact_person', $supplier?->contact_person) }}"></div>
<div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $supplier?->phone) }}"></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $supplier?->email) }}"></div>
<div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address', $supplier?->address) }}</textarea></div>
<div class="col-12 form-check ms-2"><input type="checkbox" name="status" id="status" class="form-check-input" {{ old('status', $supplier?->status ?? true) ? 'checked' : '' }}><label for="status" class="form-check-label">Active Supplier</label></div>
</div><div class="mt-4"><button class="btn btn-primary">Save Supplier</button> <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a></div></form>
</div></div>
