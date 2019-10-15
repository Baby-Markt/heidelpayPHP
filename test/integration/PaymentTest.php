<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Payment resource.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class PaymentTest extends BasePaymentTest
{
    /**
     * Verify fetching payment by authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function paymentShouldBeFetchableById()
    {
        $authorize = $this->createPaypalAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorize->getPayment()->getId());
        $this->assertNotNull(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertInstanceOf(Authorization::class, $payment->getAuthorization());
        $this->assertNotEmpty($payment->getAuthorization()->getId());
        $this->assertNotNull($payment->getState());
    }

    /**
     * Verify full charge on payment with authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullChargeShouldBePossibleOnPaymentObject()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $authorization->getPayment();

        // pre-check to verify changes due to fullCharge call
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        /** @var Charge $charge */
        $charge = $payment->charge();
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify payment can be fetched with charges.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function paymentShouldBeFetchableWithCharges()
    {
        $authorize = $this->createCardAuthorization();
        $payment = $authorize->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());
        $this->assertNotNull($payment->getAuthorization());
        $this->assertNotNull($payment->getAuthorization()->getId());

        $charge = $payment->charge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertNotNull($fetchedPayment->getCharges());
        $this->assertCount(1, $fetchedPayment->getCharges());

        $fetchedCharge = $fetchedPayment->getChargeByIndex(0);
        $this->assertEquals($charge->getAmount(), $fetchedCharge->getAmount());
        $this->assertEquals($charge->getCurrency(), $fetchedCharge->getCurrency());
        $this->assertEquals($charge->getId(), $fetchedCharge->getId());
        $this->assertEquals($charge->getReturnUrl(), $fetchedCharge->getReturnUrl());
        $this->assertEquals($charge->expose(), $fetchedCharge->expose());
    }

    /**
     * Verify partial charge after authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function partialChargeAfterAuthorization()
    {
        $authorization = $this->createCardAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $charge = $fetchedPayment->charge(10.0);
        $this->assertNotNull($charge);
        $this->assertEquals('s-chg-1', $charge->getId());
        $this->assertEquals('10.0', $charge->getAmount());
    }

    /**
     * Verify authorization on payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizationShouldBePossibleOnHeidelpayObject()
    {
        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $paypal, self::RETURN_URL);
        $this->assertNotNull($authorize);
        $this->assertNotEmpty($authorize->getId());
    }

    /**
     * Verify heidelpay payment charge is possible using a paymentId.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function paymentChargeOnAuthorizeShouldBePossibleUsingPaymentId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorization = $this->heidelpay->authorize(100.00, 'EUR', $card, 'http://heidelpay.com', null, null, null, null, false);
        $charge = $this->heidelpay->chargePayment($authorization->getPaymentId());

        $this->assertInstanceOf(Charge::class, $charge);
    }

    /**
     * Verify heidelpay payment charge throws an error if the id does not belong to a payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     *
     * @group robustness
     */
    public function chargePaymentShouldThrowErrorOnNonPaymentId()
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_PAYMENT_NOT_FOUND);
        $this->heidelpay->chargePayment('s-crd-xlj0qhdiw40k');
    }

    /**
     * Verify an Exception is thrown if the orderId already exists.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     *
     * @group robustness
     */
    public function apiShouldReturnErrorIfOrderIdAlreadyExists()
    {
        $orderId = str_replace(' ', '', microtime());

        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorization = $this->heidelpay->authorize(100.00, 'EUR', $paypal, 'http://heidelpay.com', null, $orderId, null, null, false);
        $this->assertNotEmpty($authorization);

        $paypal2 = $this->heidelpay->createPaymentType(new Paypal());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ORDER_ID_ALREADY_IN_USE);
        $this->heidelpay->authorize(101.00, 'EUR', $paypal2, 'http://heidelpay.com', null, $orderId, null, null, false);
    }

    /**
     * Verify a payment is fetched by orderId if the id is not set.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function paymentShouldBeFetchedByOrderIdIfIdIsNotSet()
    {
        $orderId = str_replace(' ', '', microtime());
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorization = $this->heidelpay->authorize(100.00, 'EUR', $paypal, 'http://heidelpay.com', null, $orderId, null, null, false);
        $payment = $authorization->getPayment();
        $fetchedPayment = $this->heidelpay->fetchPaymentByOrderId($orderId);

        $this->assertNotSame($payment, $fetchedPayment);
        $this->assertEquals($payment->expose(), $fetchedPayment->expose());
    }
}
