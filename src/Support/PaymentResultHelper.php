<?php

namespace AlRajhi\PaymentGateway\Support;

class PaymentResultHelper
{
    protected static ?TransactionStatusResolver $resolver = null;

    protected static function resolver(): TransactionStatusResolver
    {
        return self::$resolver ??= new TransactionStatusResolver();
    }

    /**
     * Unified status extraction for payment result.
     * Returns array: [status_final, system_status, bank_status]
     */
    public static function extractUnifiedStatus(array $result): array
    {
        $data = isset($result['trandata_decoded']) && is_array($result['trandata_decoded'])
            ? $result['trandata_decoded']
            : $result;

        $error = $data['error'] ?? $data['errorCode'] ?? $data['error_code'] ?? null;
        $errorText = $data['errorText'] ?? $data['error_text'] ?? $data['message'] ?? null;
        $status = $data['status'] ?? null;
        $resultField = $data['result'] ?? null;

        // bank_status is the transaction outcome (CAPTURED, APPROVED, etc.) — never actionCode.
        $bankStatus = $resultField ?? $status;

        $resolver = self::resolver();
        $normalizedResult = $resolver->normalize($resultField);
        $normalizedStatus = $resolver->normalize($status);
        $classification = $resolver->classify($normalizedResult ?? $normalizedStatus);

        if (! empty($error) || ! empty($errorText) || $status === '2') {
            $statusFinal = 'failed';
            $systemStatus = 'failed';
        } elseif ($classification === 'success') {
            $statusFinal = 'success';
            $systemStatus = 'success';
        } elseif ($classification === 'pending') {
            $statusFinal = 'pending';
            $systemStatus = 'pending';
        } elseif ($classification === 'voided') {
            $statusFinal = 'voided';
            $systemStatus = 'voided';
        } elseif ($classification === 'cancelled') {
            $statusFinal = 'cancelled';
            $systemStatus = 'cancelled';
        } elseif ($classification === 'failure') {
            $statusFinal = 'failed';
            $systemStatus = 'failed';
        } elseif ($status === '1' && $resolver->isSuccessful($normalizedResult)) {
            $statusFinal = 'success';
            $systemStatus = 'success';
        } else {
            $statusFinal = 'unknown';
            $systemStatus = 'pending';
        }

        return [
            'status_final' => $statusFinal,
            'system_status' => $systemStatus,
            'bank_status' => $bankStatus,
        ];
    }

    protected static function getField(array $result, string $key): mixed
    {
        if (isset($result[$key])) {
            return $result[$key];
        }

        if (isset($result['trandata_decoded']) && is_array($result['trandata_decoded']) && isset($result['trandata_decoded'][$key])) {
            return $result['trandata_decoded'][$key];
        }

        return null;
    }

    protected static function getResultStatus(array $result): ?string
    {
        $resolver = self::resolver();
        $resultField = self::getField($result, 'result');
        $statusField = self::getField($result, 'status');

        return $resolver->normalize($resultField ?? $statusField);
    }

    public static function isSuccess(array $result): bool
    {
        if (self::isFailure($result)) {
            return false;
        }

        return self::resolver()->isSuccessful(self::getResultStatus($result));
    }

    public static function isFailure(array $result): bool
    {
        $errorFields = [
            'error', 'errorCode', 'error_code', 'errorText', 'error_text', 'message',
        ];

        foreach ($errorFields as $field) {
            $val = self::getField($result, $field);
            if ($val !== null && $val !== '') {
                return true;
            }
        }

        $status = self::getField($result, 'status');
        if ((string) $status === '2') {
            return true;
        }

        return self::resolver()->isFailure(self::getResultStatus($result));
    }

    public static function isCaptured(array $result): bool
    {
        if (self::isFailure($result)) {
            return false;
        }

        return self::resolver()->isCaptured(self::getResultStatus($result));
    }

    public static function isAuthorized(array $result): bool
    {
        if (self::isFailure($result)) {
            return false;
        }

        return self::resolver()->isAuthorized(self::getResultStatus($result));
    }

    public static function isPending(array $result): bool
    {
        if (self::isFailure($result)) {
            return false;
        }

        return self::resolver()->isPending(self::getResultStatus($result));
    }

    public static function isCancelled(array $result): bool
    {
        return self::resolver()->isCancelled(self::getResultStatus($result));
    }

    public static function isVoided(array $result): bool
    {
        return self::resolver()->isVoided(self::getResultStatus($result));
    }
}
