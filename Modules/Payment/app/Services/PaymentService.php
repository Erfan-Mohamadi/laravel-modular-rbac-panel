<?php

namespace Modules\Payment\Services;

use Modules\Order\Models\Invoice;
use Modules\Payment\Models\Payment;
use Modules\Payment\Models\Bank;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Process payment for an invoice.
     */
    public function processPayment(Invoice $invoice): array
    {
        try {
            return DB::transaction(function () use ($invoice) {
                $transactionId = Bank::generateTransactionId();

                $payment = Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->amount,
                    'transaction_id' => $transactionId,
                    'status' => false,
                    'driver' => 'pending',
                    'message' => 'در حال پردازش...',
                ]);

                $bankResponse = Bank::processPayment($invoice->amount);

                if ($bankResponse['success']) {
                    $payment->markAsSuccess($bankResponse['tracking_code'], $bankResponse['message']);
                    $invoice->markAsSuccess();
                    $invoice->order->updateStatusBasedOnPayment();

                    return [
                        'success' => true,
                        'message' => 'پرداخت با موفقیت انجام شد.',
                        'data' => [
                            'payment_id' => $payment->id,
                            'transaction_id' => $transactionId,
                            'tracking_code' => $bankResponse['tracking_code'],
                            'amount' => $invoice->amount,
                            'status' => 'success'
                        ]
                    ];
                } else {
                    $payment->markAsFailed($bankResponse['message']);
                    $invoice->markAsFailed();
                    return [
                        'success' => false,
                        'message' => 'پرداخت ناموفق بود.',
                        'data' => [
                            'payment_id' => $payment->id,
                            'transaction_id' => $transactionId,
                            'error_message' => $bankResponse['message'],
                            'amount' => $invoice->amount,
                            'status' => 'failed'
                        ]
                    ];
                }
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'خطا در پردازش پرداخت.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment callback from bank.
     */
    public function verifyPayment(int $transactionId): array
    {
        try {
            $payment = Payment::query()->where('transaction_id', $transactionId)->first();

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'تراکنش یافت نشد.'
                ];
            }

            if ($payment->isSuccessful() || $payment->invoice->isPaid()) {
                return [
                    'success' => true,
                    'message' => 'پرداخت قبلاً تایید شده است.',
                    'data' => [
                        'payment_id' => $payment->id,
                        'status' => 'already_verified'
                    ]
                ];
            }

            return DB::transaction(function () use ($payment, $transactionId) {
                $bankVerification = Bank::verifyPayment($transactionId);

                if ($bankVerification['verified']) {
                    $payment->markAsSuccess(
                        $bankVerification['tracking_code'],
                        $bankVerification['message']
                    );
                    $payment->invoice->markAsSuccess();
                    $payment->invoice->order->updateStatusBasedOnPayment();

                    return [
                        'success' => true,
                        'message' => 'پرداخت با موفقیت تایید شد.',
                        'data' => [
                            'payment_id' => $payment->id,
                            'tracking_code' => $bankVerification['tracking_code'],
                            'status' => 'verified'
                        ]
                    ];
                } else {
                    $payment->markAsFailed($bankVerification['message']);
                    $payment->invoice->markAsFailed();

                    return [
                        'success' => false,
                        'message' => 'تایید پرداخت ناموفق بود.',
                        'data' => [
                            'payment_id' => $payment->id,
                            'error_message' => $bankVerification['message'],
                            'status' => 'verification_failed'
                        ]
                    ];
                }
            });
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'خطا در تایید پرداخت.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment status for an invoice.
     */
    public function getPaymentStatus(Invoice $invoice): array
    {
        $latestPayment = $invoice->latestPayment;

        if (!$latestPayment) {
            return [
                'status' => 'no_payment',
                'message' => 'هیچ پرداختی برای این فاکتور ثبت نشده است.'
            ];
        }

        return [
            'status' => $latestPayment->isSuccessful() ? 'paid' : 'failed',
            'payment_id' => $latestPayment->id,
            'transaction_id' => $latestPayment->transaction_id,
            'tracking_code' => $latestPayment->tracing_code,
            'amount' => $latestPayment->amount,
            'message' => $latestPayment->message,
            'created_at' => $latestPayment->created_at
        ];
    }
}
