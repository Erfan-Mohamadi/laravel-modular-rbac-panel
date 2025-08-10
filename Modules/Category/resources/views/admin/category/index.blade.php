@extends('core::layouts.master')

@section('title', 'مدیریت دسته‌بندی‌ها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت دسته‌بندی‌ها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست دسته‌بندی‌ها</h3>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد دسته‌بندی جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>آیکن</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if($category->icon)
                                        <img src="{{ asset('storage/' . $category->icon) }}" alt="Category Icon" style="width: 32px; height: 32px;">
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($category->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ verta($category->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('categories.edit', $category) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش"
                                           style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                                style="margin-left: 1rem; border-radius: 5px"
                                                onclick="confirmDelete('delete-{{ $category->id }}')"
                                                title="حذف">
                                            <i class="fas fa-trash"></i>
                                            <i class="bi bi-trash3"></i>
                                        </button>

                                        <form action="{{ route('categories.destroy', $category) }}"
                                              method="POST" id="delete-{{ $category->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">هیچ دسته‌بندی‌ای یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $categories->links('pagination::bootstrap-5') }}
                    </div>
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
