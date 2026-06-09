<?php

namespace AlRajhi\PaymentGateway\Contracts;

interface PaymentPayloadBuilderContract
{
    public function build(array $data): array;

    public function buildSupporting(array $data): array;
}
