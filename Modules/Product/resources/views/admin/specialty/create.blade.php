@extends('core::layouts.master')

@section('title', 'ایجاد ویژگی تخصصی جدید')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('specialties.index') }}">مدیریت ویژگی‌های تخصصی</a></li>
    <li class="breadcrumb-item active">ایجاد ویژگی تخصصی جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد ویژگی تخصصی جدید</h3>
                </div>

                <form method="POST" action="{{ route('specialties.store') }}" id="mainForm">
                    @csrf

                    <div class="card-body">

                        <!-- Name Field -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">نام ویژگی <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
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
                                <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>متنی</option>
                                <option value="select" {{ old('type') == 'select' ? 'selected' : '' }}>انتخابی</option>
                            </select>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Add Items Button & Summary -->
                        <div id="add-items-container" style="display: none;">
                            <button type="button" id="show-items-modal-btn" class="btn btn-outline-primary mb-2" >
                                <i class="bi bi-plus"></i> افزودن گزینه‌ها
                            </button>
                            <div id="items-summary" class="mb-2"></div>
                        </div>

                        <!-- Categories Field with Select2 -->
                        <div class="mb-3">
                            <label for="categories" class="form-label fw-bold">دسته‌بندی‌ها</label>
                            <select name="categories[]" id="categories" class="form-select select2" multiple>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, old('categories', [])) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
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
                                    {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">فعال</label>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden Items Field for Modal Result -->
                        <div id="hidden-items-container"></div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ایجاد ویژگی تخصصی
                        </button>
                        <a href="{{ route('specialties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="modalItemsForm" autocomplete="off">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemsModalLabel">ثبت گزینه‌های ویژگی</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modal-items-fields">
                            <div class="input-group mb-2 item-input-row">
                                <input type="text" class="form-control" name="modal_items[]" placeholder="مثال: قرمز" required>
                                <button type="button" class="btn btn-danger btn-remove-modal-item">حذف</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="modal-add-item-btn">
                            افزودن گزینه جدید
                        </button>
                        <small class="form-text text-muted d-block mt-2">برای ویژگی‌های انتخابی، گزینه‌های مورد نظر را اضافه کنید.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">تایید و ادامه</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal logic
            var typeSelect = document.getElementById('type');
            var addItemsContainer = document.getElementById('add-items-container');
            var showItemsModalBtn = document.getElementById('show-items-modal-btn');
            var itemsModal = new bootstrap.Modal(document.getElementById('itemsModal'));
            var hiddenItemsContainer = document.getElementById('hidden-items-container');
            var mainForm = document.getElementById('mainForm');
            var itemsSummary = document.getElementById('items-summary');
            var modalAddItemBtn = document.getElementById('modal-add-item-btn');
            var modalItemsFields = document.getElementById('modal-items-fields');

            // Show/hide the add items button when selecting type
            function toggleItemsBtn() {
                if (typeSelect.value === 'select') {
                    addItemsContainer.style.display = 'block';
                } else {
                    addItemsContainer.style.display = 'none';
                    hiddenItemsContainer.innerHTML = '';
                    itemsSummary.innerHTML = '';
                }
            }

            typeSelect.addEventListener('change', toggleItemsBtn);
            toggleItemsBtn();

            // Open modal on button click
            showItemsModalBtn.addEventListener('click', function() {
                itemsModal.show();
            });

            // Modal add/remove logic
            modalAddItemBtn.addEventListener('click', function() {
                var row = document.createElement('div');
                row.className = 'input-group mb-2 item-input-row';
                row.innerHTML = `
                    <input type="text" class="form-control" name="modal_items[]" placeholder="مثال: قرمز" required>
                    <button type="button" class="btn btn-danger btn-remove-modal-item">حذف</button>
                `;
                modalItemsFields.appendChild(row);
            });

            modalItemsFields.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('btn-remove-modal-item')) {
                    var rows = modalItemsFields.querySelectorAll('.item-input-row');
                    if (rows.length > 1) {
                        e.target.closest('.item-input-row').remove();
                    } else {
                        e.target.closest('.item-input-row').querySelector('input').value = '';
                    }
                }
            });

            // Handle modal form submit
            document.getElementById('modalItemsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var itemInputs = modalItemsFields.querySelectorAll('input[name="modal_items[]"]');
                hiddenItemsContainer.innerHTML = '';
                let summary = [];
                itemInputs.forEach(function(input){
                    if(input.value.trim() !== '') {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'items[]';
                        hidden.value = input.value.trim();
                        hiddenItemsContainer.appendChild(hidden);
                        summary.push('<span class="badge bg-info text-dark ms-1">' + input.value.trim() + '</span>');
                    }
                });
                itemsSummary.innerHTML = summary.length ? 'گزینه‌های ثبت شده: ' + summary.join(' ') : '';
                itemsModal.hide();
            });

            // Prevent form submit if type is select and no items entered
            mainForm.addEventListener('submit', function(e) {
                if(typeSelect.value === 'select') {
                    var hasItems = !!hiddenItemsContainer.querySelector('input[name="items[]"]');
                    if(!hasItems) {
                        e.preventDefault();
                        itemsModal.show();
                    }
                }
            });
        });
    </script>
@endsection
