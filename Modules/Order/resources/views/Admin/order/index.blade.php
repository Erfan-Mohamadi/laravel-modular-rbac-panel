@extends('core::layouts.master')

@section('title', 'مدیریت سفارشات')

@section('breadcrumb')
    <li class="breadcrumb-item active">مدیریت سفارشات</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">لیست سفارشات</h3>
                    <a href="{{ route('orders.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                       class="btn btn-success btn-sm"><i class="fas fa-download"></i> خروجی CSV</a>
                </div>

                <div class="card-body">

                    {{-- Filters --}}
                    <form method="GET" class="mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">همه وضعیت‌ها</option>
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="نام، ایمیل، موبایل" value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">فیلتر</button>
                                <a href="{{ route('orders.index') }}" class="btn btn-secondary">حذف فیلتر</a>
                            </div>
                        </div>
                    </form>

                    {{-- Orders Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-content-center">
                            <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>مشتری</th>
                                <th>تعداد اقلام</th>
                                <th>مبلغ کل</th>
                                <th>وضعیت</th>
                                <th>وضعیت پرداخت</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>                                    <td>
                                        <strong>{{ $order->customer->name ?? 'نامشخص' }}</strong><br>
                                        <small>{{ $order->customer->email ?? '' }}</small><br>
                                        <small>{{ $order->customer->mobile ?? '' }}</small>
                                    </td>
                                    <td>{{ $order->total_items }} قلم</td>
                                    <td>{{ number_format($order->amount) }} تومان</td>
                                    <td>
                                        @php
                                            $statusBadges = [
                                                'new' => 'primary',
                                                'wait_for_payment' => 'warning',
                                                'in_progress' => 'info',
                                                'delivered' => 'success',
                                                'failed' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusBadges[$order->status] ?? 'secondary' }}">
                                        {{ $statusOptions[$order->status] ?? $order->status }}
                                    </span>
                                    </td>
                                    <td>
                                        @if($order->invoice)
                                            @php
                                                $paymentBadges = ['pending'=>'warning','success'=>'success','failed'=>'danger'];
                                                $paymentLabels = ['pending'=>'در انتظار پرداخت','success'=>'پرداخت شده','failed'=>'پرداخت ناموفق'];
                                            @endphp
                                            <span class="badge bg-{{ $paymentBadges[$order->invoice->status] ?? 'secondary' }}">
                                            {{ $paymentLabels[$order->invoice->status] ?? $order->invoice->status }}
                                        </span>
                                        @else
                                            <span class="badge bg-secondary">بدون فاکتور</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('Y/m/d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="جزئیات"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">هیچ سفارشی یافت نشد.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $orders->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection
