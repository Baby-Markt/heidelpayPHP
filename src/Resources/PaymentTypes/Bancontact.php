<?php
/**
 * This represents the Bancontact/Mister Cash payment type.
 *
 * Copyright (C) 2020 babymarkt.de GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.heidelpay.com/
 * @link  https://docs.heidelpay.com/docs/bancontact
 *
 * @author  Marius Goniwiecha <entwicklung@babymarkt.de>
 *
 * @package  heidelpayPHP\PaymentTypes
 */

namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Traits\CanDirectCharge;

class Bancontact extends BasePaymentType
{
    use CanDirectCharge;
}
