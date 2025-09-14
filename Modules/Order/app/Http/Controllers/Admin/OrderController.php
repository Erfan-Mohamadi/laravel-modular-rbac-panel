<?php

namespace Modules\Order\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Modules\Order\Models\Order;
use Modules\Store\Models\Store;
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

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

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

        // Shipping cost from order
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

            // Prevent canceling paid orders directly
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

                // Restore inventory
                foreach ($order->orderItems as $item) {
                    $store = Store::firstOrCreate(
                        ['product_id' => $item->product_id],
                        ['balance' => 0]
                    );
                    $store->increment('balance', $item->quantity);
                }

                if ($order->invoice && !$order->invoice->isPaid()) {
                    $order->invoice->markAsFailed();
                }
            });

            return redirect()->back()->with('success', 'سفارش با موفقیت لغو شد.');

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
                // Restore inventory if not already failed
                if ($order->status !== 'failed') {
                    foreach ($order->orderItems as $item) {
                        $store = Store::firstOrCreate(
                            ['product_id' => $item->product_id],
                            ['balance' => 0]
                        );
                        $store->increment('balance', $item->quantity);
                    }
                }

                if ($order->invoice) {
                    $order->invoice->payments()->delete();
                    $order->invoice->delete();
                }

                $order->orderItems()->delete();
                $order->delete();
            });

            return redirect()->route('orders.index')->with('success', 'سفارش با موفقیت حذف شد.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'خطا در حذف سفارش: ' . $e->getMessage());
        }
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['customer', 'orderItems.product', 'invoice']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', fn($q) => $q->where('name','like',"%{$search}%")
                ->orWhere('email','like',"%{$search}%")
                ->orWhere('mobile','like',"%{$search}%"));
        }
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        $orders = $query->orderBy('created_at','desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="orders_export_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        return response()->stream(function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'شناسه سفارش', 'نام مشتری', 'ایمیل مشتری', 'تلفن مشتری',
                'مبلغ کل', 'هزینه ارسال', 'وضعیت', 'وضعیت پرداخت',
                'تاریخ ثبت سفارش','آدرس ارسال'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->customer->name ?? '',
                    $order->customer->email ?? '',
                    $order->customer->mobile ?? '',
                    number_format($order->amount),
                    number_format($order->shipping_cost),
                    $this->getStatusLabel($order->status),
                    $order->invoice ? $this->getPaymentStatusLabel($order->invoice->status) : 'بدون فاکتور',
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->formatted_address
                ]);
            }

            fclose($file);
        }, 200, $headers);
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
