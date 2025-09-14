@extends('core::layouts.master')

@section('title', 'جزئیات سفارش')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">مدیریت سفارشات</a></li>
    <li class="breadcrumb-item active">جزئیات سفارش #{{ $order->id }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">جزئیات سفارش #{{ $order->id }}</h3>
                    <div class="position-relative" style="display: contents">
                        {{-- Status buttons --}}
                        @if($order->status !== 'delivered' && $order->status !== 'failed')
                            <form action="{{ route('orders.update-status', $order->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                @php
                                    $nextStatus = match($order->status){
                                        'new' => 'in_progress',
                                        'in_progress' => 'delivered',
                                        default => ''
                                    };
                                @endphp
                                @if($nextStatus)
                                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                                    <button type="submit" class="btn btn-info btn-sm">تغییر به {{ $statusOptions[$nextStatus] }}</button>
                                @endif
                            </form>
                        @endif

                        {{-- Cancel button --}}
                        @if($order->status !== 'failed' && $order->status !== 'delivered')
                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="d-inline position-absolute" style="left: 1rem;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm" >لغو سفارش</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">

                    {{-- Order Status --}}
                    <h5>وضعیت سفارش</h5>
                    <span class="badge bg-{{ match($order->status){
                    'new'=>'primary',
                    'in_progress'=>'info',
                    'delivered'=>'success',
                    'failed'=>'danger',
                    default=>'secondary'
                } }}">{{ $statusOptions[$order->status] }}</span>

                    {{-- Customer Info Table --}}
                    <h5 class="mt-4">اطلاعات مشتری</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>نام</th>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>ایمیل</th>
                            <td>{{ $order->customer->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>موبایل</th>
                            <td>{{ $order->customer->mobile ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>آدرس ارسال</th>
                            <td>{{ $order->formatted_address ?? '-' }}</td>
                        </tr>
                    </table>

                    {{-- Order Items Table --}}
                    <h5 class="mt-3">محصولات</h5>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>عنوان</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>جمع</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $totalItemsPrice = 0; @endphp
                        @foreach($order->orderItems as $item)
                            @php $itemTotal = $item->quantity * $item->product->price; @endphp
                            @php $totalItemsPrice += $itemTotal; @endphp
                            <tr>
                                <td>
                                    <img src="{{ $item->product->getMainImageUrl() }}"
                                         alt="{{ $item->product->title }}" style="width:50px;">
                                </td>
                                <td>{{ $item->product->title }}</td>
                                <td>{{ number_format($item->product->price) }} تومان</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($itemTotal) }} تومان</td>
                            </tr>
                        @endforeach

                        {{-- Shipping cost row --}}
                        <tr>
                            <td colspan="4" class="text-end"><strong>هزینه ارسال</strong></td>
                            <td>{{ number_format($order->shipping_cost) }} تومان</td>
                        </tr>

                        {{-- Total cost row --}}
                        <tr>
                            <td colspan="4" class="text-end"><strong>جمع کل</strong></td>
                            <td>{{ number_format($totalItemsPrice + $order->shipping_cost) }} تومان</td>
                        </tr>
                        </tbody>
                    </table>



                </div>
            </div>

        </div>
    </div>
@endsection
