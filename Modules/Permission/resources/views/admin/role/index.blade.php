@extends('core::layouts.master')

@section('title', 'مدیریت نقش‌ها و مجوزها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت نقش‌ها و مجوزها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست نقش‌ها</h3>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد نقش جدید
                    </a>
                </div>
                <div class="card-body">

                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>نام قابل مشاهده</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->label }}</td>
                                <td>{{ verta($role->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('roles.edit', $role) }}"
                                           class="btn btn-sm btn-warning" title="ویرایش"
                                           style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger"
                                                style="margin-left: 1rem; border-radius: 5px"
                                                onclick="confirmDelete('delete-{{ $role->id }}')"
                                                title="حذف"
                                                @if($role->users_count > 0) disabled @endif>
                                            <i class="fas fa-trash"></i>
                                            <i class="bi bi-trash3"></i>
                                        </button>

                                        <form action="{{ route('roles.destroy', $role) }}"
                                              method="POST" id="delete-{{ $role->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ نقشی یافت نشد</td>
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
