<?php

namespace MyAwesomeWebsite\service;

class PaymentService
{
    // Payment methods
    const PAYMENT_METHOD_VISA = 'visa';
    const PAYMENT_METHOD_MASTERCARD = 'mastercard';
    const PAYMENT_METHOD_PAYPAL = 'paypal';

    // Payment statuses
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PENDING = 'pending';

    /**
     * Simulate payment processing
     * Returns a payment result with transaction details
     *
     * @param string $paymentMethod
     * @param string $amount
     * @param array $cardDetails (optional)
     * @return array Payment result with status, transaction_id, and message
     */
    public function processPayment(string $paymentMethod, string $amount, array $cardDetails = []): array
    {
        // Generate a unique transaction ID
        $transactionId = $this->generateTransactionId();

        // Always return success
        return [
            'status' => self::STATUS_SUCCESS,
            'transaction_id' => $transactionId,
            'message' => 'Payment processed successfully',
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Validate payment method
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isValidPaymentMethod(string $paymentMethod): bool
    {
        return in_array($paymentMethod, [
            self::PAYMENT_METHOD_VISA,
            self::PAYMENT_METHOD_MASTERCARD,
            self::PAYMENT_METHOD_PAYPAL
        ]);
    }

    /**
     * Validate card details (simulated validation)
     *
     * @param array $cardDetails
     * @return array Returns ['valid' => bool, 'errors' => array]
     */
    public function validateCardDetails(array $cardDetails): array
    {
        $errors = [];

        // Check card number (should be 16 digits for simulation)
        if (empty($cardDetails['card_number']) || !preg_match('/^\d{16}$/', $cardDetails['card_number'])) {
            $errors[] = 'Card number must be 16 digits';
        }

        // Check cardholder name
        if (empty($cardDetails['cardholder_name']) || strlen($cardDetails['cardholder_name']) < 3) {
            $errors[] = 'Cardholder name is required';
        }

        // Check expiry date
        if (empty($cardDetails['expiry_month']) || empty($cardDetails['expiry_year'])) {
            $errors[] = 'Expiry date is required';
        } else {
            $currentYear = (int)date('Y');
            $currentMonth = (int)date('m');
            $expiryYear = (int)$cardDetails['expiry_year'];
            $expiryMonth = (int)$cardDetails['expiry_month'];

            if ($expiryYear < $currentYear || ($expiryYear == $currentYear && $expiryMonth < $currentMonth)) {
                $errors[] = 'Card has expired';
            }
        }

        // Check CVV (should be 3 digits)
        if (empty($cardDetails['cvv']) || !preg_match('/^\d{3}$/', $cardDetails['cvv'])) {
            $errors[] = 'CVV must be 3 digits';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Generate a unique transaction ID
     *
     * @return string
     */
    private function generateTransactionId(): string
    {
        return 'TXN-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
    }

    /**
     * Get payment method display name
     *
     * @param string $paymentMethod
     * @return string
     */
    public function getPaymentMethodName(string $paymentMethod): string
    {
        $names = [
            self::PAYMENT_METHOD_VISA => 'Visa',
            self::PAYMENT_METHOD_MASTERCARD => 'Mastercard',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal'
        ];

        return $names[$paymentMethod] ?? 'Unknown';
    }
}