@extends('core::layouts.master')

@section('title', 'ایجاد مقدار جدید برای ویژگی')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.index') }}">مدیریت خصوصیات محصولات</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.items.index', $attribute) }}">مقادیر ویژگی: {{ $attribute->label }}</a>
    </li>
    <li class="breadcrumb-item active">ایجاد مقدار جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد مقدار جدید</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('attributes.items.store', $attribute) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="value" class="form-label">مقدار</label>
                            <input type="text" name="value" id="value"
                                   class="form-control @error('value') is-invalid @enderror"
                                   value="{{ old('value') }}" placeholder="مثال: قرمز">

                            @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attributes.items.index', $attribute) }}" class="btn btn-secondary">
                                بازگشت
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> ذخیره مقدار
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
