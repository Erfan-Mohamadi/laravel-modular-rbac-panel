@extends('core::layouts.master')

@section('title', 'ایجاد ادمین جدید')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">مدیریت ادمین‌ها</a></li>
    <li class="breadcrumb-item active">ایجاد ادمین جدید</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">ایجاد ادمین جدید</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">نام</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">شماره موبایل</label>
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                   value="{{ old('mobile') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">نقش</label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="">انتخاب نقش</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">وضعیت</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>فعال</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غیرفعال</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">ایجاد</button>
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary">انصراف</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
