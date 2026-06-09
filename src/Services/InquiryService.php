<?php

namespace AlRajhi\PaymentGateway\Services;

use AlRajhi\PaymentGateway\Enums\InquiryReferenceType;
use AlRajhi\PaymentGateway\Http\Clients\PaymentGatewayClient;
use AlRajhi\PaymentGateway\Services\BankPayment\InquiryRequestPreparer;
use AlRajhi\PaymentGateway\Services\BankPayment\ResponseProcessor;
use AlRajhi\PaymentGateway\Services\BankPayment\SupportingTransactionValidator;

class InquiryService
{
    public function __construct(
        protected PaymentGatewayClient $client,
        protected InquiryRequestPreparer $requestPreparer,
        protected SupportingTransactionValidator $validator,
        protected ResponseProcessor $responseProcessor
    ) {
    }

    public function inquire(array $data): array
    {
        $data = $this->requestPreparer->prepare($data);
        $this->validator->validateInquiry($data);

        $gatewayResponse = $this->client->submitSupportingTransaction(
            $data,
            $data['customer_ip']
        );

        $result = $this->responseProcessor->handleResponse($gatewayResponse);

        return $this->responseProcessor->handleResponseData($result);
    }

    public function byTransId(
        string $transId,
        string $amount,
        string $trackId,
        ?string $customerIp = null
    ): array {
        return $this->inquire([
            'reference_type' => InquiryReferenceType::TRANS_ID->value,
            'reference_id' => $transId,
            'trans_id' => $transId,
            'amount' => $amount,
            'track_id' => $trackId,
            'customer_ip' => $customerIp,
        ]);
    }

    public function byPaymentId(
        string $paymentId,
        string $amount,
        string $trackId,
        ?string $customerIp = null
    ): array {
        return $this->inquire([
            'reference_type' => InquiryReferenceType::PAYMENT_ID->value,
            'reference_id' => $paymentId,
            'trans_id' => $paymentId,
            'amount' => $amount,
            'track_id' => $trackId,
            'customer_ip' => $customerIp,
        ]);
    }

    public function byTrackId(
        string $trackId,
        string $amount,
        ?string $customerIp = null
    ): array {
        return $this->inquire([
            'reference_type' => InquiryReferenceType::TRACK_ID->value,
            'reference_id' => $trackId,
            'trans_id' => $trackId,
            'amount' => $amount,
            'track_id' => $trackId,
            'customer_ip' => $customerIp,
        ]);
    }
}
