<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>UserLTE | Register Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />
</head>
<body class="register-page bg-body-secondary">
<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}"><b>User</b>LTE</a>
    </div>

    <div class="card">
        <div class="card-body register-card-body">
            <p class="register-box-msg">Register a new account</p>

            {{-- Registration Form --}}
            <form action="{{ route('customer.register') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Full Name" value="{{ old('name') }}" required />
                    <div class="input-group-text"><span class="bi bi-person"></span></div>
                </div>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" />
                    <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                </div>

                <div class="input-group mb-3">
                    <input type="text" name="mobile" class="form-control" placeholder="Mobile Number" value="{{ old('mobile') }}" required />
                    <div class="input-group-text"><span class="bi bi-phone"></span></div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required />
                    <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                </div>

                <div class="row">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" required />
                            <label class="form-check-label">
                                I agree to the <a href="#">terms</a>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3">
                <a href="{{ route('customer.login.form') }}" class="text-center">I already have an account</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
