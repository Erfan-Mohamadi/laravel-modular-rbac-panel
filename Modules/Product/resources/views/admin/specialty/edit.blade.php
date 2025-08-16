@extends('core::layouts.master')

@section('title', 'ویرایش ویژگی تخصصی')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('specialties.index') }}">مدیریت ویژگی‌های تخصصی</a></li>
    <li class="breadcrumb-item active">ویرایش ویژگی تخصصی</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش ویژگی تخصصی: {{ $specialty->name }}</h3>
                </div>

                <form method="POST" action="{{ route('specialties.update', $specialty) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <!-- Name Field -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">نام ویژگی <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $specialty->name) }}"
                                   placeholder="مثال: جنس، رنگ"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type Field -->
                        <div class="form-group mb-3">
                            <label for="type" class="form-label">نوع ویژگی <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">انتخاب کنید...</option>
                                <option value="text" {{ old('type', $specialty->type) == 'text' ? 'selected' : '' }}>متنی</option>
                                <option value="select" {{ old('type', $specialty->type) == 'select' ? 'selected' : '' }}>انتخابی</option>
                            </select>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="add-items-container" style="display: none;">
                            <button type="button" id="show-items-modal-btn" class="btn btn-outline-primary mb-2" >
                                <i class="bi bi-plus"></i> افزودن گزینه‌ها
                            </button>
                            <div id="items-summary" class="mb-2"></div>
                        </div>

                        <div class="mb-3">
                            <label for="categories" class="form-label fw-bold">دسته‌بندی‌ها</label>
                            <select name="categories[]" id="categories" class="form-select select2" multiple>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ in_array(
                                            $category->id,
                                            old(
                                                'categories',
                                                isset($specialty) ? $specialty->categories->pluck('id')->toArray() : []
                                            )
                                        ) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">برای انتخاب چند دسته‌بندی کلید Ctrl را نگه دارید.</small>
                            @error('categories')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                    {{ old('status', $specialty->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">فعال</label>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Items Field (show only if type is select) -->
                        <div class="form-group mb-3" id="items-group" style="display: {{ old('type', $specialty->type) == 'select' ? 'block' : 'none' }};">
                            <label class="form-label">گزینه‌ها <span class="text-danger">*</span></label>
                            <div id="item-fields">
                                @php
                                    $oldItems = old('items', $specialty->items->pluck('value')->toArray());
                                @endphp
                                @if($oldItems)
                                    @foreach($oldItems as $idx => $item)
                                        <div class="input-group mb-2 item-input-row">
                                            <input type="text" name="items[]" class="form-control @error('items.' . $idx) is-invalid @enderror" value="{{ $item }}" placeholder="مثال: قرمز" required>
                                            <button type="button" class="btn btn-danger btn-remove-item">حذف</button>
                                            @error('items.' . $idx)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 item-input-row">
                                        <input type="text" name="items[]" class="form-control" placeholder="مثال: قرمز" required>
                                        <button type="button" class="btn btn-danger btn-remove-item">حذف</button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-item-btn">
                                افزودن گزینه جدید
                            </button>
                            <small class="form-text text-muted">برای ویژگی‌های انتخابی، گزینه‌های مورد نظر را اضافه کنید.</small>
                            @error('items')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> بروزرسانی ویژگی تخصصی
                        </button>
                        <a href="{{ route('specialties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/Hide items field based on type
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const itemsGroup = document.getElementById('items-group');
            const itemFields = document.getElementById('item-fields');
            const addItemBtn = document.getElementById('add-item-btn');

            function toggleItemsField() {
                if (typeSelect.value === 'select') {
                    itemsGroup.style.display = 'block';
                    Array.from(itemFields.querySelectorAll('input')).forEach(i => i.required = true);
                } else {
                    itemsGroup.style.display = 'none';
                    Array.from(itemFields.querySelectorAll('input')).forEach(i => i.required = false);
                }
            }

            typeSelect.addEventListener('change', toggleItemsField);

            // Add new item input
            addItemBtn.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'input-group mb-2 item-input-row';
                row.innerHTML = `
                    <input type="text" name="items[]" class="form-control" placeholder="مثال: قرمز" required>
                    <button type="button" class="btn btn-danger btn-remove-item">حذف</button>
                `;
                itemFields.appendChild(row);
            });

            // Remove item input
            itemFields.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-remove-item')) {
                    const rows = itemFields.querySelectorAll('.item-input-row');
                    if (rows.length > 1) {
                        e.target.closest('.item-input-row').remove();
                    } else {
                        e.target.closest('.item-input-row').querySelector('input').value = '';
                    }
                }
            });

            toggleItemsField();
        });
    </script>
@endsection
