<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light d-flex align-items-center min-vh-100">
<main class="container" style="max-width:440px">
    <h1 class="h3 mb-3">Reset your password</h1>
    <p class="text-secondary">Enter your email address and we will send you a reset link.</p>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @error('email')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <form method="POST" action="{{ route('password.email') }}">@csrf
        <label class="form-label" for="email">Email address</label>
        <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <button class="btn btn-primary w-100" type="submit">Send reset link</button>
    </form>
    <a class="d-block mt-3 text-center" href="{{ route('login') }}">Back to sign in</a>
</main>
</body>
</html>