<?php

namespace Qiita\HTTP;

class Response implements \Qiita\HTTP\ResponseInterface
{
	/** @var string */
	protected $data;
	/** @var int */
	protected $code;

	/**
	 * Return new Response object
	 * @param string $data
	 * @param int    $code
	 */
	public function __construct($data, $code)
	{
		$this->data = $data;
		$this->code = $code;
	}

	/**
	 * Return response data
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Return response code
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}
}
