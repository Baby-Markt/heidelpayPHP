<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method paypal.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class PaypalTest extends BasePaymentTest
{
    /**
     * Verify PayPal payment type can be created and fetched.
     *
     * @test
     *
     * @return BasePaymentType
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function paypalShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $this->assertInstanceOf(Paypal::class, $paypal);
        $this->assertNotEmpty($paypal->getId());

        $fetchedPaypal = $this->heidelpay->fetchPaymentType($paypal->getId());
        $this->assertInstanceOf(Paypal::class, $fetchedPaypal);
        $this->assertNotSame($paypal, $fetchedPaypal);
        $this->assertEquals($paypal->expose(), $fetchedPaypal->expose());

        return $fetchedPaypal;
    }

    /**
     * Verify paypal can authorize.
     *
     * @test
     * @depends paypalShouldBeCreatableAndFetchable
     *
     * @param Paypal $paypal
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     *
     * @group skip
     */
    public function paypalShouldBeAuthorizable(Paypal $paypal)
    {
        $authorization = $paypal->authorize(100.0, Currencies::EURO, self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());

        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());

        $cancellation = $authorization->cancel();
        $this->assertNotNull($cancellation);
        $this->assertNotEmpty($cancellation->getId());
    }

    /**
     * Verify paypal can charge.
     *
     * @test
     * @depends paypalShouldBeCreatableAndFetchable
     *
     * @param Paypal $paypal
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function paypalShouldBeChargeable(Paypal $paypal)
    {
        $charge = $paypal->charge(100.0, Currencies::EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
    }
}
