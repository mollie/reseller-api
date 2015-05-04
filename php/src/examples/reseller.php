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
 * Mollie Reseller API example.
 */
error_reporting(-1);

// 1. Register Mollie_Autoloader
require_once dirname(__FILE__).'/../mollie/autoloader.php';
Mollie_Autoloader::register();

// 2. Define Mollie config
$partner_id  = 123456;
$profile_key = '4CC7F3A3';
$app_secret  = '144F0BCB8902423D4AA49689496F4DC968400039';

// 3. Instantiate class with Mollie config
$mollie = new Mollie_Reseller($partner_id, $profile_key, $app_secret);

// 4. Call API accountCreate
try {
	$simplexml = $mollie->accountCreate('my_customer', array('email' => 'my_customer@my.website.com'));
}
catch (Mollie_Exception $e)
{
	die('An error occurred when creating an account: '.$e->getMessage());
}

var_dump($simplexml);
