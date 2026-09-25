<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light d-flex align-items-center min-vh-100">
<main class="container" style="max-width:440px">
    <h1 class="h3 mb-3">Choose a new password</h1>
    @error('email')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <form method="POST" action="{{ route('password.update') }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label class="form-label" for="email">Email address</label>
        <input class="form-control mb-3" id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
        <label class="form-label" for="password">New password</label>
        <input class="form-control mb-3" id="password" type="password" name="password" required autocomplete="new-password">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input class="form-control mb-3" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        <button class="btn btn-primary w-100" type="submit">Reset password</button>
    </form>
</main>
</body>
</html>