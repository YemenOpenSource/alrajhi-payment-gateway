<?php

namespace AlRajhi\PaymentGateway\Enums;

enum InquiryReferenceType: string
{
    case TRANS_ID = 'TRANID';
    case PAYMENT_ID = 'PaymentID';
    case TRACK_ID = 'TrackID';
}
