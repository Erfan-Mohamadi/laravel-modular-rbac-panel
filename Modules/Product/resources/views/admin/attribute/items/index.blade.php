@extends('core::layouts.master')

@section('title', 'مدیریت مقادیر ویژگی')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('attributes.index') }}">مدیریت خصوصیات محصولات</a>
    </li>
    @if($attribute == null)
        <li class="breadcrumb-item active">مقادیر ویژگی: جدید</li>
    @else
        <li class="breadcrumb-item active">مقادیر ویژگی: {{ $attribute->label }}</li>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            @if($attribute == null)
                                <strong>نام ویژگی جدید:</strong>
                            @else
                                <strong>نام ویژگی:</strong> {{ $attribute->label }}
                            @endif
                        </div>
                        <div class="col-md-3">
                            @if($attribute == null)
                                <strong>نام فنی جدید:</strong>
                            @else
                                <strong>نام فنی:</strong> <code>{{ $attribute->name }}</code>
                            @endif
                        </div>
                        <div class="col-md-3">
                            @if($attribute == null)
                                <strong>تعداد مقادیر جدید:</strong>
                            @else
                                <strong>تعداد مقادیر:</strong> {{ $items->total() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($attribute)
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">افزودن سریع چندین مقدار</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('attributes.items.store-multiple', $attribute) }}">
                            @csrf
                            <div class="form-group">
                                <label for="values">مقادیر (هر مقدار در یک خط)</label>
                                <textarea name="values" class="form-control" rows="4"
                                          placeholder="مثال:&#10;قرمز&#10;آبی&#10;سبز&#10;زرد"></textarea>
                                <small class="form-text text-muted">
                                    هر مقدار را در یک خط جداگانه وارد کنید. مقادیر تکراری نادیده گرفته می‌شوند.
                                </small>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> افزودن همه
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">لیست مقادیر</h3>
                        <div class="float-end">
                            <a href="{{ route('attributes.items.create', $attribute) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> افزودن مقدار جدید
                            </a>
                            <a href="{{ route('attributes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> بازگشت به ویژگی‌ها
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover align-content-center">
                            <thead class="bg-light">
                            <tr>
                                <th width="80">شناسه</th>
                                <th>مقدار</th>
                                <th width="150">تاریخ ثبت</th>
                                <th width="150">عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark" style="font-size: 0.9em;">
                                            {{ $item->value }}
                                        </span>
                                    </td>
                                    <td>{{ verta($item->created_at)->format('Y/m/d') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('attributes.items.edit', [$attribute, $item]) }}"
                                               class="btn btn-sm btn-warning" title="ویرایش"
                                               style="margin-left: 1rem; border-radius: 5px">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>

                                            <button class="btn btn-sm btn-danger"
                                                    style="margin-left: 1rem; border-radius: 5px"
                                                    onclick="confirmDelete('delete-{{ $item->id }}')"
                                                    title="حذف">
                                                <i class="bi bi-trash3"></i>
                                            </button>

                                            <form action="{{ route('attributes.items.destroy', [$attribute, $item]) }}"
                                                  method="POST" id="delete-{{ $item->id }}" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        هیچ مقداری برای این ویژگی تعریف نشده است.
                                        <br>
                                        <a href="{{ route('attributes.items.create', $attribute) }}" class="btn btn-sm btn-primary mt-2">
                                            اولین مقدار را اضافه کنید
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        @if($items->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $items->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-4">
                    برای افزودن مقادیر، ابتدا باید ویژگی را ایجاد کنید.
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(formId) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: "این مقدار از ویژگی حذف خواهد شد!",
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
