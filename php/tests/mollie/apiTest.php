<?php
/**
 * Copyright (c) 2012, Mollie B.V.
 * All rights reserved. 
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met: 
 * 
 * - Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright 
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE 
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH 
 * DAMAGE. 
 *
 * @license     Berkeley Software Distribution License (BSD-License 2) http://www.opensource.org/licenses/bsd-license.php
 * @author      Mollie B.V. <info@mollie.com>
 * @copyright   Copyright © 2012 Mollie B.V.
 * @link        https://www.mollie.com
 * @category    Mollie
 * @version     1.6
 *
 * @covers Mollie_Api
 * @covers Mollie_Exception
 * @covers Mollie_Response
 * @group apiclients
 */
class Mollie_APITest extends PHPUnit_Framework_Testcase
{
	/**
	 * @var Mollie_Dummy_API|PHPUnit_Framework_MockObject_MockObject
	 */
	public $api;

	const PARTNER_ID  = 123456;
	const PROFILE_KEY = "EB9F3226";
	const APP_SECRET  = "A029F2739CFB5AF94AE88FC900A332E7930FEF37";

	public function setUp ()
	{
		$this->api = $this->getMock("Mollie_Dummy_API", array("_doRequest"), array(self::PARTNER_ID, self::PROFILE_KEY, self::APP_SECRET));
	}

	public function testClassIsAbstract ()
	{
		$api = new ReflectionClass('Mollie_API');
		$this->assertTrue($api->isAbstract());
	}

	public function testConstructorStoresParametersAndLambdafiesSecret ()
	{
		$serialized = serialize($this->api);

		$this->assertContains('lambda_', $serialized);
		$this->assertNotContains(self::APP_SECRET, $serialized);
		$this->assertNotContains(self::APP_SECRET, var_export($this->api, TRUE));

		$method = new ReflectionMethod($this->api, '_getAppSecret');
		$method->setAccessible(TRUE);
		$this->assertSame(self::APP_SECRET, $method->invoke($this->api));
	}

	public function testGetRequestLogReturnsEmptyArray ()
	{
		$this->assertSame(array(), $this->api->getRequestLog());
	}

	public function testSetAndGetRequestLogReturnsMessageWithCode ()
	{
		$this->api->_logRequest('GET', '/xml/hello', array(1), array(2));
		$log = $this->api->getRequestLog();

		$this->assertSame(
			array(
				'method' => 'GET',
				'path'   => '/xml/hello',
				'params' => array(1),
				'result' => array(2),
			), 
			array_pop($log)
		);
	}

	/**
	 * @expectedException Mollie_Exception
	 * @expectedExceptionCode 25
	 * @expectedExceptionMessage Unknown application id.
	 */
	public function testErrorReponseUnderstoodCorrectly()
	{
		$this->api->expects($this->once())->method("_doRequest")
			->with(Mollie_API::METHOD_POST, "/api/dummy/foo", $this->logicalAnd(
				$this->arrayHasKey("param1"),
				$this->arrayHasKey("param2"),
				$this->arrayHasKey("partner_id"),
				$this->arrayHasKey("profile_key"),
				$this->arrayHasKey("timestamp"),
				$this->arrayHasKey("signature")
			))->will($this->returnValue(array(
			"body" => '<?xml version="1.0" encoding="UTF-8"?>
						<response>
							<success>false</success>
							<resultcode>25</resultcode>
							<resultmessage>Unknown application id.</resultmessage>
						</response>',
			"http_code" => 403,
			"content_type" => 'text/xml; charset=UTF-8'
		)));

		$this->api->foo("bar", "baz");
	}

