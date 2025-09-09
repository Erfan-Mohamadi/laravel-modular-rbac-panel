<?php

namespace Modules\Payment\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Payment\Models\Payment;
use Modules\Payment\Services\PaymentService;
use Modules\Order\Models\Invoice;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Process payment for an invoice.
     */
    public function processPayment(Request $request, int $invoiceId): JsonResponse
    {
        try {
            $invoice = Invoice::with('order')->findOrFail($invoiceId);

            if ($invoice->order->customer_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما مجاز به پرداخت این فاکتور نیستید.'
                ], 403);
            }

            if ($invoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'این فاکتور قبلاً پرداخت شده است.'
                ], 400);
            }

            $result = $this->paymentService->processPayment($invoice);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 400);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'فاکتور یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در پردازش پرداخت.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify payment callback from bank.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'transaction_id' => 'required|integer'
            ]);

            $transactionId = $request->transaction_id;
            $result = $this->paymentService->verifyPayment($transactionId);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ], $result['success'] ? 200 : 400);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تایید پرداخت.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get payment details.
     */
    public function show(Request $request, int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::with('invoice.order')
                ->findOrFail($paymentId);

            if ($payment->invoice->order->customer_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما مجاز به مشاهده این پرداخت نیستید.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'جزئیات پرداخت با موفقیت دریافت شد.',
                'data' => [
                    'payment' => $payment,
                    'invoice' => $payment->invoice,
                    'order' => $payment->invoice->order
                ]
            ], 200);

        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'پرداخت یافت نشد.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت جزئیات پرداخت.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get payment history for a user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $customerId = $request->user()->id;

            $payments = Payment::with(['invoice.order'])
                ->whereHas('invoice.order', function ($query) use ($customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'تاریخچه پرداخت‌ها با موفقیت دریافت شد.',
                'data' => $payments
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تاریخچه پرداخت‌ها.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Webhook endpoint for bank callbacks (public endpoint).
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'transaction_id' => 'required|integer',
                'status' => 'required|string',
                'tracking_code' => 'nullable|string',
                'message' => 'nullable|string'
            ]);

            $transactionId = $request->transaction_id;
            $status = $request->status;

            $payment = Payment::query()->where('transaction_id', $transactionId)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'پرداخت یافت نشد'
                ], 404);
            }

            if ($payment->isSuccessful() || $payment->invoice->isPaid()) {
                return response()->json([
                    'success' => true,
                    'message' => 'پرداخت قبلاً پردازش شده است'
                ], 200);
            }

            if ($status === 'success') {
                $payment->markAsSuccess(
                    $request->tracking_code,
                    $request->message ?? 'پرداخت از طریق وب هوک با موفقیت انجام شد'
                );
                $payment->invoice->markAsSuccess();
                $payment->invoice->order->updateStatusBasedOnPayment();
            } else {
                $payment->markAsFailed($request->message ?? 'پرداخت از طریق وب هوک ناموفق بود');
                $payment->invoice->markAsFailed();
            }

            return response()->json([
                'success' => true,
                'message' => 'وب‌هوک با موفقیت پردازش شد'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'پردازش وب هوک ناموفق بود',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
