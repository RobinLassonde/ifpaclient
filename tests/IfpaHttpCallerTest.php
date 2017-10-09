<?php
declare(strict_types=1);

namespace Lassonde\Ifpa\Tests;

use PHPUnit\Framework\TestCase;
use Lassonde\Ifpa\IfpaHttpCaller;
use Lassonde\Ifpa\CurlRequestFactory;
use Lassonde\Ifpa\CurlRequest;
use Lassonde\Ifpa\Testing\FakeCurlRequest;

/**
 * Tests for IfpaHttpCaller.
 */
class IfpaHttpCallerTest extends TestCase
{
    const API_KEY = 'abc123';

    private $_curl_request_factory;
    private $_client;

    protected function setUp()
    {
        $this->_curl_request_factory
            = $this->createMock(CurlRequestFactory::class);
        $this->_client
            = new IfpaHttpCaller(
                IfpaHttpCallerTest::API_KEY, $this->_curl_request_factory
            );
    }

    /**
     * @covers IfpaHttpCaller::get
     */
    public function testGet(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest('{"foo": "bar"}', 200));

        $this->_curl_request_factory
            ->expects($this->once())
            ->method('newCurlRequest')
            ->will(
                $this->returnCallback(
                    function ($url) {
                        $this->assertEquals(
                            'https://api.ifpapinball.com/v1/some/path?api_key='
                                . IfpaHttpCallerTest::API_KEY
                                . '&q=Hello%20World',
                            $url
                        );
                    }
                )
            );

        $result = $this->_client->get('some/path', array('q' => 'Hello World'));

        $this->assertEquals('bar', $result['foo']);
    }

    /**
     * @covers IfpaHttpCaller::get
     */
    public function testCurlOptions(): void
    {
        $curlRequest = new FakeCurlRequest('{"foo": "bar"}', 200);
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn($curlRequest);

        $this->_client->get('some/path');

        $this->assertEquals(false, $curlRequest->getOption(CURLOPT_HEADER));
        $this->assertEquals(
            true,
            $curlRequest->getOption(CURLOPT_RETURNTRANSFER)
        );
        $this->assertEquals(
            array('User-Agent: IfpaClient/1.0'),
            $curlRequest->getOption(CURLOPT_HTTPHEADER)
        );
        $this->assertEquals(5, $curlRequest->getOption(CURLOPT_CONNECTTIMEOUT));
    }

    /**
     * @covers IfpaHttpCaller::get
     */
    public function testSetTimeoutSeconds(): void
    {
        $this->_client->setTimeoutSeconds(4);
        $curlRequest = new FakeCurlRequest('{"foo": "bar"}', 200);
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn($curlRequest);

        $this->_client->get('some/path');

        $this->assertEquals(4, $curlRequest->getOption(CURLOPT_CONNECTTIMEOUT));
    }
    /**
     * @covers IfpaHttpCaller::get
     * @expectedException RuntimeException
     */
    public function testCurlExecFailure(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(new FakeCurlRequest(false, 200));

        $this->_client->get('some/path');
    }

    /**
     * @covers IfpaHttpCaller::get
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage
     *    {"player":{"player_id":"25696","first_name":"Robin"
     */
    public function testBadJson(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(
                new FakeCurlRequest(
                    '{"player":{"player_id":"25696","first_name":"Robin"',
                    200
                )
            );

        $this->_client->get('some/path');
    }

    /**
     * @covers IfpaHttpCaller::get
     */
    public function testHttpStatusError(): void
    {
        $this->_curl_request_factory
            ->method('newCurlRequest')
            ->willReturn(
                new FakeCurlRequest(
                    '{"error":"API_KEY was not found"}',
                    401
                )
            );

        try {
            $this->_client->get('some/path');
            $this->fail('Expected IfpaHttpException to be thrown');
        } catch (\Lassonde\Ifpa\IfpaHttpException $e) {
            $this->assertEquals(401, $e->getCode());
            $this->assertEquals(
                '{"error":"API_KEY was not found"}', $e->getResponseBody());
        }
    }
}
