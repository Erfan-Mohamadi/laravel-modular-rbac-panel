@extends('core::layouts.master')

@section('title', 'ایجاد ادمین جدید')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">مدیریت ادمین‌ها</a></li>
    <li class="breadcrumb-item active">ایجاد ادمین جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">ایجاد ادمین جدید</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">نام و نام خانوادگی</label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">شماره موبایل</label>
                            <input type="text" name="mobile" id="mobile"
                                   class="form-control @error('mobile') is-invalid @enderror"
                                   value="{{ old('mobile') }}" required>
                            @error('mobile')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">نقش</label>
                            <select name="role_id" id="role_id"
                                    class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">انتخاب نقش</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->label }}
                                    </option>
                                @endforeach

                            </select>
                            @error('role_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">وضعیت</label>
                            <select name="status" id="status"
                                    class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>فعال</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                            </select>
                            @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary">بازگشت</a>
                            <button type="submit" class="btn btn-success">ایجاد ادمین</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
