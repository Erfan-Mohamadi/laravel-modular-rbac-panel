@extends('core::layouts.master')

@section('title', 'ویرایش محصول')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">ویرایش محصول</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ویرایش محصول: {{ $product->title }}</h3>
                </div>
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Title -->
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">نام محصول <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title', $product->title) }}"
                                   placeholder="مثال: لپ‌تاپ Lenovo ThinkPad"
                                   required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="form-group mb-3">
                            <label for="price" class="form-label">قیمت (تومان) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('price') is-invalid @enderror"
                                   id="price"
                                   name="price"
                                   value="{{ old('price', $product->price) }}"
                                   min="0"
                                   step="1"
                                   required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Discount -->
                        <div class="form-group mb-3">
                            <label for="discount" class="form-label">تخفیف (تومان)</label>
                            <input type="number"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   id="discount"
                                   name="discount"
                                   value="{{ old('discount', $product->discount) }}"
                                   min="0"
                                   step="1">
                            <small class="form-text text-muted">تخفیف به صورت مبلغ از قیمت اصلی کسر می‌شود.</small>
                            @error('discount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Availability Status -->
                        <div class="form-group mb-3">
                            <label for="availability_status" class="form-label">وضعیت دسترس‌پذیری</label>
                            <select name="availability_status"
                                    id="availability_status"
                                    class="form-control @error('availability_status') is-invalid @enderror">
                                @foreach($availabilityStatuses as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('availability_status', $product->availability_status) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('availability_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Categories -->
                        @if(isset($categories) && count($categories) > 0)
                            <div class="form-group mb-3">
                                <label for="categories" class="form-label fw-bold">دسته‌بندی‌ها</label>
                                <select name="categories[]" id="categories" class="form-select select2 @error('categories') is-invalid @enderror" multiple>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}"
                                            {{ in_array($id, old('categories', $product->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
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
                                        <option value="{{ $id }}"
                                            {{ in_array($id, old('specialties', $product->specialties->pluck('id')->toArray())) ? 'selected' : '' }}>
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
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="توضیحات محصول...">{{ old('description', $product->description) }}</textarea>
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
                                    {{ old('status', $product->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    فعال
                                </label>
                                <small class="form-text text-muted d-block">
                                    فقط محصولات فعال قابل مشاهده در فروشگاه خواهند بود.
                                </small>
                            </div>
                            @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            به‌روزرسانی محصول
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