	/**
	 * @expectedException Mollie_Exception
	 * @expectedExceptionCode 28
	 * @expectedExceptionMessage Operation timed out after 4001 milliseconds with 0 bytes received
	 */
	public function testCurlErrorConvertedToException()
	{
		$this->api->expects($this->once())->method("_doRequest")
			->with(Mollie_API::METHOD_POST, "/api/dummy/foo", $this->logicalAnd(
			$this->arrayHasKey("param1"),
			$this->arrayHasKey("param2"),
			$this->arrayHasKey("partner_id"),
			$this->arrayHasKey("profile_key"),
			$this->arrayHasKey("timestamp"),
			$this->arrayHasKey("signature")
		))->will($this->returnValue(array(
			'body' => FALSE,
			'http_code' => 100,
			'content_type' => FALSE,
  			'code' =>  CURLE_OPERATION_TIMEOUTED,
  			'message' => "Operation timed out after 4001 milliseconds with 0 bytes received",
		)));

		$this->api->foo("bar", "baz");
	}
	public function testSignRequestReturnsNull ()
	{
		$arr = array();
		$this->assertNull($this->api->_signRequest('', $arr, ''));
	}

	public function testSignRequestAddsTimestampAndSignature ()
	{
		$arr = array();
		$this->api->_signRequest('', $arr, '*secret*', 1347961550);

		$this->assertSame(1347961550, $arr['timestamp']);
		$this->assertSame('d71c94f0c12dfaa02c0c704dfc313f333db7a3ca', $arr['signature']);
	}

	public function testConvertResultToObjectReturnsNull ()
	{
		$this->assertNull($this->api->_convertResponseBodyToObject('', ''));
	}

	public function testConvertResultToObjectReturnsXml ()
	{
		$xml = $this->api->_convertResponseBodyToObject('<?xml version="1.0"?><root><xml>XML</xml></root>', 'application/xml');
	
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		$this->assertEquals('XML', $xml->xml);
	}

	public function testConvertResultToObjectReturnsRawBody ()
	{
		$raw = $this->api->_convertResponseBodyToObject('body', 'text/something');
	
		$this->assertInternalType('string', $raw);
		$this->assertSame('body', $raw);
	}

	public function testPerformRequestReturnsObject ()
	{
		$this->api->setPersistentParam('test', true);
		$this->api->expects($this->once())
			->method('_doRequest')
			->with(Mollie_API::METHOD_GET, '/xml/path', $this->logicalAnd(
			$this->arrayHasKey("id"),
			$this->arrayHasKey("test"),
			$this->arrayHasKey("partner_id"),
			$this->arrayHasKey("profile_key"),
			$this->arrayHasKey("timestamp"),
			$this->arrayHasKey("signature")
		))->will($this->returnValue(array(
			"body" => '<?xml version="1.0" encoding="UTF-8"?>
						<response>
							<success>true</success>
							<resultcode>10</resultcode>
							<resultmessage>Flux capacitor fully charged.</resultmessage>
						</response>',
			"http_code" => 200,
			"content_type" => 'application/xml; charset=UTF-8'
		)));

		$this->assertInstanceOf("Mollie_Response", $this->api->_performRequest(Mollie_API::METHOD_GET, '/xml/path', array('id' => 187337)));
	}

	public function testPerformRequestReturnsBody ()
	{
		$this->api->expects($this->once())
			->method('_doRequest')
			->will($this->returnValue(array(
			"body" => 'foobar',
			"http_code" => 200,
			"content_type" => 'text/plain; charset=UTF-8'
		)));
		$this->assertSame('foobar', $this->api->_performRequest(Mollie_API::METHOD_POST, '/xml/path', array()));
	}
}

/**
 * Test dummy, implementation of Mollie_API base class.
 *
 * @ignore
 */
class Mollie_Dummy_API extends Mollie_API
{
	public function __call ($method, array $args)
	{
		$method = new ReflectionMethod($this, $method);
		$method->setAccessible(TRUE);
		return $method->invokeArgs($this, $args);
	}

	public function _signRequest ($path, array &$params, $secret, $timestamp = NULL)
	{
		return parent::_signRequest($path, $params, $secret, $timestamp);
	}

	public function foo($param1, $param2)
	{
		$this->_performRequest(self::METHOD_POST, "/api/dummy/foo", get_defined_vars());
	}
}