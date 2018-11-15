<?php
/**
 * This represents the card payment type which supports credit card as well as debit card payments.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/payment_types
 */
namespace heidelpay\MgwPhpSdk\Resources\PaymentTypes;

use heidelpay\MgwPhpSdk\Traits\CanAuthorize;
use heidelpay\MgwPhpSdk\Traits\CanDirectCharge;

class Card extends BasePaymentType
{
    use CanDirectCharge;
    use CanAuthorize;

    /**
     * Card constructor.
     *
     * @param string $number
     * @param string $expiryDate
     *
     * @throws \RuntimeException
     */
    public function __construct($number, $expiryDate)
    {
        $this->setNumber($number);
        $this->setExpiryDate($expiryDate);

        parent::__construct();
    }

    //<editor-fold desc="Properties">
    /** @var string $number */
    protected $number;

    /** @var string $expiryDate */
    protected $expiryDate;

    /** @var string $cvc */
    protected $cvc;

    /** @var string $holder */
    protected $holder = '';

    /** @var string $brand */
    private $brand = '';

    //</editor-fold>

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $pan
     *
     * @return Card
     */
    public function setNumber($pan): Card
    {
        $this->number = $pan;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param string $expiryDate
     *
     * @return Card
     *
     * @throws \RuntimeException
     */
    public function setExpiryDate($expiryDate): Card
    {
        // Null value is allowed to be able to fetch a card object with nothing but the id set.
        if ($expiryDate === null) {
            return $this;
        }

        $expiryDateParts = explode('/', $expiryDate);
        if (\count($expiryDateParts) !== 2 || $expiryDateParts[0] > 12 ||
            !\is_numeric($expiryDateParts[0]) || !\is_numeric($expiryDateParts[1]) ||
            !\ctype_digit($expiryDateParts[0]) || !\ctype_digit($expiryDateParts[1]) ||
            \strlen($expiryDateParts[1]) > 4) {
            throw new \RuntimeException('Invalid expiry date!');
        }
        $this->expiryDate = date('m/Y', mktime(0, 0, 0, $expiryDateParts[0], 1, $expiryDateParts[1]));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCvc()
    {
        return $this->cvc;
    }

    /**
     * @param string $cvc
     *
     * @return Card
     */
    public function setCvc($cvc): Card
    {
        $this->cvc = $cvc;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     *
     * @return Card
     */
    public function setHolder($holder): Card
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * Setter for brand property.
     * Will be set internally on create or fetch card.
     *
     * @param string $brand
     *
     * @return Card
     */
    protected function setBrand(string $brand): Card
    {
        $this->brand = $brand;
        return $this;
    }

    //</editor-fold>
}
