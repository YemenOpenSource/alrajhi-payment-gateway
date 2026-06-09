<?php

namespace AlRajhi\PaymentGateway\Services\BankPayment;

use AlRajhi\PaymentGateway\Enums\ActionCode;
use AlRajhi\PaymentGateway\Enums\InquiryReferenceType;

class InquiryRequestPreparer
{
    public function prepare(array $data): array
    {
        $data = $this->normalizeAliases($data);
        $data = $this->resolveCustomerIp($data);
        $data['action'] = $data['action'] ?? ActionCode::INQUIRY->value;
        $data = $this->resolveReferenceFields($data);

        return $data;
    }

    protected function normalizeAliases(array $data): array
    {
        $aliases = [
            'amt' => 'amount',
            'trackId' => 'track_id',
            'currencyCode' => 'currency_code',
            'customerIp' => 'customer_ip',
            'transId' => 'trans_id',
            'referenceId' => 'reference_id',
            'referenceType' => 'reference_type',
        ];

        foreach ($aliases as $from => $to) {
            if (! array_key_exists($to, $data) && array_key_exists($from, $data)) {
                $data[$to] = $data[$from];
            }
        }

        return $data;
    }

    protected function resolveReferenceFields(array $data): array
    {
        if (! empty($data['reference_type']) && empty($data['udf5'])) {
            $data['udf5'] = InquiryReferenceType::from($data['reference_type'])->value;
        }

        if (! empty($data['udf5']) && empty($data['reference_type'])) {
            $data['reference_type'] = $data['udf5'];
        }

        if (! empty($data['reference_id']) && empty($data['trans_id'])) {
            $data['trans_id'] = $data['reference_id'];
        }

        if (! empty($data['trans_id']) && empty($data['reference_id'])) {
            $data['reference_id'] = $data['trans_id'];
        }

        return $data;
    }

    protected function resolveCustomerIp(array $data): array
    {
        if (! empty($data['customer_ip'])) {
            return $data;
        }

        $request = request();

        $headerForwardedFor = $request?->header('x-forwarded-for');
        if (is_string($headerForwardedFor) && $headerForwardedFor !== '') {
            $firstForwardedIp = trim(explode(',', $headerForwardedFor)[0]);
            if ($firstForwardedIp !== '') {
                $data['customer_ip'] = $firstForwardedIp;

                return $data;
            }
        }

        $requestIp = $request?->ip();
        if (is_string($requestIp) && $requestIp !== '') {
            $data['customer_ip'] = $requestIp;

            return $data;
        }

        $remoteAddress = $request?->server('REMOTE_ADDR');
        if (is_string($remoteAddress) && $remoteAddress !== '') {
            $data['customer_ip'] = $remoteAddress;
        }

        return $data;
    }
}
