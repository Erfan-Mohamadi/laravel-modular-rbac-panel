@extends('core::layouts.master')

@section('title', 'مدیریت استان‌ها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت استان‌ها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست استان‌ها</h3>

                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام استان</th>
                            <th>تعداد شهرها</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($provinces as $province)
                            <tr>
                                <td>{{ $loop->iteration + ($provinces->currentPage() - 1) * $provinces->perPage() }}</td>
                                <td>{{ $province->name }}</td>
                                <td>{{ $province->cities()->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">استانی یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $provinces->links('pagination::bootstrap-5') }}
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
