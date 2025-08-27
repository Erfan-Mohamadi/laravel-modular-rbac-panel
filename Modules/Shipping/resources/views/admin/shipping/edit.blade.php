@extends('core::layouts.master')

@section('title', 'ویرایش حمل و نقل')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('shipping.index') }}">حمل و نقل</a>
    </li>
    <li class="breadcrumb-item active">ویرایش حمل و نقل</li>
@endsection
@section('content')
    <div class="container-fluid px-4">
        <h4 class="mb-4">ویرایش حمل و نقل</h4>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>خطا!</strong> لطفاً خطاهای زیر را بررسی کنید:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('shipping.update', $shipping) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Name & Icon -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="name" class="form-label">نام <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $shipping->name) }}" required autocomplete="off">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="icon" class="form-label">آیکون</label>
                            <input type="file" name="icon" class="form-control" accept="image/png,image/jpeg,image/jpg,image/gif">
                            <div class="form-text">فایل تصویری آپلود کنید (PNG, JPG, JPEG, GIF)</div>
                            @if($shipping->image)
                                <img src="{{ asset('storage/' . $shipping->image) }}" alt="Shipping Icon" class="mt-2" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                            @endif
                        </div>

                        <div class="col-md-2 mb-3 mt-4">
                            <label class="form-check-label" for="status">
                                وضعیت فعال
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $shipping->status) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Provinces with price -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">استان‌ها و مبلغ (تومان)</label>
                            <small>مبالغ به اضای هر ۱۰۰ گرم مرسوله وارد شوند.</small>
                            <div class="border rounded p-3">
                                @foreach($provinces as $province)
                                    @php
                                        $pivot = $shipping->provinces->firstWhere('id', $province->id);
                                    @endphp
                                    <div class="form-check d-flex align-items-center mb-2">
                                        <input class="form-check-input me-2" type="checkbox"
                                               name="provinces[{{ $province->id }}][selected]"
                                               id="province_{{ $province->id }}" value="1"
                                            {{ old('provinces.'.$province->id.'.selected', $pivot ? 1 : 0) ? 'checked' : '' }}>
                                        <label class="form-check-label me-2" for="province_{{ $province->id }}">
                                            {{ $province->name }}
                                        </label>
                                        <input type="number" name="provinces[{{ $province->id }}][price]"
                                               class="form-control w-auto" placeholder="مبلغ ( تومان )"
                                               value="{{ old('provinces.'.$province->id.'.price', $pivot ? $pivot->pivot->price : '') }}"
                                               style="margin-left: 10px;" min="0">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('shipping.index') }}" class="btn btn-secondary">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش حمل و نقل</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
