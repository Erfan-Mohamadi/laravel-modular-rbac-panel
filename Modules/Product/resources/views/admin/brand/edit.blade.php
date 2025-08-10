@extends('core::layouts.master')

@section('title', 'ویرایش برند')

@section('content')
    <div class="container-fluid px-4">
        <h4 class="mb-4">ویرایش برند: {{ $brand->name }}</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
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
                <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Brand Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">نام برند <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $brand->name) }}" required autocomplete="off">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Brand Image -->
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">تصویر برند (اختیاری)</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            <div class="form-text">فایل تصویری آپلود کنید (JPG, PNG, GIF - حداکثر 2MB)</div>
                            @if($brand->image)
                                <div class="mt-2">
                                    <small class="text-muted">تصویر فعلی: </small>
                                    <img src="{{ asset('storage/' . $brand->image) }}" alt="Brand Image" style="width: 48px; height: 48px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">توضیحات (اختیاری)</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="توضیحات برند را وارد کنید...">{{ old('description', $brand->description) }}</textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categories" class="form-label fw-bold">دسته‌بندی‌ها</label>
                        <select name="categories[]" id="categories" class="form-select select2" multiple>
                            @foreach($flatCategories as $id => $name)
                                <option value="{{ $id }}"
                                        @if(
                                            in_array($id, old('categories', $brand->categories->pluck('id')->toArray()))
                                        ) selected @endif>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" class="form-check-input" name="status" id="status" value="1"
                                    {{ old('status', $brand->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">وضعیت فعال</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('brands.index') }}" class="btn btn-secondary">بازگشت</a>
                        <button type="submit" class="btn btn-primary">به‌روزرسانی برند</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
