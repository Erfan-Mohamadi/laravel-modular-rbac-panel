<?php

namespace Modules\Order\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Store\Models\Store;
use Modules\Customer\Models\Wallet;
use Exception;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $query = Order::with([
            'customer:id,name,email,mobile',
            'orderItems.product',
            'invoice.latestPayment'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        $statusOptions = [
            'new' => 'جدید',
            'wait_for_payment' => 'در انتظار پرداخت',
            'in_progress' => 'در حال پردازش',
            'delivered' => 'تحویل داده شده',
            'failed' => 'لغو شده'
        ];

        return view('order::Admin.order.index', compact('orders', 'statusOptions'));
    }

    /**
     * Show the specified order.
     */
    public function show(Order $order): View
    {
        $order->load([
            'customer:id,name,email,mobile',
            'orderItems.product',
            'invoice.latestPayment',
            'invoice.payments'
        ]);

        $statusOptions = [
            'new' => 'جدید',
            'wait_for_payment' => 'در انتظار پرداخت',
            'in_progress' => 'در حال پردازش',
            'delivered' => 'تحویل داده شده',
            'failed' => 'لغو شده'
        ];

        $shippingPrice = $order->shipping_cost ?? 0;

        return view('order::Admin.order.show', compact('order', 'statusOptions', 'shippingPrice'));
    }

    /**
     * Update order status (custom action).
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:new,wait_for_payment,in_progress,delivered,failed'
            ]);

            $oldStatus = $order->status;
            $newStatus = $request->status;

            if ($order->invoice && $order->invoice->isPaid() && $newStatus === 'failed') {
                return redirect()->back()->with('error', 'نمی‌توان سفارش پرداخت شده را لغو کرد.');
            }

            DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                $order->update(['status' => $newStatus]);

                // Restore inventory if canceled
                if ($newStatus === 'failed' && in_array($oldStatus, ['in_progress', 'delivered'])) {
                    foreach ($order->orderItems as $item) {
                        $store = Store::firstOrCreate(
                            ['product_id' => $item->product_id],
                            ['balance' => 0]
                        );
                        $store->increment('balance', $item->quantity);
                    }

                    $this->processWalletRefund($order, 'تغییر وضعیت سفارش به لغو شده');
                }

                if ($order->invoice && $newStatus === 'failed' && !$order->invoice->isPaid()) {
                    $order->invoice->markAsFailed();
                }
            });

            $statusLabels = [
                'new' => 'جدید',
                'wait_for_payment' => 'در انتظار پرداخت',
                'in_progress' => 'در حال پردازش',
                'delivered' => 'تحویل داده شده',
                'failed' => 'لغو شده'
            ];

            return redirect()->back()->with('success',
                "وضعیت سفارش از '{$statusLabels[$oldStatus]}' به '{$statusLabels[$newStatus]}' تغییر یافت."
            );

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'خطا در به‌روزرسانی وضعیت سفارش: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the order (custom action).
     */
    public function cancel(Order $order): RedirectResponse
    {
        try {
            if ($order->status === 'failed') {
                return redirect()->back()->with('error', 'این سفارش قبلاً لغو شده است.');
            }

            if ($order->status === 'delivered') {
                return redirect()->back()->with('error', 'نمی‌توان سفارش تحویل داده شده را لغو کرد.');
            }

            DB::transaction(function () use ($order) {
                $order->update(['status' => 'failed']);

                foreach ($order->orderItems as $item) {
                    $store = Store::firstOrCreate(
                        ['product_id' => $item->product_id],
                        ['balance' => 0]
                    );
                    $store->increment('balance', $item->quantity);
                }

                $this->processWalletRefund($order, 'لغو سفارش');

                if ($order->invoice && !$order->invoice->isPaid()) {
                    $order->invoice->markAsFailed();
                }
            });

            return redirect()->back()->with('success', 'سفارش با موفقیت لغو شد و مبلغ به کیف پول مشتری بازگردانده شد.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'خطا در لغو سفارش: ' . $e->getMessage());
        }
    }

    /**
     * Delete the order.
     */
    public function destroy(Order $order): RedirectResponse
    {
        try {
            DB::transaction(function () use ($order) {
                if ($order->status !== 'failed') {
                    foreach ($order->orderItems as $item) {
                        $store = Store::firstOrCreate(
                            ['product_id' => $item->product_id],
                            ['balance' => 0]
                        );
                        $store->increment('balance', $item->quantity);
                    }

                    $this->processWalletRefund($order, 'حذف سفارش');
                }

                if ($order->invoice) {
                    $order->invoice->payments()->delete();
                    $order->invoice->delete();
                }

                $order->orderItems()->delete();
                $order->delete();
            });

            return redirect()->route('orders.index')->with('success', 'سفارش با موفقیت حذف شد و مبلغ به کیف پول مشتری بازگردانده شد.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'خطا در حذف سفارش: ' . $e->getMessage());
        }
    }

    /**
     * Process wallet refund for cancelled/deleted orders.
     */
    private function processWalletRefund(Order $order, string $description): void
    {
        if (!$order->invoice || !$order->invoice->isPaid()) {
            return;
        }

        $wallet = Wallet::firstOrCreate(
            ['customer_id' => $order->customer_id],
            ['balance' => 0]
        );

        $refundAmount = $order->total_amount
            ?? $order->total_price
            ?? $order->amount
            ?? $order->total
            ?? 0;

        if (!$refundAmount && $order->orderItems) {
            $refundAmount = $order->orderItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $refundAmount += $order->shipping_cost ?? 0;
        }

        if ($refundAmount > 0) {
            $wallet->refund(
                (int) $refundAmount,
                $description . " - سفارش #{$order->id}",
                Order::class,
                $order->id
            );
        }
    }

    private function getStatusLabel(string $status): string
    {
        return [
            'new' => 'جدید',
            'wait_for_payment' => 'در انتظار پرداخت',
            'in_progress' => 'در حال پردازش',
            'delivered' => 'تحویل داده شده',
            'failed' => 'لغو شده'
        ][$status] ?? $status;
    }

    private function getPaymentStatusLabel(string $status): string
    {
        return [
            'pending' => 'در انتظار پرداخت',
            'success' => 'پرداخت شده',
            'failed' => 'پرداخت ناموفق'
        ][$status] ?? $status;
    }
}
