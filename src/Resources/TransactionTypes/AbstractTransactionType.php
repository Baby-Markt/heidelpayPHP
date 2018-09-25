<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\Resources\TransactionTypes;

use heidelpay\NmgPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Resources\Payment;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayResourceInterface;

abstract class AbstractTransactionType extends AbstractHeidelpayResource
{
    /** @var Payment $payment */
    private $payment;

    //<editor-fold desc="Getters/Setters">
    /**
     * @return Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment($payment): self
    {
        $this->payment = $payment;
        return $this;
    }
    //</editor-fold>

    public function handleResponse(\stdClass $response)
    {
        parent::handleResponse($response);
        $this->updatePayment();
    }

    /**
     * Updates the payment object if it exists and if this is not the payment object.
     * This is called from the crud methods to update the payments state whenever anything happens.
     */
    private function updatePayment()
    {
        if (!$this instanceof Payment) {
            $payment = $this->getPayment();
            if ($payment instanceof HeidelpayResourceInterface) {
                $this->getHeidelpayObject()->getResourceService()->fetch($payment);
            }
        }
    }


}
