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
 * Abstract base class for signed (and unsigned) Mollie APIs.
 */
abstract class Mollie_API
{
	/**
	 * @var string
	 */
	const API_BASE_URL = 'https://secure.mollie.nl';
	/**
	 * @var bool
	 */
	const STRICT_SSL = TRUE;
	/**
	 * @var string
	 */
	const METHOD_GET = 'GET';
	/**
	 * @var string
	 */
	const METHOD_POST = 'POST';

	/**
	 * Persistent parameters.
	 *
	 * @var array
	 */
	private $_persistent_params = array();
	/**
	 * Callable that contains the application secret, because callables cannot be var_dumped.
	 *
	 * @var callable
	 */
	private $_callableSecret;
	/**
	 * Request history log.
	 * 
	 * @var array
	 */
	private $_requestLog = array();

	/**
	 * The base URL as used by the API client.
	 *
	 * @var string
	 */
	private $_api_base_url = self::API_BASE_URL;

	/**
	 * Constructor sets persistent parameters and creates a secret callable.
	 * Method may be overloaded to set other persistent parameters, or to disable signing.
	 * 
	 * @param int $partner_id
	 * @param string $profile_key
	 * @param string $app_secret
	 */
	public function __construct ($partner_id, $profile_key, $app_secret)
	{
		// Set persistent parameters
		$this->setPersistentParam('partner_id',  $partner_id);
		$this->setPersistentParam('profile_key', $profile_key);

		// Make a private secret callable
		$this->_callableSecret = create_function('', 'return '.var_export($app_secret, TRUE).';');
	}

	/**
	 * Set a persistent parameter that will be used in all following requests.
	 *
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	final public function setPersistentParam ($name, $value)
	{
		return $this->_persistent_params[$name] = $value;
	}

	/**
	 * Get all performed requests.
	 * 
	 * @return array 
	 */
	final public function getRequestLog ()
	{
		return $this->_requestLog;
	}

	/**
	 * Check if the response received from the Mollie service is an error. If it is an error, then it will throw a
	 * Mollie_Exception, else it will do nothing.
	 * 
	 * @param Mollie_Response $object
	 * @throws Mollie_Exception
	 * @return void
	 */
	protected function _checkResultErrors (Mollie_Response $object)
	{
		if (!$object->isSuccess())
		{
			throw new Mollie_Exception(strval($object->resultmessage), intval($object->resultcode));
		}
	}

	/**
	 * Perform HTTP request and return result string/object.
	 * 
	 * @param string $method
	 * @param string $path
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function _performRequest ($method, $path, array $params)
	{
		// Combine given parameters with persistent parameters and convert to string values.
		$params = array_map('strval', $params + $this->_persistent_params);

		$this->_signRequest($path, $params, $this->_getAppSecret());
		$result = $this->_doRequest($method, $path, $params);

		$this->_logRequest($method, $path, $params, $result);

		// See if there were cURL errors
		if (empty($result["body"]))
		{
			throw new Mollie_Exception($result["message"], $result["code"]);
		}
		$object = $this->_convertResponseBodyToObject($result['body'], $result['content_type']);

		if ($object instanceof Mollie_Response)
		{
			$this->_checkResultErrors($object);
		}

		return $object;
	}

	/**
	 * Uses the private secret callable to get the Mollie Application secret.
	 * 
	 * @return string
	 */
	final private function _getAppSecret ()
	{
		return call_user_func($this->_callableSecret);
	}

	/**
	 * Calculate an MD5-signature based on request path, parameters and key.
	 * Signature will be added to input parameters array.
	 * 
	 * @param string $path Current request path without query string
	 * @param array &$params Parameters to use as HMAC data
	 * @param string $secret Secret to use as HMAC key
	 * @param int $timestamp (Optional) Override timestamp
	 */
	protected function _signRequest ($path, array &$params, $secret, $timestamp = NULL)
	{
		// If there is no secret, don't sign request
		if (empty($secret)) {
			return;
		}

		// Remove any existing signature
		unset($params['signature']);

		// Update timestamp 
		$params['timestamp'] = $timestamp !== NULL ? $timestamp : time();

		// Sort parameters by keys in alphabetical order
		ksort($params);

		// Calculate signature
		$params['signature'] = hash_hmac('sha1', '/'.trim($path, '/').'?'.http_build_query($params, '', '&'), strtoupper($secret));
	}

	/**
	 * Change the API base URL.
	 *
	 * @internal
	 * @codeCoverageIgnore
	 * @param $url
	 */
	final public function setApiBaseUrl($url)
	{
		$this->_api_base_url = $url;
	}

	/**
	 * Do the actual HTTP request.
	 * 
	 * @param string $method HTTP request method
	 * @param string $path Request path without query string
	 * @param array $params Parameters including profile_key, timestamp and signature
	 * @return array
	 * 
	 * @codeCoverageIgnore
	 */
	protected function _doRequest ($method, $path, array $params)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, self::STRICT_SSL);
		curl_setopt($ch, CURLOPT_ENCODING, ''); // Signal that we support gzip

		$api_endpoint = trim($this->_api_base_url, '/').'/'.trim($path, '/');

		if ($method == self::METHOD_GET)
		{
			curl_setopt($ch, CURLOPT_URL, $api_endpoint.'?'.http_build_query($params, '', '&'));
		}
		else
		{
			curl_setopt($ch, CURLOPT_URL, $api_endpoint);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		$body = curl_exec($ch);

		if (curl_errno($ch) == CURLE_SSL_CACERT || curl_errno($ch) == CURLE_SSL_PEER_CERTIFICATE || curl_errno($ch) == 77 /* CURLE_SSL_CACERT_BADFILE (constant not defined in PHP though) */)
		{
			/*
			 * On some servers, the list of installed certificates is outdated or not present at all (the ca-bundle.crt
			 * is not installed). So we tell cURL which certificates we trust. Then we retry the requests.
			 */
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . DIRECTORY_SEPARATOR . "cacert.pem");
			$body = curl_exec($ch);
		}

		if (strpos(curl_error($ch), "certificate subject name 'mollie.nl' does not match target host") !== FALSE)
		{
			/*
			 * On some servers, the wildcard SSL certificate is not processed correctly. This happens with OpenSSL 0.9.7
			 * from 2003.
			 */
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			$body = curl_exec($ch);
		}

		$results = array(
			'body'         => $body,
			'http_code'    => curl_getinfo($ch, CURLINFO_HTTP_CODE),
			'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
			'code'         => curl_errno($ch), 
			'message'      => curl_error($ch),
		);

		curl_close($ch);

		return $results;
	}

	/**
	 * Log a request.
	 * 
	 * @param string $method HTTP request method
	 * @param string $path Request path without query string
	 * @param array $params All used HTTP parameters
	 * @param mixed $result cURL result
	 */
	final private function _logRequest ($method, $path, array $params, $result)
	{
		$this->_requestLog[gmdate('Y-m-d\TH:i:s\Z ').substr(microtime(),0,5)] = get_defined_vars();
	}

	/**
	 * Convert result body to an object based on Content-Type.
	 * 
	 * @param string $body 
	 * @param string $content_type 
	 * @return mixed
	 */
	final private function _convertResponseBodyToObject ($body, $content_type)
	{
		// No body to convert
		if (empty($body)) {
			return NULL;
		}

		/*
		 * Convert to Mollie_Response object.
		 */
		if (preg_match('/(application|text)\/xml/i', $content_type))
		{
			return simplexml_load_string($body, "Mollie_Response");
		}

		// Return as string
		return $body;
	}
}
