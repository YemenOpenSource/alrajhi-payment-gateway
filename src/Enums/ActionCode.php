<?php

namespace AlRajhi\PaymentGateway\Enums;

enum ActionCode: string
{
    case PURCHASE = '1';
    case REFUND = '2';
    case VOID = '3';
    case AUTHORIZE = '4';
    case CAPTURE = '5';
    case INQUIRY = '8';
    case VOID_AUTH = '9';
    case AUTH_EXTENSION = '14';
}
