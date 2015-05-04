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
 * A response created by the Mollie API. This extends SimpleXMLElement and contains some tags that are present in all
 * Mollie API responses.
 */
class Mollie_Response extends SimpleXMLElement
{
	/**
	 * Error code that Mollie uses to indicate success.
	 */
	const SUCCESS = 10;
	const TRUE    = "true";
	const FALSE   = "false";

	/**
	 * This field maps to the <success /> tag in the API response, it is either "true" or "false".
	 *
	 * @see self::TRUE
	 * @see self::FALSE
	 * @var string
	 */
	public $success;

	/**
	 * The resultcode received from Mollie.
	 *
	 * @var int
	 */
	public $resultcode;

	/**
	 * The result message received from Mollie.
	 *
	 * @var string
	 */
	public $resultmessage;

	/**
	 * Was the API call a success? (E.g. there was no error / connection error).
	 *
	 * NB. This ONLY applies to the result of the API call, if you use the API to check the status of a payment, please
	 * look at the $payment->paid field instead.
	 *
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->resultcode == self::SUCCESS;
	}
}