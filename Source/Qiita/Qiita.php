<?php

namespace Qiita;

use \Qiita\QiitaException;
use \Qiita\HTTP\RequestInterface;
use \Qiita\HTTP\Request;
use \Qiita\HTTP\Exception as HTTPException;

class Qiita implements QiitaInterface
{
	const LIBRARY_VERSION = '1.0.0';
	const BASE_URL = 'https://qiita.com/api/v1';

	/** @var array */
	protected $config = array();
	/** @var null|string Access token */
	protected $token;

	/**
	 * Return new Qiita object
	 * @param array $config
	 */
	public function __construct(array $config = array())
	{
		$this->config = $config;
	}

	/**
	 * Call a API method
	 * @param string $path
	 * @param string $method
	 * @param array  $data
	 * @return array
	 * @throws \Qiita\QiitaException
	 */
	public function api($path, $method = 'GET', $data = null)
	{
		if ( $this->token === null )
		{
			$this->token = $this->_authenticate($this->config['username'], $this->config['password']);
		}

		$options = array(
			'format' => 'json',
			'method' => $method,
		);

		if ( $data !== null )
		{
			$options['data'] = $data;
		}

		$request = $this->_newRequest();

		if ( strpos($path, '?') === false )
		{
			$path .= '?token='.$this->token;
		}
		else
		{
			$path .= '&token='.$this->token;
		}

		try
		{
			$response = $request->execute(static::BASE_URL.$path, $options);
		}
		catch ( HTTPException $e )
		{
			throw QiitaException::failedToAuthenticateDueToHTTPError($e);
		}

		$statusCode = $response->getCode();
		$data = json_decode($response->getData(), true);

		if ( 400 <= $statusCode and $statusCode <= 499 )
		{
			throw QiitaException::failedToRequestDueToClientError($statusCode, $data['error']);
		}

		return $data;
	}

	/**
	 * Create new request object
	 * @return \Qiita\HTTP\RequestInterface
	 */
	protected function _newRequest()
	{
		$request = new Request();
		$request->setUserAgent('suin/php-qiita/'.static::LIBRARY_VERSION);
		return $request;
	}

	/**
	 * Authenticate
	 * @param string $username
	 * @param string $password
	 * @throws \Qiita\QiitaException
	 * @return string Authenticate token. may be session ID
	 */
	protected function _authenticate($username, $password)
	{
		$request = $this->_newRequest();

		try
		{
			$response = $request->execute(static::BASE_URL.'/auth', array(
				'method' => 'POST',
				'data'   => array(
					'url_name' => $username,
					'password' => $password,
				),
			));
		}
		catch ( HTTPException $e )
		{
			throw QiitaException::failedToAuthenticateDueToHTTPError($e);
		}

		if ( $response->getCode() !== 200 )
		{
			throw QiitaException::responseCodeIs($response->getCode());
		}

		$data = json_decode($response->getData(), true);
		return $data['token'];
	}
}
