@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<h2 class="mb-4">Edit User</h2>
<div class="card shadow-sm"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('users.update', $user) }}" method="POST">@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
<div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option><option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option></select></div>
<div class="col-md-6"></div>
<div class="col-md-6"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"><small class="text-muted">Leave blank to keep current password.</small></div>
<div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
</div><div class="mt-4"><button class="btn btn-primary">Update User</button> <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
@endsection
