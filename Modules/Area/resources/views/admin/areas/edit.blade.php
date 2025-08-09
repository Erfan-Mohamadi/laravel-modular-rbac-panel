@extends('core::layouts.master')

@section('title', 'ویرایش ادمین')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">مدیریت ادمین‌ها</a></li>
    <li class="breadcrumb-item active">ویرایش ادمین</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">ویرایش ادمین</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.update', $admin) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">نام</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="{{ old('name', $admin->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">شماره موبایل</label>
                            <input type="text" id="mobile" name="mobile" class="form-control"
                                   value="{{ old('mobile', $admin->mobile) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="role_id" class="form-label">نقش</label>

                            @if ($isSuperAdmin)
                                <input type="text" class="form-control" readonly
                                       value="{{ $admin->role?->label ?? '---' }}">
                            @else
                                <select name="role_id" id="role_id" class="form-select" required>
                                    <option value="">انتخاب نقش</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ old('role_id', $admin->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->label }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">وضعیت</label>

                            @if ($isSuperAdmin)
                                <input type="text" class="form-control" readonly
                                       value="{{ $admin->status ? 'فعال' : 'غیرفعال' }}">
                            @else
                                <select name="status" class="form-select" required>
                                    <option value="1" {{ old('status', $admin->status) ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ old('status', $admin->status) ? '' : 'selected' }}>غیرفعال</option>
                                </select>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور (در صورت تغییر)</label>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary">انصراف</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
