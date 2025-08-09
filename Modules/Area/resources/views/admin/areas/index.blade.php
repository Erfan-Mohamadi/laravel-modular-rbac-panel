@extends('core::layouts.master')

@section('title', 'مدیریت ادمین‌ها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت ادمین‌ها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست ادمین‌ها</h3>
                    <a href="{{ route('admin.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد ادمین جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>شماره موبایل</th>
                            <th>نقش</th>
                            <th>وضعیت</th>
                            <th>آخرین ورود</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->mobile }}</td>
                                <td>{{ $admin->role?->label ?? '---' }}</td>
                                <td>
                                    <span class="badge bg-{{ $admin->status ? 'success' : 'danger' }}">
                                        {{ $admin->status ? 'فعال' : 'غیرفعال' }}</span>
                                </td>

                                <td>{{ $admin->formatted_last_login_date }}</td>
                                <td>{{ verta($admin->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.edit', $admin) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش"
                                           style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        @if($admin->role?->name !== \Modules\Permission\Models\Role::SUPER_ADMIN)
                                            <button class="btn btn-sm btn-danger"
                                                    style="margin-left: 1rem; border-radius: 5px"
                                                    onclick="confirmDelete('delete-{{ $admin->id }}')"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                                <i class="bi bi-trash3"></i>
                                            </button>

                                            <form action="{{ route('admin.destroy', $admin) }}"
                                                  method="POST" id="delete-{{ $admin->id }}">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">هیچ ادمینی یافت نشد</td>
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
