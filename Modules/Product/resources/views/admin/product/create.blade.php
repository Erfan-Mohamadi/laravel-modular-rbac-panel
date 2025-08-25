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
                                           min="0" step="50" required autocomplete="off">
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Discount -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="discount" class="form-label">تخفیف (تومان)</label>
                                    <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                           id="discount" name="discount" value="{{ old('discount',0) }}" placeholder="0"
                                           min="0" step="50" autocomplete="off">
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
                                <option value="" selected>انتخاب کنید</option>
                                @foreach($availabilityStatuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('availability_status','available') == $key}}>
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

                        <!-- Brand select container -->
                        <div id="brands-wrapper" class="form-group mb-3" style="display:none;">
                            <label for="brands" class="form-label fw-bold">برندها</label>
                            <select name="brands[]" id="brands" class="form-select select2" multiple></select>
                        </div>


                        <!-- Specialties -->
                        <div class="form-group mb-3" id="specialties-wrapper" style="display: none;">
                            <label for="specialties" class="form-label fw-bold">مشخصات</label>
                            <select name="specialties[]" id="specialties" class="form-select select2" multiple>
                                <!-- Options will be populated dynamically -->
                            </select>
                            @error('specialties')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Specialty Values -->
                        <div class="form-group mb-3" id="specialty-values-wrapper">
                            <!-- Dynamic inputs will be injected here -->
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
                            <textarea name="description"
                                      id="description"
                                      class="form-control ckeditor @error('description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="توضیحات کاملی از محصول ارائه دهید...">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">توضیحات تکمیلی در مورد محصول.</small>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- weight -->
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="weight" class="form-label">وزن (گرم)</label>
                                <input type="number" class="form-control" id="weight" name="weight"
                                       value="{{ old('weight') }}" placeholder="0"
                                       min="0" step="50" required autocomplete="off">
                                <small class="form-text text-muted d-block" >وزن باید به گرم وارد شود.</small>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const brandsSelect = document.getElementById('brands-wrapper'); // container for brand select
            const brandsSelectEl = document.getElementById('brands'); // the actual <select>

            // Restore old value if validation fails
            const oldBrands = @json(old('brands', []));

            const brandsByCategoryUrl = "{{ route('brands.byCategory') }}";
            const cache = {};

            // init select2 safely
            function initSelect2(selectorOrEl) {
                if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
                    const $el = window.jQuery(selectorOrEl);
                    try { $el.select2('destroy'); } catch(e) {}
                    $el.select2({ width: '100%' });
                }
            }

            function clearBrands() {
                brandsSelectEl.innerHTML = '';
                brandsSelect.style.display = 'none';
            }

            function populateBrands(brands) {
                brandsSelectEl.innerHTML = '';
                brands.forEach(brand => {
                    const opt = document.createElement('option');
                    opt.value = brand.id;
                    opt.textContent = brand.name;
                    if (oldBrands.map(String).includes(String(brand.id))) opt.selected = true;
                    brandsSelectEl.appendChild(opt);
                });
                brandsSelect.style.display = 'block';
                initSelect2(brandsSelectEl);
            }

            async function loadBrands(categoryId) {
                clearBrands();
                if (!categoryId) return;

                try {
                    let data;
                    if (cache[categoryId]) {
                        data = cache[categoryId];
                    } else {
                        const url = `${brandsByCategoryUrl}?category_id=${encodeURIComponent(categoryId)}`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('Failed to load brands');
                        data = await res.json(); // [{id,name}]
                        cache[categoryId] = data;
                    }

                    if (!Array.isArray(data) || data.length === 0) {
                        clearBrands();
                        return;
                    }

                    populateBrands(data);

                } catch (e) {
                    console.error(e);
                    clearBrands();
                }
            }

            categorySelect.addEventListener('change', function () {
                loadBrands(this.value);
            });

            // initial load
            loadBrands(categorySelect.value);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const specialtiesSelect = document.getElementById('specialties');
            const specialtiesWrapper = document.getElementById('specialties-wrapper');
            const specialtyValuesWrapper = document.getElementById('specialty-values-wrapper');

            // برای بازیابی مقادیر در ولیدیشن ناموفق
            const oldSpecialties      = @json(old('specialties', []));
            const oldSpecialtyValues  = @json(old('specialty_values', []));
            const oldSpecialtyItems   = @json(old('specialty_items', []));

            // URL اکشن AJAX
            const specialtiesByCategoryUrl = "{{ route('products.specialties.byCategory') }}";

            // کش ساده تا اگر کاربر بین دسته‌ها رفت و برگشت، دوباره ریکوئست نزنیم
            const cache = {};

            // کمکی: ایمن‌سازی select2 (اگر jQuery/select2 موجود نبود، خطا نده)
            function initSelect2(selectorOrEl) {
                if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
                    const $el = window.jQuery(selectorOrEl);
                    // اگر قبلاً مقداردهی شده بود، destroy کن
                    try { $el.select2('destroy'); } catch(e) {}
                    $el.select2({ width: '100%' });
                }
            }

            function clearAll() {
                specialtiesSelect.innerHTML = '';
                specialtyValuesWrapper.innerHTML = '';
                specialtiesWrapper.style.display = 'none';
                if (window.jQuery) { jQuery(specialtiesSelect).off('change.specialties'); }
            }

            function populateSpecialtiesSelect(specialties) {
                specialtiesSelect.innerHTML = '';
                specialties.forEach(sp => {
                    const opt = document.createElement('option');
                    opt.value = sp.id;
                    opt.textContent = sp.name;
                    // اگر قبلاً ولیدیشن شکست خورده، انتخاب‌های قبلی رو نگه دار
                    if (oldSpecialties.map(String).includes(String(sp.id))) {
                        opt.selected = true;
                    }
                    specialtiesSelect.appendChild(opt);
                });
                specialtiesWrapper.style.display = 'block';
                initSelect2(specialtiesSelect);
            }

            function renderFields(selectedIds, specialties) {
                specialtyValuesWrapper.innerHTML = '';
                const selectedSet = new Set((selectedIds || []).map(String));

                specialties.forEach(sp => {
                    if (!selectedSet.has(String(sp.id))) return;

                    const wrap = document.createElement('div');
                    wrap.className = 'form-group mb-3 d-flex align-items-center gap-2';
                    wrap.id = `specialty-field-${sp.id}`;

                    if (sp.type === 'text') {
                        const val = (oldSpecialtyValues && (String(sp.id) in oldSpecialtyValues))
                            ? oldSpecialtyValues[String(sp.id)]
                            : '';
                        wrap.innerHTML = `
                    <label class="form-label mb-0 text-nowrap">${sp.name} (متنی)</label>
                    <input type="text"
                           name="specialty_values[${sp.id}]"
                           value="${val ? String(val).replace(/"/g,'&quot;') : ''}"
                           class="form-control"
                           placeholder="مقدار ${sp.name}">
                `;
                    } else if (sp.type === 'select') {
                        const items = Array.isArray(sp.items) ? sp.items : [];
                        // انتخاب‌های قدیمی
                        const oldSel = (oldSpecialtyItems && oldSpecialtyItems[String(sp.id)])
                            ? oldSpecialtyItems[String(sp.id)].map(String)
                            : [];

                        const options = items.map(it => {
                            const isSelected = oldSel.includes(String(it.id)) ? 'selected' : '';
                            // it.value ممکنه کاراکتر خاص داشته باشه
                            const text = String(it.value).replace(/</g,'&lt;').replace(/>/g,'&gt;');
                            return `<option value="${it.id}" ${isSelected}>${text}</option>`;
                        }).join('');

                        wrap.innerHTML = `
                    <label class="form-label mb-0 text-nowrap" >${sp.name} <span>(انتخابی)</span></label>
                    <select name="specialty_items[${sp.id}][]"
                            class="form-select js-specialty-multi"
                            multiple>
                        ${options}
                    </select>
                `;
                    } else {
                        // نوع ناشناخته: یک input ساده
                        wrap.innerHTML = `
                    <label class="form-label">${sp.name}</label>
                    <input type="text" name="specialty_values[${sp.id}]" class="form-control" placeholder="مقدار ${sp.name}">
                `;
                    }

                    specialtyValuesWrapper.appendChild(wrap);
                });

                // فقط مولتی‌سلکت‌های تازه ساخته‌شده رو select2 کن
                initSelect2('.js-specialty-multi');
            }

            async function loadSpecialties(categoryId) {
                clearAll();
                if (!categoryId) return;

                // لودینگ
                specialtiesWrapper.classList.add('specialties-loading');

                try {
                    let data;
                    if (cache[categoryId]) {
                        data = cache[categoryId];
                    } else {
                        const url = `${specialtiesByCategoryUrl}?category_id=${encodeURIComponent(categoryId)}`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('Failed to load specialties');
                        data = await res.json(); // [{id,name,type,type_label,items:[{id,value}]}]
                        cache[categoryId] = data;
                    }

                    if (!Array.isArray(data) || data.length === 0) {
                        clearAll();
                        return;
                    }

                    // 1) پر کردن سلکت تخصص‌ها
                    populateSpecialtiesSelect(data);

                    // 2) اگر oldSpecialties داریم، فیلدها رو همون اول باز کن
                    const initiallySelected = (window.jQuery && jQuery(specialtiesSelect).val())
                        ? jQuery(specialtiesSelect).val()
                        : (oldSpecialties || []);
                    renderFields(initiallySelected, data);

                    // 3) لیسنر change (با namespacing) تا دوباره ثبت نشه
                    if (window.jQuery) {
                        jQuery(specialtiesSelect).off('change.specialties').on('change.specialties', function () {
                            const selectedIds = jQuery(this).val() || [];
                            renderFields(selectedIds, data);
                        });
                    } else {
                        // fallback بدون jQuery/select2
                        specialtiesSelect.onchange = function () {
                            const selectedIds = Array.from(this.selectedOptions).map(o => o.value);
                            renderFields(selectedIds, data);
                        };
                    }
                } catch (e) {
                    console.error(e);
                    clearAll();
                } finally {
                    specialtiesWrapper.classList.remove('specialties-loading');
                }
            }

            // تغییر دسته‌بندی
            categorySelect.addEventListener('change', function () {
                loadSpecialties(this.value);
            });

            // اجرای اولیه با مقدار فعلی دسته‌بندی
            loadSpecialties(categorySelect.value);
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
        #specialties-wrapper {
            transition: all 0.3s ease;
        }

        #specialties {
            min-height: 100px;
        }

        #specialties option {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        #specialties option:hover {
            background-color: #f8f9fa;
        }

        /* Loading state for specialties */
        .specialties-loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .specialties-loading::after {
            content: "در حال بارگذاری...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
        }
        .ck-editor__editable_inline {
            min-height: 150px;
            border: 1px solid #ced4da;
            border-radius: 0.375rem; /* Bootstrap form-control radius */
            padding: 0.5rem;
            background-color: #fff;
            direction: rtl; /* RTL support */
        }

    </style>
@endpush
