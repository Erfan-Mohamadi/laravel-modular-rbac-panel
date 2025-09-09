<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    /**
     * Generate a fake transaction ID.
     */
    public static function generateTransactionId(): int
    {
        return rand(1000000000, 9999999999);
    }

    /**
     * Generate a fake tracking code.
     */
    public static function generateTrackingCode(): int
    {
        return rand(100000000000, 999999999999);
    }

    /**
     * Simulate payment processing.
     */
    public static function processPayment(int $amount): array
    {
        $isSuccess = rand(1, 100) <= 90;

        if ($isSuccess) {
            return [
                'success' => true,
                'tracking_code' => self::generateTrackingCode(),
                'message' => 'پرداخت با موفقیت انجام شد. کد پیگیری: ' . self::generateTrackingCode(),
                'transaction_id' => self::generateTransactionId(),
            ];
        } else {
            $errorMessages = [
                'موجودی حساب کافی نیست.',
                'اتصال به بانک برقرار نشد.',
                'کارت مسدود می‌باشد.',
                'خطا در پردازش تراکنش.',
                'تراکنش توسط کاربر لغو شد.',
            ];

            return [
                'success' => false,
                'tracking_code' => null,
                'message' => $errorMessages[array_rand($errorMessages)],
                'transaction_id' => self::generateTransactionId(),
            ];
        }
    }

    /**
     * Verify payment status (for webhook/callback processing).
     */
    public static function verifyPayment(int $transactionId): array
    {
        $isVerified = rand(1, 100) <= 90;

        if ($isVerified) {
            return [
                'verified' => true,
                'tracking_code' => self::generateTrackingCode(),
                'message' => 'تراکنش با موفقیت تایید شد.',
            ];
        } else {
            return [
                'verified' => false,
                'tracking_code' => null,
                'message' => 'تراکنش تایید نشد یا نامعتبر است.',
            ];
        }
    }
}
