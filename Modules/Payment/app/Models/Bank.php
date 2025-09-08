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
        return rand(1000000000, 9999999999); // 10-digit random number
    }

    /**
     * Generate a fake tracking code.
     */
    public static function generateTrackingCode(): int
    {
        return rand(100000000000, 999999999999); // 12-digit random number
    }

    /**
     * Simulate payment processing.
     * Returns array with success status, tracking code, and message.
     */
    public static function processPayment(int $amount): array
    {
        // Simulate 80% success rate
        $isSuccess = rand(1, 100) <= 80;

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
        // Simulate verification process
        // In real implementation, this would call bank API to verify transaction

        $isVerified = rand(1, 100) <= 85; // 85% verification success rate

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
