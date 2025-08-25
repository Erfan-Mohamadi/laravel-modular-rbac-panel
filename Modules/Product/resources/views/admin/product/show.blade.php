@extends('core::layouts.master')

@section('title', 'نمایش محصول')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">مدیریت محصولات</a>
    </li>
    <li class="breadcrumb-item active">نمایش محصول</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $product->title }}</h3>
                </div>
                <div class="card-body">

                    <!-- Main Image -->
                    @if($product->getMainImageUrl())
                        <div class="mb-3 text-center">
                            <img src="{{ $product->getMainImageUrl() }}" alt="{{ $product->title }}" class="img-fluid" style="max-height: 300px;">
                        </div>
                    @endif


                    <!-- Gallery Images -->
                    @if($product->getMedia('gallery')->isNotEmpty())
                        <div class="mb-3">
                            <h5>گالری تصاویر</h5>
                            <div class="gallery d-flex flex-wrap gap-2">
                                @foreach($product->getMedia('gallery') as $media)
                                    <a href="{{ $media->getUrl() }}" target="_blank">
                                        <img src="{{ $media->getUrl() }}" alt="Gallery Image" class="img-thumbnail" style="max-height: 120px;">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Product Details -->
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>عنوان محصول</th>
                            <td>{{ $product->title }}</td>
                        </tr>
                        <tr>
                            <th>دسته‌بندی</th>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>قیمت</th>
                            <td>{{ number_format($product->price) }} تومان</td>
                        </tr>
                        <tr>
                            <th>تخفیف</th>
                            <td>{{ number_format($product->discount) }} تومان</td>
                        </tr>
                        <tr>
                            <th>قیمت نهایی</th>
                            <td>{{ number_format(max($product->price - $product->discount,0)) }} تومان</td>
                        </tr>
                        <tr>
                            <th>وضعیت موجودی</th>
                            <td>{{ \Modules\Product\Models\Product::getAvailabilityStatuses()[$product->availability_status] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>موجودی</th>
                            <td>{{ $product->store?->balance ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>توضیحات</th>
                            <td>{!! nl2br(e($product->description)) !!}</td>
                        </tr>
                        <tr>
                            <th>تخصص‌ها</th>
                            <td>
                                @if($product->specialties->count() > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($product->specialties as $specialty)
                                            <li>
                                                <strong>{{ $specialty->name }} : </strong>
                                                @if($specialty->pivot->specialty_item_id)
                                                    {{ $specialty->items()->find($specialty->pivot->specialty_item_id)?->value ?? '-' }}
                                                @else
                                                    {{ $specialty->pivot->value ?? '-' }}
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>برندها</th>
                            <td>
                                @if($product->brands->count() > 0)
                                    {{ $product->brands->pluck('name')->join(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="card-footer">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> بازگشت
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
