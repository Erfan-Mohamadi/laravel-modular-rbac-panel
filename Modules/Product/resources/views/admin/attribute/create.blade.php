@extends('core::layouts.master')

@section('title', 'ایجاد ویژگی جدید')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.index') }}">مدیریت خصوصیات محصولات</a>
    </li>
    <li class="breadcrumb-item active">ایجاد ویژگی جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد ویژگی جدید</h3>
                </div>

                <form method="POST" action="{{ route('attributes.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="label" class="form-label">
                                برچسب نمایش <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('label') is-invalid @enderror"
                                   id="label"
                                   name="label"
                                   value="{{ old('label') }}"
                                   placeholder="مثال: رنگ، سایز، وزن"
                                   required>
                            <small class="form-text text-muted">
                                این متن برای کاربران نمایش داده خواهد شد.
                            </small>
                            @error('label')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">
                                نام فنی <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="مثال: color, size, weight"
                                   required>
                            <small class="form-text text-muted">
                                فقط از حروف انگلیسی کوچک، اعداد و زیرخط استفاده کنید. این نام در سیستم استفاده می‌شود.
                            </small>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="hidden" name="type" value="select">
                        <div class="form-group mb-3" style="display: none">
                            <label class="form-label">نوع ویژگی</label>
                            <input type="text" class="form-control" value="انتخابی (select)" disabled>
                        </div>
                        <div class="form-group mb-3">
                            <label for="items" class="form-label">
                                گزینه‌ها (هر گزینه در یک خط) <span class="text-danger">*</span>
                            </label>
                            <textarea name="items" id="items" class="form-control @error('items') is-invalid @enderror"
                                      rows="4" placeholder="مثال:&#10;قرمز&#10;آبی&#10;سبز">{{ old('items') }}</textarea>
                            <small class="form-text text-muted">
                                هر گزینه را در یک خط جداگانه وارد کنید.
                            </small>
                            @error('items')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="status"
                                       name="status"
                                       value="1"
                                    {{ old('status', true) ? 'checked' : '' }}>
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

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            ایجاد ویژگی
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate English name from Persian label
            const labelInput = document.getElementById('label');
            const nameInput = document.getElementById('name');

            // Persian to English mapping for common words
            const persianToEnglish = {
                'رنگ': 'color',
                'سایز': 'size',
                'اندازه': 'size',
                'وزن': 'weight',
                'طول': 'length',
                'عرض': 'width',
                'ارتفاع': 'height',
                'جنس': 'material',
                'برند': 'brand',
                'مدل': 'model',
                'نوع': 'type'
            };

            labelInput.addEventListener('input', function() {
                if (nameInput.value === '' || nameInput.dataset.autoGenerated === 'true') {
                    let generatedName = this.value.toLowerCase();

                    // Check if it's a common Persian word
                    if (persianToEnglish[this.value]) {
                        generatedName = persianToEnglish[this.value];
                    } else {
                        // Convert Persian/Arabic characters and clean up
                        generatedName = generatedName
                            .replace(/[آأإا]/g, 'a')
                            .replace(/[ی]/g, 'i')
                            .replace(/[و]/g, 'o')
                            .replace(/[ه]/g, 'h')
                            .replace(/[^a-z0-9\s]/g, '')
                            .replace(/\s+/g, '_')
                            .trim();
                    }

                    nameInput.value = generatedName;
                    nameInput.dataset.autoGenerated = 'true';
                }
            });

            nameInput.addEventListener('input', function() {
                if (this.value !== '') {
                    this.dataset.autoGenerated = 'false';
                }
            });
        });
    </script>
@endpush
