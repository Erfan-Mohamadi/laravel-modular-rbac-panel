@extends('core::layouts.master')

@section('title', 'ایجاد محصول جدید')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">ایجاد محصول جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد محصول جدید</h3>
                </div>

                <form method="POST" action="{{ route('products.store') }}">
                    @csrf
                    <div class="card-body">
                        <!-- Product Title -->
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">
                                عنوان محصول <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="نام محصول را وارد کنید"
                                   required autocomplete="off">
                            <small class="form-text text-muted">
                                عنوان محصول که برای کاربران نمایش داده می‌شود.
                            </small>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <!-- Price -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="price" class="form-label">
                                        قیمت (تومان) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           class="form-control @error('price') is-invalid @enderror"
                                           id="price"
                                           name="price"
                                           value="{{ old('price') }}"
                                           placeholder="0"
                                           min="0"
                                           step="0.01"
                                           required autocomplete="off">
                                    @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Discount -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="discount" class="form-label">
                                        تخفیف (تومان)
                                    </label>
                                    <input type="number"
                                           class="form-control @error('discount') is-invalid @enderror"
                                           id="discount"
                                           name="discount"
                                           value="{{ old('discount', 0) }}"
                                           placeholder="0"
                                           min="0"
                                           step="0.01"  autocomplete="off">
                                    <small class="form-text text-muted">
                                        مقدار تخفیف باید کمتر از قیمت باشد.
                                    </small>
                                    @error('discount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Availability Status -->
                        <div class="form-group mb-3">
                            <label for="availability_status" class="form-label">
                                وضعیت موجودی <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('availability_status') is-invalid @enderror"
                                    id="availability_status"
                                    name="availability_status"
                                    required>
                                <option value="">انتخاب کنید</option>
                                @if(isset($availabilityStatuses))
                                    @foreach($availabilityStatuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('availability_status', 'available') == $key }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="available" {{ old('availability_status', 'available') == 'available' ? 'selected' : '' }}>موجود</option>
                                    <option value="coming_soon" {{ old('availability_status') == 'coming_soon' ? 'selected' : '' }}>به زودی</option>
                                    <option value="unavailable" {{ old('availability_status') == 'unavailable' ? 'selected' : '' }}>ناموجود</option>
                                @endif
                            </select>
                            @error('availability_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Initial Stock -->
                        <div class="form-group mb-3">
                            <label for="initial_stock" class="form-label">تعداد اولیه</label>
                            <input type="number"
                                   class="form-control @error('initial_stock') is-invalid @enderror"
                                   id="initial_stock"
                                   name="initial_stock"
                                   value="{{ old('initial_stock', 0) }}"
                                   placeholder="0"
                                   min="0"
                                   step="1" autocomplete="off">
                            <small class="form-text text-muted">
                                اگر می‌خواهید موجودی اولیه برای این محصول ثبت شود، مقدار را وارد کنید.
                            </small>
                            @error('initial_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Categories -->
                        @if(isset($categories) && count($categories) > 0)
                            <div class="form-group mb-3">
                                <label for="categories" class="form-label fw-bold">دسته‌بندی‌ها</label>
                                <select name="categories[]" id="categories" class="form-select select2 @error('categories') is-invalid @enderror" multiple>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('categories', [])) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categories')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Specialties -->
                        @if(isset($specialties) && count($specialties) > 0)
                            <div class="form-group mb-3">
                                <label for="specialties" class="form-label fw-bold">تخصص‌ها</label>
                                <select name="specialties[]" id="specialties" class="form-select select2 @error('specialties') is-invalid @enderror" multiple>
                                    @foreach($specialties as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, old('specialties', [])) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('specialties')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <!-- Description -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">
                                توضیحات
                            </label>
                            <textarea name="description"
                                      id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="توضیحات کاملی از محصول ارائه دهید...">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">
                                توضیحات تکمیلی در مورد محصول.
                            </small>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="status"
                                       name="status"
                                       value="1"
                                    {{ old('status', true) ? 'checked' : '' }} autocomplete="off">
                                <label class="form-check-label" for="status">
                                    فعال
                                </label>
                                <small class="form-text text-muted d-block">
                                    فقط محصولات فعال برای کاربران نمایش داده می‌شوند.
                                </small>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
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

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            ایجاد محصول
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.getElementById('price');
            const discountInput = document.getElementById('discount');
            const pricePreview = document.getElementById('price-preview');
            const originalPriceElement = document.getElementById('original-price');
            const discountAmountElement = document.getElementById('discount-amount');
            const finalPriceElement = document.getElementById('final-price');

            // Format number with Persian separators
            function formatPrice(price) {
                return new Intl.NumberFormat('fa-IR').format(price) + ' تومان';
            }

            // Calculate and display price preview
            function updatePricePreview() {
                const price = parseFloat(priceInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                const finalPrice = Math.max(0, price - discount);

                if (price > 0) {
                    originalPriceElement.textContent = formatPrice(price);
                    discountAmountElement.textContent = formatPrice(discount);
                    finalPriceElement.textContent = formatPrice(finalPrice);
                    pricePreview.style.display = 'block';
                } else {
                    pricePreview.style.display = 'none';
                }

                // Validate discount
                if (discount >= price && price > 0) {
                    discountInput.setCustomValidity('تخفیف نمی‌تواند بیشتر یا مساوی قیمت باشد');
                    discountInput.classList.add('is-invalid');
                } else {
                    discountInput.setCustomValidity('');
                    discountInput.classList.remove('is-invalid');
                }
            }

            // Event listeners for price calculation
            priceInput.addEventListener('input', updatePricePreview);
            discountInput.addEventListener('input', updatePricePreview);

            // Initialize price preview
            updatePricePreview();

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const price = parseFloat(priceInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;

                if (discount >= price && price > 0) {
                    e.preventDefault();
                    alert('تخفیف نمی‌تواند بیشتر یا مساوی قیمت باشد');
                    discountInput.focus();
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .form-check-label {
            font-weight: normal;
        }

        .alert-info {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        #price-preview hr {
            margin: 0.5rem 0;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@endpush
