<?php

namespace AlRajhi\PaymentGateway\Services\Webhook;

use AlRajhi\PaymentGateway\Contracts\ArrayValueResolverContract;
use AlRajhi\PaymentGateway\Support\TransactionStatusResolver;

class WebhookTransactionMapper
{
    public function __construct(
        protected ArrayValueResolverContract $valueResolver,
        protected TransactionStatusResolver $transactionStatusResolver
    ) {
    }

    public function extractStatus(array $resultData): ?string
    {
        $status = $this->valueResolver->first($resultData, ['status', 'result', 'paymentStatus']);

        return $this->transactionStatusResolver->normalize($status);
    }

    public function hasError(array $resultData): bool
    {
        $error = $this->valueResolver->first($resultData, ['error', 'errorCode', 'error_code']);
        $errorText = $this->valueResolver->first($resultData, ['errorText', 'error_text', 'message']);

        return $this->valueResolver->isNotNullish($error)
            || $this->valueResolver->isNotNullish($errorText);
    }

    /**
     * @return 'success'|'failure'|'pending'|'voided'|'cancelled'|'unknown'
     */
    public function resolveOutcome(array $resultData): string
    {
        $normalizedStatus = $this->extractStatus($resultData);

        if ($this->hasError($resultData) && ! $this->transactionStatusResolver->isSuccessful($normalizedStatus)) {
            return 'failure';
        }

        return $this->transactionStatusResolver->classify($normalizedStatus);
    }

    public function isSuccessfulStatus(array $resultData): bool
    {
        return $this->resolveOutcome($resultData) === 'success';
    }

    public function mapToTransaction(array $payloadData, array $resultData): array
    {
        $normalizedStatus = $this->extractStatus($resultData);
        $outcome = $this->resolveOutcome($resultData);

        return [
            'payment_id' => $this->valueResolver->first($payloadData, ['paymentId', 'paymentid']),
            'transaction_id' => $this->valueResolver->first($payloadData, ['transId', 'transid']),
            'reference_number' => $this->valueResolver->first($payloadData, ['ref', 'referenceNo']),
            'track_id' => $this->valueResolver->first($payloadData, ['trackId', 'trackid']),
            'amount' => $this->valueResolver->first($payloadData, ['amt', 'amount']),
            'auth_code' => $this->valueResolver->first($payloadData, ['authCode', 'authcode']),
            'auth_response_code' => $this->valueResolver->first($payloadData, ['authRespCode', 'authrespcode']),
            'card_type' => $this->valueResolver->first($payloadData, ['cardType', 'cardtype']),
            'action_code' => $this->valueResolver->first($payloadData, ['actionCode', 'actioncode']),
            'card_number' => $this->valueResolver->first($payloadData, ['card', 'maskedCard', 'maskedCardNo']),
            'exp_month' => $this->valueResolver->first($payloadData, ['expMonth', 'expmonth']),
            'exp_year' => $this->valueResolver->first($payloadData, ['expYear', 'expyear']),
            'orig_transaction_id' => $this->valueResolver->first($payloadData, ['origTransactionID', 'origTransactionId']),
            'result' => $this->valueResolver->first($resultData, ['status', 'result', 'paymentStatus']),
            'normalized_status' => $normalizedStatus,
            'payment_outcome' => $outcome,
            'payment_status' => $outcome,
            'timestamp' => $this->valueResolver->first($payloadData, ['paymentTimestamp', 'timestamp']) ?? now()->toIso8601String(),
            'raw_payload' => $payloadData,
            'raw_result' => $resultData,
        ];
    }

    public function mapToFailure(array $resultData, array $payloadData): array
    {
        $outcome = $this->resolveOutcome($resultData);

        return [
            'error_code' => $this->valueResolver->first($resultData, ['error', 'errorCode', 'error_code']) ?? 'UNKNOWN_ERROR',
            'error_text' => $this->valueResolver->first($resultData, ['errorText', 'error_text', 'message']) ?? 'Unknown error',
            'normalized_status' => $this->extractStatus($resultData),
            'payment_outcome' => $outcome,
            'payment_status' => $outcome,
            'transaction_data' => $this->mapToTransaction($payloadData, $resultData),
        ];
    }
}
