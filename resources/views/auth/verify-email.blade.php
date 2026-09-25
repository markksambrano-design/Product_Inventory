<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify Email</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light d-flex align-items-center min-vh-100">
<main class="container text-center" style="max-width:520px">
    <h1 class="h3 mb-3">Verify your email address</h1>
    <p class="text-secondary">Before continuing, please check your email for a verification link.</p>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary" type="submit">Resend verification email</button></form>
    <form class="mt-3" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-link text-secondary" type="submit">Sign out</button></form>
</main>
</body>
</html>