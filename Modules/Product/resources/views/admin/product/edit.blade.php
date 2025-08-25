@extends('core::layouts.master')
@section('title', 'ویرایش محصول')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">ویرایش محصول </li>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ویرایش محصول</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="title" class="form-label">عنوان محصول <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                               id="title" name="title" value="{{ old('title', $product->title) }}" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="category_id" class="form-label">دسته‌بندی <span class="text-danger">*</span></label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                                id="category_id" name="category_id" required>
                                            <option value="">انتخاب دسته‌بندی</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Brands Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div id="brands-wrapper" class="form-group mb-3" style="display: {{ old('brands', $product->brands->pluck('id')->toArray()) ? 'block' : 'none' }}">
                                        <label for="brands" class="form-label">برندها</label>
                                        <select class="form-select" id="brands" name="brands[]" multiple>
                                            @foreach($product->brands as $brand)
                                                <option value="{{ $brand->id }}" selected>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('brands')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Specialties Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div id="specialties-wrapper" class="form-group mb-3" style="display: {{ old('specialties', $product->specialties->pluck('id')->toArray()) ? 'block' : 'none' }}">
                                        <label for="specialties" class="form-label">تخصص‌های محصول</label>
                                        <select class="form-select" id="specialties" name="specialties[]" multiple>
                                            @foreach($product->specialties as $specialty)
                                                <option value="{{ $specialty->id }}" selected>{{ $specialty->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('specialties')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Specialty Values Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div id="specialty-values-wrapper">
                                        @foreach($product->specialties as $specialty)
                                            <div class="form-group mb-3 d-flex align-items-center gap-2" id="specialty-field-{{ $specialty->id }}">
                                                @if($specialty->type === 'text')
                                                    <label class="form-label mb-0 text-nowrap">{{ $specialty->name }} (متنی)</label>
                                                    <input type="text"
                                                           name="specialty_values[{{ $specialty->id }}]"
                                                           value="{{ old('specialty_values.'.$specialty->id, $specialtyValues[$specialty->id] ?? '') }}"
                                                           class="form-control"
                                                           placeholder="مقدار {{ $specialty->name }}">
                                                @elseif($specialty->type === 'select')
                                                    <label class="form-label mb-0 text-nowrap">{{ $specialty->name }} <span>(انتخابی)</span></label>
                                                    <select name="specialty_items[{{ $specialty->id }}][]"
                                                            class="form-select js-specialty-multi"
                                                            multiple>
                                                        @foreach($specialty->items as $item)
                                                            @php
                                                                $selectedItems = old('specialty_items.'.$specialty->id, isset($specialtyValues[$specialty->id]) ? (is_array($specialtyValues[$specialty->id]) ? $specialtyValues[$specialty->id] : [$specialtyValues[$specialty->id]]) : []);
                                                            @endphp
                                                            <option value="{{ $item->id }}"
                                                                {{ in_array($item->id, $selectedItems) ? 'selected' : '' }}>
                                                                {{ $item->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="description" class="form-label">توضیحات</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                        @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- weight -->

                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="price" class="form-label">قیمت (تومان)</label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000">
                                        @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="discount" class="form-label">تخفیف (تومان)</label>
                                        <input type="number" class="form-control @error('discount') is-invalid @enderror"
                                               id="discount" name="discount" value="{{ old('discount', $product->discount) }}" min="0" step="1000">
                                        @error('discount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label for="weight" class="form-label">وزن (گرم)</label>
                                        <input type="number" class="form-control" id="weight" name="weight"
                                               value="{{ old('weight', $product->weight) }}" placeholder="0"
                                               min="0" step="50" required autocomplete="off">
                                        <small class="form-text text-muted d-block" >وزن باید به گرم وارد شود.</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label for="availability" class="form-label">وضعیت موجودی</label>
                                        <select class="form-select @error('availability') is-invalid @enderror"
                                                id="availability" name="availability">
                                            <option value="" selected>انتخاب کنید</option>
                                            @foreach($availabilityStatuses as $key => $status)
                                                <option value="{{ $key }}"
                                                    {{ old('availability', $product->availability) == $key}}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('availability')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Price Preview -->
                            <div class="row">
                                <div class="col-12">
                                    <div id="price-preview" class="alert alert-info" style="display: {{ ($product->price > 0 || $product->discount > 0) ? 'block' : 'none' }}">
                                        <h6>پیش‌نمای قیمت:</h6>
                                        <div>قیمت اصلی: <span id="original-price">{{ number_format($product->price) }} تومان</span></div>
                                        <div>تخفیف: <span id="discount-amount">{{ number_format($product->discount) }} تومان</span></div>
                                        <hr>
                                        <div><strong>قیمت نهایی: <span id="final-price" class="text-success">{{ number_format(max($product->price - $product->discount, 0)) }} تومان</span></strong></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Image -->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label for="main_image" class="form-label">تصویر اصلی محصول</label>
                                        <input type="file" class="form-control @error('main_image') is-invalid @enderror"
                                               id="main_image" name="main_image" accept="image/*">
                                        <small class="form-text text-muted">فرمت‌های مجاز: JPG, PNG, GIF</small>
                                        @error('main_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @if($product->getMainImageUrl('main'))
                                        <div class="current-main-image mb-3">
                                            <label class="form-label">تصویر اصلی فعلی:</label>
                                            <div class="card" style="width: 200px;">
                                                <img src="{{ $product->getMainImageUrl('main') }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input"
                                                               name="remove_main_image" value="1"
                                                               id="remove_main_image">
                                                        <label class="form-check-label" for="remove_main_image">
                                                            حذف تصویر اصلی
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="gallery_images" class="form-label">تصاویر گالری</label>
                                        <input type="file" class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.*') is-invalid @enderror"
                                               id="gallery_images" name="gallery_images[]" multiple accept="image/*">
                                        <small class="form-text text-muted">می‌توانید چندین تصویر انتخاب کنید. فرمت‌های مجاز: JPG, PNG, GIF</small>
                                        @error('gallery_images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('gallery_images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Current Gallery Images -->
                            @if($galleryImages && $galleryImages->count() > 0)
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">تصاویر گالری فعلی</label>
                                            <div class="row">
                                                @foreach($galleryImages as $image)
                                                    <div class="col-md-2 mb-2">
                                                        <div class="card">
                                                            <img src="{{ $image->getUrl() }}" class="card-img-top" style="height: 100px; object-fit: cover;">
                                                            <div class="card-body p-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                           name="remove_gallery_images[]" value="{{ $image->id }}"
                                                                           id="remove_gallery_image_{{ $image->id }}">
                                                                    <label class="form-check-label" for="remove_gallery_image_{{ $image->id }}">
                                                                        حذف
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> به‌روزرسانی محصول
                                        </button>
                                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> انصراف
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
            const brandsSelect = document.getElementById('brands-wrapper');
            const brandsSelectEl = document.getElementById('brands');

            // Restore old value if validation fails or current product brands
            const oldBrands = @json(old('brands', $product->brands->pluck('id')->toArray()));

            const brandsByCategoryUrl = "{{ route('brands.byCategory') }}";
            const cache = {};

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
                        data = await res.json();
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

            // For validation failure recovery or current product data
            const oldSpecialties = @json(old('specialties', $product->specialties->pluck('id')->toArray()));
            const oldSpecialtyValues = @json(old('specialty_values', $specialtyValues ?? []));
            const oldSpecialtyItems = @json(old('specialty_items', []));

            const specialtiesByCategoryUrl = "{{ route('products.specialties.byCategory') }}";
            const cache = {};

            function initSelect2(selectorOrEl) {
                if (window.jQuery && jQuery.fn && typeof jQuery.fn.select2 === 'function') {
                    const $el = window.jQuery(selectorOrEl);
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
                        const oldSel = (oldSpecialtyItems && oldSpecialtyItems[String(sp.id)])
                            ? oldSpecialtyItems[String(sp.id)].map(String)
                            : [];

                        const options = items.map(it => {
                            const isSelected = oldSel.includes(String(it.id)) ? 'selected' : '';
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
                        wrap.innerHTML = `
                    <label class="form-label">${sp.name}</label>
                    <input type="text" name="specialty_values[${sp.id}]" class="form-control" placeholder="مقدار ${sp.name}">
                `;
                    }

                    specialtyValuesWrapper.appendChild(wrap);
                });

                initSelect2('.js-specialty-multi');
            }

            async function loadSpecialties(categoryId) {
                clearAll();
                if (!categoryId) return;

                specialtiesWrapper.classList.add('specialties-loading');

                try {
                    let data;
                    if (cache[categoryId]) {
                        data = cache[categoryId];
                    } else {
                        const url = `${specialtiesByCategoryUrl}?category_id=${encodeURIComponent(categoryId)}`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('Failed to load specialties');
                        data = await res.json();
                        cache[categoryId] = data;
                    }

                    if (!Array.isArray(data) || data.length === 0) {
                        clearAll();
                        return;
                    }

                    populateSpecialtiesSelect(data);

                    const initiallySelected = (window.jQuery && jQuery(specialtiesSelect).val())
                        ? jQuery(specialtiesSelect).val()
                        : (oldSpecialties || []);
                    renderFields(initiallySelected, data);

                    if (window.jQuery) {
                        jQuery(specialtiesSelect).off('change.specialties').on('change.specialties', function () {
                            const selectedIds = jQuery(this).val() || [];
                            renderFields(selectedIds, data);
                        });
                    } else {
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

            categorySelect.addEventListener('change', function () {
                loadSpecialities(this.value);
            });

            // Initial load with current category value
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
    </style>
@endpush
