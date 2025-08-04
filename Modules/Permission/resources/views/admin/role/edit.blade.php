@extends('core::layouts.master')

@section('title', 'ویرایش نقش')

@section('content')
    <div class="container-fluid px-4">
        <h4 class="mb-4">ویرایش نقش</h4>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>خطا!</strong> لطفاً خطاهای زیر را بررسی کنید:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">نام (به انگلیسی)<span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="label" class="form-label">نام قابل مشاهده (به فارسی)<span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" value="{{ old('label', $role->label) }}" required autocomplete="off">
                        </div>
                    </div>
                    @if ($role->name !== $superAdminName)

                    <div class="mb-3">
                        <div style="background-color: #f0f1f2 ; border-radius: 5px" class="mb-2">
                        <label class="form-label fw-bold" style="margin-right: 1rem">دسترسی‌ها</label>
                        </div>
                        @foreach($permissions->chunk(4) as $chunk)
                            <div class="row">
                                @foreach($chunk as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->name }}"
                                                   id="permission-{{ $permission->id }}"
                                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                {{ $permission->label ?? $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">بازگشت</a>
                        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
