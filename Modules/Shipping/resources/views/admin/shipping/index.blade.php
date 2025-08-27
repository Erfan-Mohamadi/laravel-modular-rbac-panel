@extends('core::layouts.master')

@section('title', 'مدیریت حمل و نقل')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت حمل و نقل</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست حمل و نقل</h3>
                    <a href="{{ route('shipping.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد حمل و نقل جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>آیکون</th>
                            <th>تعداد استان‌ها</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($shippings as $shipping)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $shipping->name }}</td>
                                <td>
                                    @if($shipping->image)
                                        <img src="{{ asset('storage/' . $shipping->image) }}" alt="Shipping Icon" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @else
                                        <span class="badge bg-secondary">بدون آیکون</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $shipping->provinces->count() }}</span>
                                </td>
                                <td>
                                    @if($shipping->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ verta($shipping->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('shipping.edit', $shipping) }}" class="btn btn-sm btn-warning" title="ویرایش" style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" style="margin-left: 1rem; border-radius: 5px" onclick="confirmDelete('delete-{{ $shipping->id }}')" title="حذف">
                                            <i class="fas fa-trash"></i>
                                            <i class="bi bi-trash3"></i>
                                        </button>

                                        <form action="{{ route('shipping.destroy', $shipping) }}" method="POST" id="delete-{{ $shipping->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">هیچ حمل و نقلی یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $shippings->links() }}
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
