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
namespace heidelpay\PhpSdk;

class Heidelpay
{
    const MODE_TEST = 'sandbox';
    const MODE_LIVE = 'live';

    private $key;

    private $returnUrl;

    /** @var bool */
    private $sandboxMode = true;

    private $type;

    /**
     * Heidelpay constructor.
     *
     * @param string $key
     * @param $returnUrl
     * @param string $mode
     */
    public function __construct($key, $returnUrl, $type, $mode = self::MODE_TEST)
    {
        $this->key = $key;
        $this->returnUrl = $returnUrl;

        if ($mode !== self::MODE_TEST) {
            $this->sandboxMode = false;
        }
    }

    /**
     * Creates the card object, updates the given local card and returns it.
     *
     * @param Card $card
     * @return Card
     */
    public function createCard(Card $card)
    {
        // create card in heidelpay api and return it including the id
        return $card; // temporary
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Heidelpay
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     * @return Heidelpay
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param mixed $returnUrl
     * @return Heidelpay
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return Heidelpay
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    //</editor-fold>
}