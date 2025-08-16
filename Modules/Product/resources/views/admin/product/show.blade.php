@extends('core::layouts.master')

@section('title', 'نمایش محصول')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">{{ $product->title }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">جزئیات محصول: {{ $product->title }}</h3>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-fill"></i> ویرایش
                    </a>
                </div>

                <div class="card-body">
                    <!-- Product Info -->
                    <div class="mb-3">
                        <strong>عنوان:</strong>
                        <p>{{ $product->title }}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>قیمت:</strong>
                            <p>{{ number_format($product->price) }} تومان</p>
                        </div>
                        <div class="col-md-6">
                            <strong>تخفیف:</strong>
                            <p>{{ number_format($product->discount) }} تومان</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>وضعیت موجودی:</strong>
                        <p>{{ $product->availability_status_label ?? $product->availability_status }}</p>
                    </div>

                    <div class="mb-3">
                        <strong>تعداد اولیه / موجودی فعلی:</strong>
                        <p>{{ $product->store->balance ?? 0 }}</p>
                    </div>

                    @if($product->categories->count() > 0)
                        <div class="mb-3">
                            <strong>دسته‌بندی‌ها:</strong>
                            <p>
                                @foreach($product->categories as $category)
                                    <span class="badge bg-primary">{{ $category->name }}</span>
                                @endforeach
                            </p>
                        </div>
                    @endif

                    @if($product->specialties->count() > 0)
                        <div class="mb-3">
                            <strong>تخصص‌ها:</strong>
                            <p>
                                @foreach($product->specialties as $specialty)
                                    <span class="badge bg-success">{{ $specialty->name }}</span>
                                @endforeach
                            </p>
                        </div>
                    @endif

                    @if($product->description)
                        <div class="mb-3">
                            <strong>توضیحات:</strong>
                            <p>{{ $product->description }}</p>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>وضعیت فعال/غیرفعال:</strong>
                        <p>{{ $product->status ? 'فعال' : 'غیرفعال' }}</p>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        بازگشت
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
