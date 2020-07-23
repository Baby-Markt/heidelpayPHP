<?php

namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Traits\CanDirectCharge;

class Bancontact extends BasePaymentType
{
    use CanDirectCharge;
}
