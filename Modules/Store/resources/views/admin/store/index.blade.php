@extends('core::layouts.master')

@section('title', 'مدیریت موجودی انبار')

@section('breadcrumb')
    <li class="breadcrumb-item active">موجودی انبار</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">لیست موجودی انبار</h3>
                </div>

                <!-- Transaction Modal -->
                <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="transactionForm" method="POST" action="{{ route('stores.transaction') }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="transactionModalLabel">افزایش/کاهش موجودی انبار</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Product select -->
                                    <div class="mb-3">
                                        <label for="product_id" class="form-label">انتخاب محصول</label>
                                        <select name="product_id" id="product_id" class="form-select" required>
                                            <option value="">-- انتخاب محصول --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-balance="{{ $product->store->balance ?? 0 }}">
                                                    {{ $product->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Current balance display -->
                                    <div class="mb-3">
                                        <label class="form-label">موجودی فعلی: <span id="current_balance">0</span></label>
                                    </div>

                                    <!-- Amount input -->
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">مقدار</label>
                                        <input type="number" name="amount" id="amount" class="form-control" required autocomplete="off">
                                    </div>

                                    <!-- Description input -->
                                    <div class="mb-3">
                                        <label for="description" class="form-label">توضیحات</label>
                                        <input type="text" name="description" id="description" class="form-control" autocomplete="off">
                                    </div>

                                    <!-- Hidden type -->
                                    <input type="hidden" name="type" id="transaction_type" value="increase">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">ثبت</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Buttons to open modal -->
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transactionModal" data-type="increase">
                            افزایش موجودی
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#transactionModal" data-type="decrease">
                            کاهش موجودی
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>محصول</th>
                                <th>توضیح</th>
                                <th>تعداد</th>
                                <th>نوع تغییرات</th>
                                <th>تاریخ ثبت</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->store->product->title ?? '-' }}</td>
                                    <td>{{ $transaction->description ?? '-' }}</td>
                                    <td>{{ $transaction->count ?? '-' }}</td>
                                    <td>
                                        @if($transaction->type === \Modules\Store\Models\StoreTransaction::TYPE_INCREMENT)
                                            افزایش
                                        @elseif($transaction->type === \Modules\Store\Models\StoreTransaction::TYPE_DECREMENT)
                                            کاهش
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">هیچ تراکنشی وجود ندارد</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var transactionModal = document.getElementById('transactionModal');

        // Reset modal when opened and set type (increase/decrease)
        transactionModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var type = button.getAttribute('data-type');

            document.getElementById('transaction_type').value = type;
            document.getElementById('transactionModalLabel').textContent =
                type === 'increase' ? 'افزایش موجودی انبار' : 'کاهش موجودی انبار';

            // Reset fields
            document.getElementById('product_id').value = '';
            document.getElementById('current_balance').textContent = 0;
            document.getElementById('amount').value = '';
            document.getElementById('description').value = '';
        });

        // Update balance display when product is selected
        document.getElementById('product_id').addEventListener('change', function () {
            var selectedOption = this.options[this.selectedIndex];
            var balance = selectedOption.getAttribute('data-balance') ?? 0;
            document.getElementById('current_balance').textContent = balance;
        });
    </script>
@endsection
