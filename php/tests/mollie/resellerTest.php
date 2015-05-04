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
 * @covers Mollie_Reseller
 * @group apiclients
 */
class Mollie_ResellerTest extends PHPUnit_Framework_Testcase
{
	/**
	 * @var Mollie_Reseller|PHPUnit_Framework_MockObject_MockObject
	 */
	public $api;

	const API_VERSION = 1;
	const PARTNER_ID  = 123456;
	const PROFILE_KEY = 'EB9F3236';
	const APP_SECRET  = 'A029F2739CFB5AF94AE88FC900A332E7930FEF37';

	public function setUp ()
	{
		$this->api = $this->getMock('Mollie_Reseller', array('_doRequest'), array(self::PARTNER_ID, self::PROFILE_KEY, self::APP_SECRET));
	}

	public function camel2dashed ($funcName) {
		return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $funcName));
	}

	public function dpApiCalls ()
	{
		return array(
			array('accountClaim', array(
				'username' => 'john',
				'password' => '123456',
			)),
			array('accountCreate', array(
				'username' => 'john', 
				'fields'   => array('address' => '123 Fake Street'),
			)),
			array('accountValid', array(
				'username' => 'john',
				'password' => '123456',
			)),
			array('accountEdit', array(
				'username' => 'john',
				'password' => '123456',
				'fields'   => array('address' => '123 Fake Street'),
			)),
			array('profiles', array(
				'username' => 'john',
				'password' => '123456',
			)),
			array('profileCreate', array(
				'username' => 'john',
				'password' => '123456',
				'name'     => 'peter',
				'website'  => 'petershop',
				'email'    => 'peter@email',
				'phone'    => '02468',
				'category' => '5399',
			)),
			array('bankaccounts', array(
				'username' => 'john',
				'password' => '123456',
			)),
			array('bankaccountEdit', array(
				'username' => 'john',
				'password' => '123456',
				'id'       => '123',
				'fields'   => array('account_number' => '123456789'),
			)),
			array('availablePaymentMethods', array(
				'username' => 'john',
				'password' => '123456',
			)),
			array('availablePaymentMethodsByPartnerId', array(
				'partner_id_customer' => '555',
			)),
		);
	}

	/**
	 * @dataProvider dpApiCalls
	 */
	public function testApiCalls ($method, array $params)
	{
		$expected_path = "/api/reseller/v".self::API_VERSION.'/'.$this->camel2dashed($method == "availablePaymentMethodsByPartnerId" ? "availablePaymentMethods" : $method);
		$expected_params = $params + (isset($params['fields']) ? $params['fields'] : array());
		unset($expected_params["fields"]);

		$that = $this;

		$this->api->expects($this->once())
			->method('_doRequest')
			->will($this->returnCallback(function ($http, $path, $params) use ($expected_params, $expected_path, $that) {

			$that->assertEquals("POST", $http);
			$that->assertEquals($expected_path, $path);
			foreach ($expected_params as $param => $value)
			{
				$that->assertArrayHasKey($param, $params);
				$that->assertContains($value, $params);
			}

			$that->assertArrayHasKey("signature", $params);
			$that->assertArrayHasKey("timestamp", $params);
			$that->assertEquals(Mollie_ResellerTest::PARTNER_ID, $params["partner_id"]);
			$that->assertEquals(Mollie_ResellerTest::PROFILE_KEY, $params["profile_key"]);

			return array(
				"body" => "<?xml version=\"1.0\"?>
								<response>
								<success>true</success>
								<resultcode>10</resultcode>
								<resultmessage>Test OK!.</resultmessage>
							</response>",
				"content_type" => "text/xml",
			);
		}));

		$this->assertInstanceOf("Mollie_Response", call_user_func_array(array($this->api, $method), array_values($params)));
	}
}
