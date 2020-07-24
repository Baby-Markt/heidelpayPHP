<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method Bancontact.
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
 *
 * @author  Marius Goniwiecha <entwicklung@babymarkt.de>
 *
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Bancontact;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class BancontactTest extends BasePaymentTest
{
    /**
     * Verify bancontact can be created.
     *
     * @test
     *
     * @return Bancontact
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function bancontactShouldBeCreatable(): Bancontact
    {
        $bancontact = $this->heidelpay->createPaymentType(new Bancontact());
        $this->assertInstanceOf(Bancontact::class, $bancontact);
        $this->assertNotNull($bancontact->getId());

        return $bancontact;
    }

    /**
     * Verify bancontact is fetchable.
     *
     * @test
     *
     * @param Bancontact $bancontact
     *
     * @return Bancontact
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @depends bancontactShouldBeCreatable
     */
    public function bancontactShouldBeFetchable(Bancontact $bancontact): Bancontact
    {
        /** @var Bancontact $fetchedBancontact */
        $fetchedBancontact = $this->heidelpay->fetchPaymentType($bancontact->getId());
        $this->assertInstanceOf(Bancontact::class, $fetchedBancontact);
        $this->assertEquals($bancontact->expose(), $fetchedBancontact->expose());

        return $fetchedBancontact;
    }

    /**
     * Verify bancontact is chargeable.
     *
     * @test
     *
     * @param Bancontact $bancontact
     *
     * @return Charge
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @depends bancontactShouldBeFetchable
     */
    public function bancontactShouldBeAbleToCharge(Bancontact $bancontact): Charge
    {
        $charge = $bancontact->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify bancontact is not authorizable.
     *
     * @test
     *
     * @param Bancontact $bancontact
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @depends bancontactShouldBeFetchable
     */
    public function bancontactShouldNotBeAuthorizable(Bancontact $bancontact)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'EUR', $bancontact, self::RETURN_URL);
    }
}
