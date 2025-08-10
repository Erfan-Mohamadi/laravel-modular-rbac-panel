@extends('core::layouts.master')

@section('title', 'مدیریت برندها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت برندها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست برندها</h3>
                    <a href="{{ route('brands.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد برند جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>تصویر</th>
                            <th>دسته‌بندی‌ها</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $brand->name }}</td>
                                <td>
                                    @if($brand->image)
                                        <img src="{{ asset('storage/' . $brand->image) }}" alt="Brand Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <span class="badge bg-secondary">بدون تصویر</span>
                                    @endif
                                </td>
                                <td>
                                    @if($brand->categories->count() > 0)
                                        @foreach($brand->categories as $category)
                                            <span class="badge bg-info me-1">{{ $category->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">بدون دسته‌بندی</span>
                                    @endif
                                </td>
                                <td>
                                    @if($brand->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ verta($brand->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('brands.edit', $brand) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش"
                                           style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                                style="margin-left: 1rem; border-radius: 5px"
                                                onclick="confirmDelete('delete-{{ $brand->id }}')"
                                                title="حذف">
                                            <i class="fas fa-trash"></i>
                                            <i class="bi bi-trash3"></i>
                                        </button>

                                        <form action="{{ route('brands.destroy', $brand) }}"
                                              method="POST" id="delete-{{ $brand->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ برندی یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
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
    </script>
@endpush
