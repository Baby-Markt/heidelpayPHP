<?php
/**
 * This class defines a dummy http adapter used for unit tests.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Services;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;

class DummyAdapter implements HttpAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function init($url, $payload = null, $httpMethod = HttpAdapterInterface::REQUEST_GET)
    {
        // do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function execute(): string
    {
        return 'responseString';
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseCode(): string
    {
        return 'responseCode';
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        // do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function setHeaders(array $headers)
    {
        // do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function setUserAgent($userAgent)
    {
        // do nothing
    }
}