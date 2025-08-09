@extends('core::layouts.master')

@section('title', 'ایجاد شهر جدید')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cities.index') }}">مدیریت شهرها</a></li>
    <li class="breadcrumb-item active">ایجاد شهر جدید</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ایجاد شهر جدید</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('cities.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">نام شهر</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control
                            @error('name') is-invalid @enderror" required autocomplete="off">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="mb-3">
                            <label for="province_id" class="form-label">استان</label>
                            <select name="province_id" id="province_id" class="form-select @error('province_id') is-invalid @enderror" required>
                                <option value="">انتخاب استان</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">ثبت</button>
                        <a href="{{ route('cities.index') }}" class="btn btn-secondary">بازگشت</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
