<?php
/**
 * This class defines unit tests to verify functionality of the HttpService.
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
namespace heidelpay\MgwPhpSdk\test\unit;

use heidelpay\MgwPhpSdk\Adapter\CurlAdapter;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\DebugHandlerInterface;
use heidelpay\MgwPhpSdk\Services\HttpService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyAdapter;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

class HttpServiceTest extends BaseUnitTest
{
    /**
     * Verify getAdapter will return a CurlAdapter if none has been set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function getAdapterShouldReturnDefaultAdapterIfNonHasBeenSet()
    {
        $httpService = new HttpService();
        $this->assertInstanceOf(CurlAdapter::class, $httpService->getAdapter());
    }

    /**
     * Verify getAdapter will return custom adapter if it has been set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function getAdapterShouldReturnCustomAdapterIfItHasBeenSet()
    {
        $dummyAdapter = new DummyAdapter();
        $httpService = (new HttpService())->setHttpAdapter($dummyAdapter);
        $this->assertSame($dummyAdapter, $httpService->getAdapter());
    }

    /**
     * Verify send will throw exception if resource is null.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function sendShouldThrowExceptionIfResourceIsNotSet()
    {
        $httpService = new HttpService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transfer object is empty!');
        $httpService->send();
    }

    /**
     * Verify send calls methods to setup and send request.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function sendShouldInitAndSendRequest()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();

        $resource = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));
        $adapterMock->expects($this->once())->method('init')->with(
            'https://api.heidelpay.com/v1/my/uri/123',
            'dummyResourceJsonSerialized',
            'GET'
        );
        $adapterMock->expects($this->once())->method('setUserAgent')->with('HeidelpayPHP');
        $headers = [
            'Authorization' => 'Basic cy1wcml2LU15VGVzdEtleTo=',
            'Content-Type'  => 'application/json',
            'SDK-VERSION'   => '1.0.0.0-beta.3'
        ];
        $adapterMock->expects($this->once())->method('setHeaders')->with($headers);
        $adapterMock->expects($this->once())->method('execute')->willReturn('myResponseString');
        $adapterMock->expects($this->once())->method('getResponseCode')->willReturn('myResponseCode');
        $adapterMock->expects($this->once())->method('close');

        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        /** @var HttpService $httpServiceMock*/
        $response = $httpServiceMock->send('/my/uri/123', $resource);

        $this->assertEquals('myResponseString', $response);
    }

    /**
     * Verify debugLog logs to debug handler if debug mode and a handler are set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function sendShouldLogDebugMessagesIfDebugModeAndHandlerAreSet()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('execute')->willReturn('{"response" : "myResponseString"}');
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $loggerMock = $this->getMockBuilder(DummyDebugHandler::class)->setMethods(['log'])->getMock();
        $loggerMock->expects($this->exactly(3))->method('log')->withConsecutive(
            ['Http GET-Request: https://api.heidelpay.com/v1/my/uri/123'],
            ['Request: dummyResourceJsonSerialized'],
            ['Response: {"response" : "myResponseString"}']
        );

        /** @var DebugHandlerInterface $loggerMock */
        $heidelpay = (new Heidelpay('s-priv-MyTestKey'))->setDebugMode(true)->setDebugHandler($loggerMock);
        $resource  = (new DummyResource())->setParentResource($heidelpay);

        /** @var HttpService $httpServiceMock*/
        $response = $httpServiceMock->send('/my/uri/123', $resource);

        $this->assertEquals('{"response" : "myResponseString"}', $response);
    }

    /**
     * Verify handleErrors will throw Exception if response string is null.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseIsEmpty()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('execute')->willReturn(null);
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The Request returned a null response!');
        $this->expectExceptionCode('');

        /** @var HttpService $httpServiceMock*/
        $httpServiceMock->send('/my/uri/123', $resource);
    }

    /**
     * Verify handleErrors will throw Exception if responseCode is greaterOrEqual to 400.
     *
     * @test
     * @dataProvider responseCodeProvider
     *
     * @param string $responseCode
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseCodeIsGoE400($responseCode)
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('getResponseCode')->willReturn($responseCode);
        $adapterMock->method('execute')->willReturn('{"response" : "myResponseString"}');
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The payment api returned an error!');
        $this->expectExceptionCode('');

        /** @var HttpService $httpServiceMock*/
        $httpServiceMock->send('/my/uri/123', $resource);
    }

    /**
     * Verify handleErrors will throw Exception if response contains errors field.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseContainsErrorField()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();

        $firstResponse = '{"errors": [{}]}';
        $secondResponse = '{"errors": [{"merchantMessage": "This is an error message for the merchant!"}]}';
        $thirdResponse = '{"errors": [{"customerMessage": "This is an error message for the customer!"}]}';
        $fourthResponse = '{"errors": [{"code": "This is the error code!"}]}';

        $adapterMock->method('execute')->willReturnOnConsecutiveCalls(
            $firstResponse,
            $secondResponse,
            $thirdResponse,
            $fourthResponse
        );
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        /** @var HttpService $httpServiceMock*/
        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The first exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEmpty($e->getCode());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The second exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('This is an error message for the merchant!', $e->getMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEmpty($e->getCode());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The third exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMessage());
            $this->assertEquals('This is an error message for the customer!', $e->getClientMessage());
            $this->assertEmpty($e->getCode());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The fourth exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('This is the error code!', $e->getCode());
        }
    }

    //<editor-fold desc="DataProviders">

    /**
     * Data provider for handleErrorsShouldThrowExceptionIfResponseCodeIsGoE400.
     *
     * @return array
     */
    public function responseCodeProvider(): array
    {
        return [
            '400' => ['400'],
            '401' => ['401'],
            '404' => ['404'],
            '500' => ['500'],
            '600' => ['600'],
            '1000' => ['1000']
        ];
    }

    //</editor-fold>
}