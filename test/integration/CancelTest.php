<?php
/**
 * This class defines integration tests to verify cancellation in general.
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class CancelTest extends BasePaymentTest
{
    /**
     * Verify reversal is fetchable.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function reversalShouldBeFetchableViaHeidelpayObject()
    {
        $authorization = $this->createAuthorization();
        $cancel = $authorization->cancel();
        $fetchedCancel = $this->heidelpay->fetchReversal($authorization->getPayment()->getId(), $cancel->getId());
        $this->assertNotNull($fetchedCancel);
        $this->assertNotNull($fetchedCancel->getId());
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify reversal is fetchable.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function reversalShouldBeFetchableViaPaymentObject()
    {
        $authorization = $this->createAuthorization();
        $cancel = $authorization->cancel();
        $fetchedCancel = $cancel->getPayment()->getAuthorization()->getCancellation($cancel->getId());
        $this->assertNotNull($fetchedCancel);
        $this->assertNotNull($fetchedCancel->getId());
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify refund is fetchable.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function refundShouldBeFetchableViaHeidelpayObject()
    {
        $charge = $this->createCharge();
        $cancel = $charge->cancel();
        $fetchedCancel = $this->heidelpay
            ->fetchRefundById($charge->getPayment()->getId(), $charge->getId(), $cancel->getId());
        $this->assertNotNull($fetchedCancel);
        $this->assertNotNull($fetchedCancel->getId());
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify refund is fetchable.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function refundShouldBeFetchableViaPaymentObject()
    {
        $charge = $this->createCharge();
        $cancel = $charge->cancel();
        $fetchedCancel = $cancel->getPayment()->getChargeById($charge->getId())->getCancellation($cancel->getId());
        $this->assertNotNull($fetchedCancel);
        $this->assertNotNull($fetchedCancel->getId());
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify reversal is fetchable via payment object.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function authorizationCancellationsShouldBeFetchableViaPaymentObject()
    {
        $authorization = $this->createAuthorization();
        $reversal = $authorization->cancel();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertNotNull($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }

    /**
     * Verify refund is fetchable via payment object.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function chargeCancellationsShouldBeFetchableViaPaymentObject()
    {
        $charge = $this->createCharge();
        $reversal = $charge->cancel();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertNotNull($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }
}