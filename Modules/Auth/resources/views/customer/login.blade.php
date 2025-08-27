<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>UserLTE | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="login-page bg-body-secondary">
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}"><b>User</b>LTE</a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Login to your account</p>

            @include('core::includes._errors')

            <form action="{{ route('customer.login') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}" class="form-control" placeholder="Mobile" required autofocus />
                    <div class="input-group-text"><span class="bi bi-phone"></span></div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required />
                    <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" name="remember" type="checkbox" value="1" />
                            <label class="form-check-label"> Remember me </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3">
                <a href="{{ route('customer.register.form') }}" class="text-center">Don't have an account? Register</a>
            </p>
        </div>
    </div>
</div>
<script src="{{ asset('js/adminlte.js') }}"></script>
</body>
</html>
