@extends('core::layouts.master')

@section('title', 'مدیریت شهرها')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت شهرها</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            {{-- Filter Form --}}
            <form action="{{ route('cities.index') }}" method="GET" class="mb-3 row g-3 align-items-center">
                <div class="col-auto">
                    <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="نام شهر" autocomplete="off">
                </div>
                <div class="col-auto">
                    <select name="province_id" class="form-select">
                        <option value="">انتخاب استان</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->id }}" @selected(request('province_id') == $province->id)>{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">فیلتر</button>
                    <a href="{{ route('cities.index') }}" class="btn btn-secondary">حذف فیلتر</a>
                </div>
            </form>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست شهرها</h3>
                    <a href="{{ route('cities.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد شهر جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>شناسه</th>
                            <th>نام شهر</th>
                            <th>استان</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cities as $city)
                            <tr>
                                <td>{{ $loop->iteration + ($cities->currentPage() - 1) * $cities->perPage() }}</td>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->province->name ?? '---' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm btn-warning" title="ویرایش" style="margin-left: 1rem; border-radius: 5px">
                                            <i class="fas fa-edit"></i>
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <button class="btn btn-sm btn-danger" style="margin-left: 1rem; border-radius: 5px" onclick="confirmDelete('delete-{{ $city->id }}')" title="حذف">
                                            <i class="fas fa-trash"></i>
                                            <i class="bi bi-trash3"></i>
                                        </button>

                                        <form action="{{ route('cities.destroy', $city) }}" method="POST" id="delete-{{ $city->id }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">شهر یا رکوردی یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $cities->links('pagination::bootstrap-5') }}
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
