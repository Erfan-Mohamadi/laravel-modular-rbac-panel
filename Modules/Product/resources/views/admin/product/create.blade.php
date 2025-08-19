@extends('core::layouts.master')

@section('title', 'ایجاد محصول جدید')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">ایجاد محصول جدید</li>
@endsection
{{--@dd($specialties)--}}
@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد محصول جدید</h3>
                </div>

                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        <!-- Product Title -->
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">عنوان محصول <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}"
                                   placeholder="نام محصول را وارد کنید" required autocomplete="off">
                            <small class="form-text text-muted">عنوان محصول که برای کاربران نمایش داده می‌شود.</small>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <!-- Price -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">قیمت (تومان) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                           id="price" name="price" value="{{ old('price') }}" placeholder="0"
                                           min="0" step="0.01" required autocomplete="off">
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Discount -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="discount" class="form-label">تخفیف (تومان)</label>
                                    <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                           id="discount" name="discount" value="{{ old('discount',0) }}" placeholder="0"
                                           min="0" step="0.01" autocomplete="off">
                                    <small class="form-text text-muted">مقدار تخفیف باید کمتر از قیمت باشد.</small>
                                    @error('discount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <!-- Price Preview -->
                        <div class="form-group mb-3">
                            <div class="alert alert-info" id="price-preview" style="display: none;">
                                <h6>پیش‌نمایش قیمت:</h6>
                                <div class="d-flex justify-content-between">
                                    <span>قیمت اصلی:</span>
                                    <span id="original-price">0 تومان</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>تخفیف:</span>
                                    <span id="discount-amount" class="text-danger">0 تومان</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <span>قیمت نهایی:</span>
                                    <span id="final-price" class="text-success">0 تومان</span>
                                </div>
                            </div>
                        </div>
                        <!-- Availability Status -->
                        <div class="form-group mb-3">
                            <label for="availability_status" class="form-label">وضعیت موجودی <span class="text-danger">*</span></label>
                            <select class="form-control @error('availability_status') is-invalid @enderror"
                                    id="availability_status" name="availability_status" required>
                                <option value="">انتخاب کنید</option>
                                @foreach($availabilityStatuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('availability_status','available') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Initial Stock -->
                        <div class="form-group mb-3">
                            <label for="initial_stock" class="form-label">تعداد اولیه</label>
                            <input type="number" class="form-control @error('initial_stock') is-invalid @enderror"
                                   id="initial_stock" name="initial_stock" value="{{ old('initial_stock',0) }}"
                                   placeholder="0" min="0" step="1" autocomplete="off">
                            <small class="form-text text-muted">اگر می‌خواهید موجودی اولیه برای این محصول ثبت شود، مقدار را وارد کنید.</small>
                            @error('initial_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Categories -->
                        @if(isset($categories) && count($categories) > 0)
                            <div class="form-group mb-3">
                                <label for="category_id" class="form-label fw-bold">دسته‌بندی</label>
                                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">انتخاب دسته‌بندی</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif

                        <!-- Specialties -->
                        <div class="form-group mb-3" id="specialties-wrapper" style="display: block;">
                            <label for="specialties" class="form-label fw-bold">تخصص‌ها</label>
                            <select name="specialties[]" id="specialties" class="form-select" multiple>
                                <!-- Options will be populated dynamically -->
                            </select>
                            @error('specialties')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>


                        <!-- Images -->
                        <div class="form-group mb-3">
                            <label for="main_image" class="form-label">تصویر اصلی</label>
                            <input type="file" name="main_image" id="main_image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group mb-3">
                            <label for="gallery_images" class="form-label">گالری تصاویر</label>
                            <input type="file" name="gallery_images[]" id="gallery_images" class="form-control" accept="image/*" multiple>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="توضیحات کاملی از محصول ارائه دهید...">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">توضیحات تکمیلی در مورد محصول.</small>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                    {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">فعال</label>
                                <small class="form-text text-muted d-block">فقط محصولات فعال برای کاربران نمایش داده می‌شوند.</small>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ایجاد محصول</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // =========================
        //    Specialties Logic
        // =========================
    </script>

    <script>
        // =========================
        //    Price Preview Logic
        // =========================
        document.addEventListener('DOMContentLoaded', function () {


            const priceInput = document.getElementById('price');
            const discountInput = document.getElementById('discount');
            const previewBlock = document.getElementById('price-preview');
            const originalPriceSpan = document.getElementById('original-price');
            const discountSpan = document.getElementById('discount-amount');
            const finalPriceSpan = document.getElementById('final-price');

            function updatePricePreview() {
                const price = parseFloat(priceInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;

                if (price > 0 || discount > 0) previewBlock.style.display = 'block';
                else previewBlock.style.display = 'none';

                const finalPrice = Math.max(price - discount, 0);

                originalPriceSpan.textContent = price.toLocaleString() + ' تومان';
                discountSpan.textContent = discount.toLocaleString() + ' تومان';
                finalPriceSpan.textContent = finalPrice.toLocaleString() + ' تومان';
            }

            priceInput.addEventListener('input', updatePricePreview);
            discountInput.addEventListener('input', updatePricePreview);
            updatePricePreview(); // initial call
        });
    </script>

@endsection

@push('styles')
    <style>
        .form-check-label { font-weight: normal; }
        .alert-info { background-color: #f8f9fa; border-color: #dee2e6; }
        #price-preview hr { margin: 0.5rem 0; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
    </style>
@endpush
