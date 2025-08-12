@extends('core::layouts.master')

@section('title', 'ویرایش مقدار ویژگی')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.index') }}">مدیریت خصوصیات محصولات</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.items.index', $attribute) }}">مقادیر ویژگی: {{ $attribute->label }}</a>
    </li>
    <li class="breadcrumb-item active">ویرایش مقدار</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش مقدار ویژگی: <span class="text-primary">{{ $attribute->label }}</span></h3>
                </div>

                <form method="POST" action="{{ route('attributes.items.update', [$attribute, $item]) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- Value Field -->
                        <div class="form-group mb-3">
                            <label for="value" class="form-label">
                                مقدار ویژگی <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('value') is-invalid @enderror"
                                   id="value"
                                   name="value"
                                   value="{{ old('value', $item->value) }}"
                                   placeholder="مثال: قرمز، آبی، بزرگ، کوچک"
                                   required>
                            <small class="form-text text-muted">
                                مقدار جدید را برای این ویژگی وارد کنید. مقدار باید یکتا باشد.
                            </small>
                            @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            به‌روزرسانی مقدار
                        </button>
                        <a href="{{ route('attributes.items.index', $attribute) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
