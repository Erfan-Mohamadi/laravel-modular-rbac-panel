@extends('core::layouts.master')

@section('title', 'مدیریت ویژگی‌ها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت ویژگی‌ها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <!-- Search and Filter Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">جستجو و فیلتر</h3>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                       placeholder="جستجو در نام..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('specialties.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست ویژگی‌ها</h3>
                    <a href="{{ route('specialties.create') }}" class="btn btn-success float-end">
                        <i class="fas fa-plus"></i> افزودن ویژگی جدید
                    </a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>نام</th>
                            <th>نوع</th>
                            <th>وضعیت</th>
                            <th>دسته‌بندی‌ها</th>
                            <th>گزینه‌ها (در صورت انتخابی)</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($specialties as $specialty)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $specialty->name }}</td>
                                <td>
                                    @if($specialty->type === 'select')
                                        <span class="badge bg-info">انتخابی</span>
                                    @else
                                        <span class="badge bg-secondary">متنی</span>
                                    @endif
                                </td>
                                <td>
                                    @if($specialty->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>
                                    @if($specialty->categories->count())
                                        @foreach($specialty->categories as $cat)
                                            <span class="badge bg-light text-dark">{{ $cat->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($specialty->type === 'select')
                                        @foreach($specialty->items as $item)
                                            <span class="badge bg-light text-dark">{{ $item->value }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('specialties.edit', $specialty) }}"
                                       class="btn btn-sm btn-warning" title="ویرایش"
                                       style="margin-left: 1rem; border-radius: 5px">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger"
                                            style="margin-left: 1rem; border-radius: 5px"
                                            onclick="confirmDelete('delete-{{ $specialty->id }}')"
                                            title="حذف">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                    <form action="{{ route('specialties.destroy', $specialty) }}"
                                          method="POST" id="delete-{{ $specialty->id }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">هیچ ویژگی تخصصی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
