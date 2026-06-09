<?php

namespace AlRajhi\PaymentGateway\Services\BankPayment;

use AlRajhi\PaymentGateway\Enums\InquiryReferenceType;
use AlRajhi\PaymentGateway\Exceptions\ValidationException;

class SupportingTransactionValidator
{
    public function validateInquiry(array $data): void
    {
        $amount = $data['amount'] ?? $data['amt'] ?? null;

        if ($amount === null || $amount === '' || ! is_numeric($amount) || (float) $amount <= 0) {
            throw new ValidationException('Invalid amount: must be positive number');
        }

        if (empty($data['customer_ip']) || ! filter_var($data['customer_ip'], FILTER_VALIDATE_IP)) {
            throw new ValidationException('Invalid customer IP address format');
        }

        $referenceId = $data['trans_id'] ?? $data['transId'] ?? $data['reference_id'] ?? null;

        if ($referenceId === null || trim((string) $referenceId) === '') {
            throw new ValidationException('Missing required field: trans_id (reference value)');
        }

        $referenceType = $data['udf5'] ?? $data['reference_type'] ?? null;

        if ($referenceType === null || trim((string) $referenceType) === '') {
            throw new ValidationException('Missing required field: reference_type (udf5)');
        }

        $allowed = array_map(
            static fn (InquiryReferenceType $type): string => $type->value,
            InquiryReferenceType::cases()
        );

        if (! in_array((string) $referenceType, $allowed, true)) {
            throw new ValidationException(
                'Invalid reference_type: allowed values are TRANID, PaymentID, TrackID'
            );
        }

        $trackId = $data['track_id'] ?? $data['trackId'] ?? null;

        if ($trackId === null || trim((string) $trackId) === '') {
            throw new ValidationException('Missing required field: track_id');
        }
    }
}
