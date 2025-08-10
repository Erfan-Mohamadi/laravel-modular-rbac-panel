@extends('core::layouts.master')

@section('title', 'ایجاد دسته‌بندی جدید')

@section('content')
    <div class="container-fluid px-4">
        <h4 class="mb-4">ایجاد دسته‌بندی جدید</h4>

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
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parent_id" class="form-label">دسته‌بندی والد (اختیاری)</label>
                            <select name="parent_id" class="form-control">
                                <option value="">بدون والد (دسته‌بندی اصلی)</option>
                                @foreach($parentCategories as $parentCategory)
                                    <option value="{{ $parentCategory->id }}" {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                                        {{ $parentCategory->name }}
                                    </option>
                                    @if($parentCategory->children)
                                        @foreach($parentCategory->children as $child)
                                            <option value="{{ $child->id }}" {{ old('parent_id') == $child->id ? 'selected' : '' }}>
                                                -- {{ $child->name }}
                                            </option>
                                            @if($child->children)
                                                @foreach($child->children as $grandChild)
                                                    <option value="{{ $grandChild->id }}" {{ old('parent_id') == $grandChild->id ? 'selected' : '' }}>
                                                        ---- {{ $grandChild->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">آیکن (اختیاری)</label>
                            <input type="file" name="icon" class="form-control" accept="image/png,image/jpeg,image/jpg,image/gif">
                            <div class="form-text">فایل تصویری آپلود کنید (PNG, JPG, JPEG, GIF)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch" style="margin-top: 2rem;">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    وضعیت فعال
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">بازگشت</a>
                        <button type="submit" class="btn btn-success">ایجاد دسته‌بندی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
