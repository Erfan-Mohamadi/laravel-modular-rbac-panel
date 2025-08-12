@extends('core::layouts.master')

@section('title', 'ویرایش ویژگی')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.index') }}">مدیریت خصوصیات محصولات</a>
    </li>
    <li class="breadcrumb-item active">ویرایش ویژگی</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش ویژگی: {{ $attribute->label }}</h3>
                </div>
                <form method="POST" action="{{ route('attributes.update', $attribute) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Label Field -->
                        <div class="form-group mb-3">
                            <label for="label" class="form-label">
                                برچسب نمایش <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('label') is-invalid @enderror"
                                   id="label"
                                   name="label"
                                   value="{{ old('label', $attribute->label) }}"
                                   placeholder="مثال: رنگ، سایز، وزن"
                                   required>
                            <small class="form-text text-muted">
                                این متن برای کاربران نمایش داده خواهد شد.
                            </small>
                            @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name Field -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">
                                نام فنی <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $attribute->name) }}"
                                   placeholder="مثال: color, size, weight"
                                   required>
                            <small class="form-text text-muted">
                                فقط از حروف انگلیسی کوچک، اعداد و زیرخط استفاده کنید. این نام در سیستم استفاده می‌شود.
                            </small>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Status Field -->
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="status"
                                       name="status"
                                       value="1"
                                    {{ old('status', $attribute->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    فعال
                                </label>
                                <small class="form-text text-muted d-block">
                                    فقط خصوصیات فعال قابل استفاده خواهند بود.
                                </small>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($attribute->type == 'select')
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>توجه:</strong> برای مدیریت گزینه‌های آن به بخش مقادیر ویژگی مراجعه کنید.
                            </div>
                        @endif
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            به‌روزرسانی ویژگی
                        </button>
                        <a href="{{ route('attributes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
