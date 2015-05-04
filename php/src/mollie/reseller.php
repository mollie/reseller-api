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
 * Mollie Reseller API.
 *
 * @link https://www.mollie.com/beheer/reseller/documentatie
 */
class Mollie_Reseller extends Mollie_API
{
	/**
	 * @var int
	 */
	const API_VERSION = 1;

	/**
	 * @param string $username
	 * @param string $password
	 * @return SimpleXMLElement
	 */
	public function accountClaim ($username, $password)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/account-claim', self::API_VERSION), get_defined_vars());
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return SimpleXMLElement
	 */
	public function accountValid ($username, $password)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/account-valid', self::API_VERSION), get_defined_vars());
	}
	/**
	 * @param string $username
	 * @param array $fields
	 * @return SimpleXMLElement
	 */
	public function accountCreate ($username, array $fields)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/account-create', self::API_VERSION), array("username" => $username) + $fields);
	}
	/**
	 * @param string $username
	 * @param string $password
	 * @param array $fields
	 * @return SimpleXMLElement
	 */
	public function accountEdit ($username, $password, array $fields)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/account-edit', self::API_VERSION), array("username" => $username, "password" => $password) + $fields);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return SimpleXMLElement
	 */
	public function bankAccounts ($username, $password)
	{
		return $this->_performRequest(self::METHOD_POST, sprintf('/api/reseller/v%d/bankaccounts', self::API_VERSION), get_defined_vars());
	}
	/**
	 * @param string $username
	 * @param string $password
	 * @param string $id
	 * @param array $fields
	 * @return SimpleXMLElement
	 */
	public function bankAccountEdit ($username, $password, $id, array $fields)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/bankaccount-edit', self::API_VERSION), array("username" => $username, "password" => $password, "id" => $id) + $fields);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return SimpleXMLElement
	 */
	public function profiles ($username, $password)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/profiles', self::API_VERSION), get_defined_vars());
	}
	/**
	 * @param string $username
	 * @param string $password
	 * @param string $name
	 * @param string $website
	 * @param string $email
	 * @param string $phone
	 * @param int $category
	 *
	 * @return SimpleXMLElement
	 */
	public function profileCreate ($username, $password, $name, $website, $email, $phone, $category)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/profile-create', self::API_VERSION), get_defined_vars());
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return SimpleXMLElement
	 */
	public function availablePaymentMethods ($username, $password)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/available-payment-methods', self::API_VERSION), get_defined_vars());
	}

	/**
	 * @param string $partner_id_customer
	 * @return SimpleXMLElement
	 */
	public function availablePaymentMethodsByPartnerId ($partner_id_customer)
	{
		return $this->_performRequest(self::METHOD_POST,  sprintf('/api/reseller/v%d/available-payment-methods', self::API_VERSION), get_defined_vars());
	}
}
