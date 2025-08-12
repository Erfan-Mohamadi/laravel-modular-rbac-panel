@extends('core::layouts.master')

@section('title', 'مدیریت خصوصیات محصولات')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت خصوصیات محصولات</li>
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
                                       placeholder="جستجو در نام یا برچسب..."
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
                                <a href="{{ route('attributes.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست خصوصیات محصولات</h3>
                    <a href="{{ route('attributes.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد ویژگی جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام فنی</th>
                            <th>برچسب نمایش</th>
                            <th>نوع</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($attributes as $attribute)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;">{{ $attribute->name }}</code>
                                </td>
                                <td>{{ $attribute->label }}</td>
                                <td>
                                    @if($attribute->type == 'text')
                                        <span class="badge bg-info">متن</span>
                                    @else
                                        <span class="badge bg-warning">انتخابی</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attribute->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ verta($attribute->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('attributes.edit', $attribute) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش"
                                           style="margin-left: 1rem; border-radius: 5px">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        @if($attribute->type == 'select')
                                            <a href="{{ route('attributes.items.index', $attribute) }}"
                                               class="btn btn-sm btn-info" title="مدیریت مقادیر"
                                               style="margin-left: 1rem; border-radius: 5px">
                                                <i class="bi bi-list-ul"></i>
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-danger"
                                                style="margin-left: 1rem; border-radius: 5px"
                                                onclick="confirmDelete('delete-{{ $attribute->id }}')"
                                                title="حذف">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                        <form action="{{ route('attributes.destroy', $attribute) }}"
                                              method="POST" id="delete-{{ $attribute->id }}" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ ویژگی‌ای یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if($attributes->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $attributes->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(formId) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این عمل قابل بازگشت نیست!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }

        function toggleStatus(formId) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "وضعیت این ویژگی تغییر خواهد کرد.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بله، تغییر دهید',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }
    </script>
@endpush
