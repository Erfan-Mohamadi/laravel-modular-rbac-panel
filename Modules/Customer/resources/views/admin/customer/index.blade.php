@extends('core::layouts.master')

@section('title', 'مدیریت مشتری‌ها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت مشتری‌ها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست مشتری‌ها</h3>
                </div>

                <div class="card-body">

                    {{-- Filters --}}
                    <form method="GET" class="mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <input type="text" name="name" class="form-control" placeholder="نام"
                                       value="{{ $filters['name'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <input type="email" name="email" class="form-control" placeholder="ایمیل"
                                       value="{{ $filters['email'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="mobile" class="form-control" placeholder="موبایل"
                                       value="{{ $filters['mobile'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ isset($filters['status']) && $filters['status'] == 1 ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ isset($filters['status']) && $filters['status'] == 0 ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary">حذف فیلتر</a>
                            </div>
                        </div>
                    </form>

                    {{-- Customers table --}}
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>ایمیل</th>
                            <th>موبایل</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                <td>{{ $customer->name ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>
                                    @if($customer->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ verta($customer->created_at)->format('Y/m/d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">هیچ مشتری‌ای یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $customers->links('pagination::bootstrap-5') }}
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
