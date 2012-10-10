<?php

namespace Qiita\HTTP;

use \Qiita\HTTP\Response;
use \Qiita\HTTP\Exception as HTTPException;

class Request implements \Qiita\HTTP\RequestInterface
{
	/** @var int */
	protected $timeout = 10;
	/** @var string */
	protected $userAgent = 'PHP';
	/** @var bool */
	protected $sslVerification = true;
	/** @var string|null */
	protected $username;
	/** @var string|null */
	protected $password;

	/**
	 * Set timeout
	 * @param int $timeout Seconds for timeout
	 * @return $this
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Set user agent
	 * @param string $userAgent
	 * @return $this
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * Disable SSL verification
	 * @return $this
	 */
	public function disableSSLVerification()
	{
		$this->sslVerification = false;
		return $this;
	}

	/**
	 * Enable SSL verification
	 * @return $this
	 */
	public function enableSSLVerification()
	{
		$this->sslVerification = true;
		return $this;
	}

	/**
	 * Set user password
	 * @param string $username
	 * @param string $password
	 * @return $this
	 */
	public function setUserPassword($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		return $this;
	}

	/**
	 * Unset user password
	 * @return $this
	 */
	public function unsetUserPassword()
	{
		$this->username = null;
		$this->password = null;
		return $this;
	}

	/**
	 * Execute request
	 * @param string $url
	 * @param array  $options
	 * @return \Qiita\HTTP\ResponseInterface
	 * @throws \Qiita\HTTP\Exception
	 */
	public function execute($url, array $options = array())
	{
		$options = array_merge(array(
			'method'   => 'GET',
			'format'   => null,
			'data'     => null,
			'username' => $this->username,
			'password' => $this->password,
			'headers'  => array(),
		), $options);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerification);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);

		if ( $options['format'] === 'json' and isset($options['data']) )
		{
			$options['data'] = json_encode($options['data']);
			$options['headers']['Content-Type'] = 'application/json';
		}

		if ( isset($options['data']) )
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
		}

		if ( isset($options['username']) and isset($options['password']) )
		{
			curl_setopt($ch, CURLOPT_USERPWD,  $options['username'].':'.$options['password']);
		}

		if ( count($options['headers']) )
		{
			$headers = array();

			foreach ( $options['headers'] as $key => $value )
			{
				$headers[] = sprintf('%s:%s', $key, $value);
			}

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$result = curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ( $result === false )
		{
			throw HTTPException::connectionError(curl_error($ch));
		}

		curl_close($ch);

		return new Response($result, $statusCode);
	}
}
