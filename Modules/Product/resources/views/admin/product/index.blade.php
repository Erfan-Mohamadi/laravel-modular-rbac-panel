@extends('core::layouts.master')

@section('title', 'مدیریت محصولات')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت محصولات</li>
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
                                       placeholder="جستجو در نام محصول..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>فعال</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="availability_status" class="form-control">
                                    <option value="">همه دسترس‌پذیری‌ها</option>
                                    @foreach(\Modules\Product\Models\Product::getAvailabilityStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ request('availability_status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">پاک کردن</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products List Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">لیست محصولات</h3>
                    <a href="{{ route('products.create') }}" class="btn btn-primary float-end">
                        <i class="fas fa-plus"></i> ایجاد محصول جدید
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover align-content-center">
                        <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>نام محصول</th>
                            <th>قیمت</th>
                            <th>تخفیف</th>
                            <th>وضعیت</th>
                            <th>دسترس‌پذیری</th>
                            <th>دسته‌بندی‌ها</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $product->title }}</td>
                                <td>{{ number_format($product->price, 0) }} تومان</td>
                                <td>
                                    @if($product->is_on_sale)
                                        <span class="badge bg-warning">
                                            {{ number_format($product->discount, 0) }} تومان
                                            ({{ $product->discount_percentage }}%)
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیرفعال</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($product->availability_status)
                                        @case(\Modules\Product\Models\Product::AVAILABLE)
                                            <span class="badge bg-success">موجود</span>
                                            @break
                                        @case(\Modules\Product\Models\Product::COMING_SOON)
                                            <span class="badge bg-info">به‌زودی</span>
                                            @break
                                        @case(\Modules\Product\Models\Product::UNAVAILABLE)
                                            <span class="badge bg-secondary">ناموجود</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($product->categories->isNotEmpty())
                                        @foreach($product->categories as $cat)
                                            <span class="badge bg-light text-dark">{{ $cat->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ verta($product->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Product actions">
                                        <a href="{{ route('products.edit', $product) }}"
                                           class="btn btn-sm btn-warning me-1" title="ویرایش">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <a href="{{ route('products.show', $product) }}"
                                           class="btn btn-sm btn-info me-1" title="نمایش">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <form action="{{ route('products.destroy', $product) }}"
                                              method="POST" id="delete-{{ $product->id }}" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete('delete-{{ $product->id }}')"
                                                title="حذف">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">هیچ محصولی یافت نشد</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    @if($products->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $products->withQueryString()->links() }}
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
    </script>
@endpush
