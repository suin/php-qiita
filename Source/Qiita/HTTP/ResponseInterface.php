<?php

namespace Qiita\HTTP;

interface ResponseInterface
{
	/**
	 * Return new Response object
	 * @param string $data
	 * @param int $code
	 */
	public function __construct($data, $code);

	/**
	 * Return response data
	 * @return string
	 */
	public function getData();

	/**
	 * Return response code
	 * @return int
	 */
	public function getCode();
}
