@extends('layouts.app')
@section('title', 'Account Settings')
@section('page_description', 'Update your personal information and password.')
@section('content')
<div class="row justify-content-center"><div class="col-xl-8">
<div class="card shadow-sm"><div class="card-body p-4">
    <div class="d-flex align-items-center gap-3 mb-4"><div class="header-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div><div><h5 class="mb-0">Personal Information</h5><small class="text-muted">Signed in as {{ ucfirst($user->role) }}</small></div></div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('settings.update') }}">@csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
            <div class="col-12"><hr class="my-2"><h6>Change Password <small class="text-muted fw-normal">(optional)</small></h6></div>
            <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">New Password</label><input type="password" name="password" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
            <div class="col-12"><hr><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="two_factor_enabled" id="twoFactor" {{ $user->two_factor_enabled?'checked':'' }}><label class="form-check-label" for="twoFactor"><strong>Email two-factor authentication</strong><br><small class="text-muted">Send a six-digit security code to your email during login.</small></label></div></div>
        </div>
        <button class="btn btn-primary mt-4"><i class="bi bi-check2-circle me-1"></i> Save Changes</button>
    </form>
</div></div></div></div>
@endsection
